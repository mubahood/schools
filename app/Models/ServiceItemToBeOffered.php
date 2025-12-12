<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceItemToBeOffered extends Model
{
    use HasFactory;

    //set the table to be service_items_to_be_offered
    protected $table = 'service_items_to_be_offered'; 

    protected $fillable = [
        'service_subscription_id',
        'stock_item_category_id',
        'quantity',
        'is_service_offered',
        'remarks',
        'user_id',
        'enterprise_id',
        'stock_batch_id',
        'offered_by_id',
        'offered_at'
    ];

    /**
     * Boot method - handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When item is marked as offered, check if all items are complete
        self::updated(function ($item) {
            if ($item->isDirty('is_service_offered') && $item->is_service_offered === 'Yes') {
                // Auto-set offered_at if not already set
                if (!$item->offered_at) {
                    $item->offered_at = now();
                    $item->saveQuietly();
                }
                
                // Trigger completion check on parent subscription
                if ($item->serviceSubscription) {
                    $item->serviceSubscription->checkAndUpdateCompletionStatus();
                }
            }
        });
    }

    // Relationships
    public function serviceSubscription()
    {
        return $this->belongsTo(ServiceSubscription::class, 'service_subscription_id');
    }

    public function stockItemCategory()
    {
        return $this->belongsTo(StockItemCategory::class, 'stock_item_category_id');
    }

    public function stockBatch()
    {
        return $this->belongsTo(StockRecord::class, 'stock_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function offeredBy()
    {
        return $this->belongsTo(User::class, 'offered_by_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    // Accessors
    public function getItemNameAttribute()
    {
        return $this->stockItemCategory->name ?? 'N/A';
    }

    public function getSubscriberNameAttribute()
    {
        return $this->serviceSubscription->subscriber->name ?? 'N/A';
    }

    public function getServiceNameAttribute()
    {
        return $this->serviceSubscription->service->name ?? 'N/A';
    }
}
