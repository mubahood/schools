<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchServiceSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'service_id',
        'quantity',
        'total',
        'due_academic_year_id',
        'due_term_id',
        'link_with',
        'transport_route_id',
        'trip_type',
        'administrators',
        'is_processed',
        'processed_notes',
        'success_count',
        'fail_count',
        'total_count',
        'to_be_managed_by_inventory',
        'items_to_be_offered',
        'skipped_count',
        'locked_at',
        'locked_by_id',
    ];

    /** A run that has held the lock this long is treated as dead and may be retaken. */
    const LOCK_TIMEOUT_MINUTES = 30;

    // ── Accessors / Mutators ─────────────────────────────────────────────────

    public function setAdministratorsAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['administrators'] = json_encode([]);
            return;
        }
        if (is_array($value)) {
            // Unique: the same student listed twice would otherwise be reported as
            // "already subscribed" on its second pass — a false rejection caused
            // purely by the input list.
            $this->attributes['administrators'] = json_encode(
                array_values(array_unique(array_filter($value)))
            );
            return;
        }
        // Accept already-encoded JSON string
        $decoded = json_decode($value, true);
        $this->attributes['administrators'] = ($decoded !== null) ? $value : json_encode([]);
    }

    public function getAdministratorsAttribute($value)
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function setItemsToBeOfferedAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['items_to_be_offered'] = json_encode($value);
        } elseif (is_string($value) && json_decode($value) !== null) {
            $this->attributes['items_to_be_offered'] = $value;
        } else {
            $this->attributes['items_to_be_offered'] = null;
        }
    }

    public function getItemsToBeOfferedAttribute($value)
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    // ── Lifecycle hooks ──────────────────────────────────────────────────────

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $term = Term::find($m->due_term_id);
            if ($term === null) {
                throw new Exception("Due term not found.");
            }
            $service = Service::find($m->service_id);
            if ($service === null) {
                throw new Exception("Service not found.");
            }

            $m->due_academic_year_id = $term->academic_year_id;
            $m->enterprise_id        = $term->enterprise_id;

            // Quantity must be at least 1
            $quantity   = (int) $m->quantity;
            $m->quantity = $quantity < 1 ? 1 : $quantity;
            $m->total   = 0; // Batch total is 0; per-subscriber totals are computed on processing
        });

        self::deleting(function ($m) {
            // Remove child inventory items
            $m->batchItems()->delete();
            // Note: TransportSubscriptions and ServiceSubscriptions created during processing
            // manage their own cleanup through their own lifecycle hooks.
        });
    }

    // ── Processing ───────────────────────────────────────────────────────────

    /**
     * Atomically claims this batch so exactly one run can process it.
     *
     * This is the fix for the phantom "already subscribed" reports. `is_processed`
     * used to be flipped to 'Yes' only after the loop finished, and a run takes
     * seconds per student, so a second click on the Process button (it opens in a
     * new tab) started a parallel pass over the same list. One pass inserted the
     * subscription, the other found it and reported the student as already
     * subscribed — then whichever finished last overwrote the counters.
     *
     * The single UPDATE below is atomic under InnoDB: of two concurrent callers,
     * exactly one sees an affected-row count of 1.
     *
     * A lock older than LOCK_TIMEOUT_MINUTES is considered abandoned (the run died
     * mid-way) and may be retaken, so a crash can never wedge a batch forever.
     *
     * A re-run takes the very same lock. Reprocessing only lifts the
     * "not already processed" condition — it never bypasses the lock itself, so
     * a reprocess can never run alongside a first run or another reprocess.
     *
     * @param  bool $allowReprocess Permit claiming a batch already marked processed.
     * @return bool true only for the caller that won the claim.
     */
    public function claimForProcessing($allowReprocess = false)
    {
        $by = Auth::user() ?: Admin::user();

        $query = static::query()
            ->where('id', $this->id)
            ->where(function ($q) {
                $q->whereNull('locked_at')
                    ->orWhere('locked_at', '<', Carbon::now()->subMinutes(self::LOCK_TIMEOUT_MINUTES));
            });

        if (!$allowReprocess) {
            $query->where('is_processed', '!=', 'Yes');
        }

        $claimed = $query->update([
            'locked_at'    => Carbon::now(),
            'locked_by_id' => $by ? $by->id : null,
        ]);

        if ($claimed === 1) {
            $this->refresh();
            return true;
        }

        return false;
    }

    /** True when another run currently holds the lock. */
    public function isLocked()
    {
        return $this->locked_at !== null
            && Carbon::parse($this->locked_at)->gt(Carbon::now()->subMinutes(self::LOCK_TIMEOUT_MINUTES));
    }

    /**
     * Creates one ServiceSubscription per listed student.
     *
     * Only call this after claimForProcessing() has returned true.
     *
     * Outcomes are reported in three buckets, never two:
     *   created — a new subscription was written by this run
     *   skipped — the student already held this service for this term; that is a
     *             no-op, not a failure, so re-running a batch is always safe
     *   failed  — a genuine error worth a human looking at
     *
     * @return array{created:int,skipped:int,failed:int,total:int,rows:array,notes:string}
     */
    public function processSubscriptions()
    {
        $result = [
            'created' => 0,
            'skipped' => 0,
            'failed'  => 0,
            'total'   => 0,
            'rows'    => [],
            'notes'   => '',
        ];

        $administrators = $this->administrators;
        if (empty($administrators)) {
            return $result;
        }

        $termId = (int) $this->due_term_id;
        $term   = Term::find($termId);
        if ($term === null) {
            throw new Exception("Batch #{$this->id} has no valid due term (#{$termId}).");
        }

        $service = Service::find($this->service_id);
        if ($service === null) {
            throw new Exception("Batch #{$this->id} refers to service #{$this->service_id}, which no longer exists.");
        }

        // The batch, its term and its service must all belong to the same school.
        // Without this a stale or tampered batch could bill across schools.
        if ((int) $term->enterprise_id !== (int) $this->enterprise_id) {
            throw new Exception("Batch #{$this->id} points at a term belonging to another school.");
        }
        if ((int) $service->enterprise_id !== (int) $this->enterprise_id) {
            throw new Exception("Batch #{$this->id} points at a service belonging to another school.");
        }

        // The academic year always follows the term, never the enterprise's
        // currently active term — batches are routinely built ahead of the term
        // they bill.
        $academicYearId = (int) $term->academic_year_id;

        // A subscription is identified by its due term, so that is what the
        // report names. When a subscription was created is irrelevant here — a
        // batch is routinely built months ahead of the term it bills, and
        // quoting the creation date only reads as if the wrong term were meant.
        $termLabel = 'Term ' . $term->name;
        if ($term->academic_year) {
            $termLabel .= ' ' . $term->academic_year->name;
        }

        $inventoryMode = $this->to_be_managed_by_inventory === 'Yes' ? 'Yes' : 'No';
        $batchItems    = $inventoryMode === 'Yes' ? $this->batchItems()->get() : collect();
        $quantity      = max(1, (int) $this->quantity);

        $notes = [];

        foreach ($administrators as $adminId) {
            $result['total']++;

            $user = User::find($adminId);
            if (!$user) {
                $result['failed']++;
                $notes[]          = "FAILED — student #{$adminId} no longer exists";
                $result['rows'][] = ['status' => 'failed', 'label' => "Student #{$adminId} no longer exists"];
                continue;
            }

            if ((int) $user->enterprise_id !== (int) $this->enterprise_id) {
                $result['failed']++;
                $notes[]          = "FAILED — {$user->name} (#{$user->id}) belongs to another school";
                $result['rows'][] = ['status' => 'failed', 'label' => "{$user->name} — belongs to another school, refused"];
                continue;
            }

            $existing = ServiceSubscription::where([
                'service_id'       => $this->service_id,
                'administrator_id' => $user->id,
                'due_term_id'      => $termId,
            ])->first();

            if ($existing) {
                $result['skipped']++;
                $notes[]          = "SKIPPED — {$user->name} already subscribed for {$termLabel} (subscription #{$existing->id})";
                $result['rows'][] = ['status' => 'skipped', 'label' => "{$user->name} — already subscribed for {$termLabel} (#{$existing->id})"];
                continue;
            }

            try {
                // One student, one transaction. The subscription row is inserted
                // before its `created` hook posts the fee, so a billing failure
                // would otherwise leave a subscription that was never charged —
                // and which every later run would then skip as "already
                // subscribed", so it could never be corrected. Rolling the row
                // back keeps the student retryable.
                DB::transaction(function () use ($user, $quantity, $termId, $academicYearId, $inventoryMode, $batchItems) {
                    $sub                             = new ServiceSubscription();
                    $sub->service_id                 = $this->service_id;
                    $sub->enterprise_id              = $this->enterprise_id;
                    $sub->administrator_id           = $user->id;
                    $sub->quantity                   = $quantity;
                    $sub->due_term_id                = $termId;
                    $sub->due_academic_year_id       = $academicYearId;
                    $sub->link_with                  = $this->link_with;
                    $sub->transport_route_id         = $this->transport_route_id;
                    $sub->trip_type                  = $this->trip_type;
                    $sub->to_be_managed_by_inventory = $inventoryMode;
                    $sub->is_service_offered         = 'No';
                    $sub->is_completed               = 'No';
                    $sub->save();

                    if ($inventoryMode === 'Yes' && $batchItems->count() > 0) {
                        foreach ($batchItems as $batchItem) {
                            if (empty($batchItem->stock_item_category_id)) {
                                continue;
                            }
                            ServiceItemToBeOffered::firstOrCreate(
                                [
                                    'service_subscription_id' => $sub->id,
                                    'stock_item_category_id'  => $batchItem->stock_item_category_id,
                                ],
                                [
                                    'quantity'           => max(1, (int) ($batchItem->quantity ?? 1)),
                                    'is_service_offered' => 'No',
                                    'user_id'            => $user->id,
                                    'enterprise_id'      => $this->enterprise_id,
                                ]
                            );
                        }
                    }
                });

                $result['created']++;
                $result['rows'][] = ['status' => 'created', 'label' => $user->name];
            } catch (QueryException $e) {
                // 23000 = integrity constraint violation, i.e. the unique index on
                // (service_id, administrator_id, due_term_id) caught a race. The
                // row exists and is correct, so this is a skip, never a failure.
                if ((string) $e->getCode() === '23000') {
                    $result['skipped']++;
                    $notes[]          = "SKIPPED — {$user->name} was subscribed for {$termLabel} concurrently by another run";
                    $result['rows'][] = ['status' => 'skipped', 'label' => "{$user->name} — subscribed for {$termLabel} concurrently by another run"];
                    continue;
                }
                // A raw QueryException message carries the whole failing SQL
                // statement. That belongs in the log, not on an operator's screen.
                $result['failed']++;
                $reason           = 'a database error occurred (see the application log)';
                $notes[]          = "FAILED — {$user->name}: {$reason}";
                $result['rows'][] = ['status' => 'failed', 'label' => "{$user->name} — {$reason}"];
                Log::error("Batch #{$this->id} subscription failed for user #{$user->id}: " . $e->getMessage());
            } catch (\Throwable $e) {
                if ((int) $e->getCode() === ServiceSubscription::ERROR_ALREADY_SUBSCRIBED) {
                    $result['skipped']++;
                    $notes[]          = "SKIPPED — {$e->getMessage()}";
                    $result['rows'][] = ['status' => 'skipped', 'label' => $e->getMessage()];
                    continue;
                }
                $result['failed']++;
                $notes[]          = "FAILED — {$user->name}: {$e->getMessage()}";
                $result['rows'][] = ['status' => 'failed', 'label' => "{$user->name} — {$e->getMessage()}"];
                Log::error("Batch #{$this->id} subscription failed for user #{$user->id}: " . $e->getMessage());
            }
        }

        $result['notes'] = implode("\n", $notes);

        return $result;
    }

    /**
     * Writes the outcome and releases the lock. Always runs, even when the loop
     * threw, so a batch is never left locked.
     */
    public function finishProcessing(array $result)
    {
        static::query()->where('id', $this->id)->update([
            'success_count'   => $result['created'],
            'skipped_count'   => $result['skipped'],
            'fail_count'      => $result['failed'],
            'total_count'     => $result['total'],
            'processed_notes' => $result['notes'] !== '' ? $result['notes'] : null,
            'is_processed'    => 'Yes',
            'locked_at'       => null,
            'locked_by_id'    => null,
            'updated_at'      => Carbon::now(),
        ]);

        $this->refresh();
    }

    /** Releases the lock without marking the batch done, so it can be retried. */
    public function releaseLock()
    {
        static::query()->where('id', $this->id)->update([
            'locked_at'    => null,
            'locked_by_id' => null,
        ]);
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function batchItems()
    {
        return $this->hasMany(BatchServiceSubscriptionItem::class);
    }
}
