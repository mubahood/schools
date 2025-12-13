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

        // When item is marked as offered, create stock-out record and update subscription
        self::updated(function ($item) {
            if ($item->isDirty('is_service_offered') && $item->is_service_offered === 'Yes') {
                // Auto-set offered_at if not already set
                if (!$item->offered_at) {
                    $item->offered_at = now();
                    $item->saveQuietly();
                }
                
                // Create stock-out record
                $item->createStockOutRecord();
                
                // Trigger completion check on parent subscription
                if ($item->serviceSubscription) {
                    $item->serviceSubscription->checkAndUpdateCompletionStatus();
                }
            }
        });
    }

    /**
     * Create a stock-out record when item is marked as offered
     */
    public function createStockOutRecord()
    {
        // Validate required fields
        if (!$this->stock_batch_id) {
            throw new \Exception("Stock batch must be selected before marking item as offered.");
        }
        
        if (!$this->quantity || $this->quantity <= 0) {
            throw new \Exception("Quantity must be greater than zero.");
        }

        // Check if stock record already exists for this item
        $existingRecord = StockRecord::where('service_subscription_id', $this->service_subscription_id)
            ->where('stock_batch_id', $this->stock_batch_id)
            ->where('stock_item_category_id', $this->stock_item_category_id)
            ->where('type', 'OUT')
            ->where('description', 'LIKE', '%Service Item #' . $this->id . '%')
            ->first();
        
        if ($existingRecord) {
            // Update existing record if quantity changed
            if ($existingRecord->quanity != -1 * abs($this->quantity)) {
                $existingRecord->quanity = abs($this->quantity);
                $existingRecord->description = $this->generateStockRecordDescription();
                $existingRecord->save();
            }
            return $existingRecord;
        }

        // Get related entities
        $batch = StockBatch::find($this->stock_batch_id);
        if (!$batch) {
            throw new \Exception("Stock batch #{$this->stock_batch_id} not found.");
        }

        // Verify sufficient quantity
        if ($batch->current_quantity < $this->quantity) {
            throw new \Exception("Insufficient stock in batch. Available: {$batch->current_quantity}, Required: {$this->quantity}");
        }

        $subscription = $this->serviceSubscription;
        if (!$subscription) {
            throw new \Exception("Service subscription not found.");
        }

        $student = $subscription->subscriber;
        if (!$student) {
            throw new \Exception("Student not found for subscription.");
        }

        // Get current user
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        if (!$currentUser) {
            $currentUser = \Encore\Admin\Facades\Admin::user();
        }
        
        // Create stock-out record
        $stockRecord = new StockRecord();
        $stockRecord->enterprise_id = $this->enterprise_id;
        $stockRecord->stock_batch_id = $this->stock_batch_id;
        $stockRecord->stock_item_category_id = $this->stock_item_category_id;
        $stockRecord->service_subscription_id = $this->service_subscription_id;
        $stockRecord->created_by = $this->offered_by_id ?? ($currentUser ? $currentUser->id : $this->user_id);
        $stockRecord->received_by = $subscription->administrator_id; // Student receives the item
        $stockRecord->quanity = abs($this->quantity); // Will be converted to negative in boot method
        $stockRecord->type = 'OUT';
        $stockRecord->record_date = $this->offered_at ?? now();
        $stockRecord->due_term_id = $subscription->due_term_id;
        $stockRecord->description = $this->generateStockRecordDescription();
        $stockRecord->save();

        return $stockRecord;
    }

    /**
     * Generate description for stock record
     */
    protected function generateStockRecordDescription()
    {
        $subscription = $this->serviceSubscription;
        $student = $subscription->subscriber ?? null;
        $service = $subscription->service ?? null;
        $item = $this->stockItemCategory ?? null;

        return sprintf(
            "Stock OUT for Service Item #%d - %s - Student: %s - Service: %s - Qty: %s",
            $this->id,
            $item ? $item->name : 'Item',
            $student ? $student->name : 'N/A',
            $service ? $service->name : 'N/A',
            $this->quantity
        );
    }

    /**
     * Relationship to stock record
     */
    public function stockRecord()
    {
        return $this->hasOne(StockRecord::class, 'service_subscription_id', 'service_subscription_id')
            ->where('stock_batch_id', $this->stock_batch_id)
            ->where('stock_item_category_id', $this->stock_item_category_id)
            ->where('type', 'OUT');
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
