<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * One row here is one award: this student gets this scheme's fund for this term.
 *
 * A "Termly" scheme (is_termly = 1) means the student is awarded again in each
 * term — which is expressed by adding another award row for that term, since every
 * row carries its own due_term_id. It does NOT mean the fund is paid three times
 * over into a single term, which is what the old code did.
 */
class BursaryBeneficiary extends Model
{
    use HasFactory;

    /** Raised when the same student already holds this scheme for this term. */
    const ERROR_ALREADY_AWARDED = 4091;

    protected $fillable = [
        'enterprise_id',
        'administrator_id',
        'bursary_id',
        'description',
        'due_academic_year_id',
        'due_term_id',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            self::prepare($m);
            self::assertNotAlreadyAwarded($m);
        });

        // An edit can move an award onto a student or term that already has one.
        self::updating(function ($m) {
            self::prepare($m);
            self::assertNotAlreadyAwarded($m);
        });

        self::created(function ($m) {
            self::postCredit($m);
        });

        self::deleting(function ($m) {
            self::reverseCredit($m);
        });
    }

    // ── Validation ───────────────────────────────────────────────────────────

    /** Fills in the academic year from the term and normalises the key fields. */
    private static function prepare($m)
    {
        $termId = (int) $m->due_term_id;
        if ($termId < 1) {
            throw new Exception("A due term is required for a bursary award.", 1);
        }

        $term = Term::find($termId);
        if ($term == null) {
            throw new Exception("Due term #{$termId} was not found.", 1);
        }

        if ((int) $m->administrator_id < 1) {
            throw new Exception("A beneficiary is required for a bursary award.", 1);
        }

        if ((int) $m->bursary_id < 1) {
            throw new Exception("A bursary scheme is required.", 1);
        }

        $m->due_academic_year_id = $term->academic_year_id;

        if (empty($m->enterprise_id)) {
            $m->enterprise_id = $term->enterprise_id;
        }
    }

    /**
     * The old code called die() here, which killed the request mid-write: no
     * rollback, no usable message, and an admin left staring at a blank page.
     */
    private static function assertNotAlreadyAwarded($m)
    {
        $query = self::where([
            'due_term_id'      => $m->due_term_id,
            'bursary_id'       => $m->bursary_id,
            'administrator_id' => $m->administrator_id,
        ]);

        if (!empty($m->id)) {
            $query->where('id', '!=', $m->id);
        }

        $existing = $query->first();
        if ($existing == null) {
            return;
        }

        $student = Administrator::find($m->administrator_id);
        $bursary = Bursary::find($m->bursary_id);
        $term    = Term::find($m->due_term_id);

        $who    = $student ? $student->name : "#{$m->administrator_id}";
        $scheme = $bursary ? $bursary->name : "#{$m->bursary_id}";
        $when   = $term ? ('Term ' . $term->name . ($term->academic_year ? ' ' . $term->academic_year->name : '')) : "term #{$m->due_term_id}";

        throw new Exception(
            "{$who} already benefits from {$scheme} in {$when} (award #{$existing->id}). "
                . "A student can only be awarded a scheme once per term.",
            self::ERROR_ALREADY_AWARDED
        );
    }

    // ── Money ────────────────────────────────────────────────────────────────

    /**
     * Resolves the pieces every money movement needs, or explains what is missing.
     *
     * @return array{0:Bursary,1:Account,2:int,3:Term}
     */
    private static function resolveContext($m)
    {
        $bursary = $m->bursary;
        if ($bursary == null) {
            throw new Exception("Bursary scheme #{$m->bursary_id} was not found.", 1);
        }

        $student = $m->beneficiary;
        if ($student == null) {
            throw new Exception("Beneficiary #{$m->administrator_id} was not found.", 1);
        }

        $account = $student->account;
        if ($account == null) {
            $account = Account::create($m->administrator_id);
        }
        if ($account == null) {
            throw new Exception("No financial account could be found or created for {$student->name}.", 1);
        }

        $by = Auth::user();
        if ($by == null) {
            $by = Admin::user();
        }
        if ($by == null) {
            throw new Exception("Cannot record a bursary movement with no authenticated user to attribute it to.", 1);
        }

        $term = Term::find($m->due_term_id);
        if ($term == null) {
            throw new Exception("Due term #{$m->due_term_id} was not found.", 1);
        }

        return [$bursary, $account, $by->id, $term];
    }

    /**
     * Credits the fund once for this award.
     *
     * Idempotent: the transaction carries bursary_beneficiary_id, so an award that
     * has already been paid is never paid again — whatever re-triggers the hook.
     */
    public static function postCredit($m)
    {
        [$bursary, $account, $byId, $term] = self::resolveContext($m);

        $already = Transaction::where('bursary_beneficiary_id', $m->id)
            ->where('amount', '>', 0)->exists();
        if ($already) {
            return false;
        }

        $amount = abs((int) $bursary->fund);
        if ($amount < 1) {
            return false;
        }

        DB::transaction(function () use ($m, $bursary, $account, $byId, $term, $amount) {
            $t = new Transaction();
            $t->enterprise_id             = $m->enterprise_id;
            $t->account_id                = $account->id;
            $t->amount                    = $amount;
            $t->is_contra_entry           = 0;
            $t->payment_date              = Carbon::now();
            $t->created_by_id             = $byId;
            $t->school_pay_transporter_id = "-";
            $t->source                    = 'BURSARY';
            $t->bursary_beneficiary_id    = $m->id;
            // The award belongs to the term it was granted for, not to whichever
            // term happens to be active when the record is saved.
            $t->term_id                   = $term->id;
            $t->academic_year_id          = $term->academic_year_id;
            $t->description = "Bursary funds of UGX " . number_format($amount)
                . " deposited to account by " . $bursary->name . " bursary scheme for Term "
                . $term->name . ($term->academic_year ? ' ' . $term->academic_year->name : '') . ".";
            $t->save();
        });

        return true;
    }

    /**
     * Withdraws exactly what this award paid — no more, no less.
     *
     * The old version always deducted one fund amount, even for a "termly" award
     * that had credited three; the school silently lost the difference. Reversing
     * the linked credits keeps the two sides symmetrical.
     */
    public static function reverseCredit($m)
    {
        [$bursary, $account, $byId, $term] = self::resolveContext($m);

        $credits = Transaction::where('bursary_beneficiary_id', $m->id)
            ->where('amount', '>', 0)->get();

        // Awards made before bursary_beneficiary_id existed have no link to follow,
        // so fall back to the scheme's fund — the original behaviour.
        $amount = $credits->count() > 0
            ? (int) $credits->sum('amount')
            : abs((int) $bursary->fund);

        if ($amount < 1) {
            return false;
        }

        DB::transaction(function () use ($m, $bursary, $account, $byId, $term, $amount) {
            $t = new Transaction();
            $t->enterprise_id             = $m->enterprise_id;
            $t->account_id                = $account->id;
            $t->amount                    = -1 * $amount;
            $t->is_contra_entry           = 0;
            $t->payment_date              = Carbon::now();
            $t->created_by_id             = $byId;
            $t->school_pay_transporter_id = "-";
            $t->source                    = 'BURSARY';
            $t->bursary_beneficiary_id    = $m->id;
            $t->term_id                   = $term->id;
            $t->academic_year_id          = $term->academic_year_id;
            $t->description = "UGX " . number_format($amount)
                . " was deducted from this account because this account was removed from "
                . $bursary->name . " bursary scheme.";
            $t->save();
        });

        return true;
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function bursary()
    {
        return $this->belongsTo(Bursary::class);
    }

    public function due_term()
    {
        return $this->belongsTo(Term::class, 'due_term_id');
    }

    public function beneficiary()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'bursary_beneficiary_id');
    }
}
