<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    // ── Accessors / Mutators ─────────────────────────────────────────────────

    public function setAdministratorsAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['administrators'] = json_encode([]);
            return;
        }
        if (is_array($value)) {
            $this->attributes['administrators'] = json_encode(array_values(array_filter($value)));
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

    // ── Relationships ────────────────────────────────────────────────────────

    public function batchItems()
    {
        return $this->hasMany(BatchServiceSubscriptionItem::class);
    }
}
