<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ServiceSubscription extends Model
{
    use HasFactory;


    protected $fillable = [
        'enterprise_id',
        'service_id',
        'administrator_id',
        'quantity',
        'total',
        'due_academic_year_id',
        'due_term_id',
        'link_with',
        'transport_route_id',
        'trip_type',
        'ref_id',
        'is_processed',
        'processed_at',
        'processed_count',
        'failed_count',
        'to_be_managed_by_inventory',
        'is_service_offered',
        'is_completed',
        'stock_record_id',
        'stock_batch_id',
        'provided_quantity',
        'inventory_provided_date',
        'inventory_provided_by_id',
    ];

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            self::my_update($m);
            Service::update_fees($m->service);
        });
        
        self::updating(function ($m) {
            // Handle inventory changes BEFORE the update is saved
            self::handleInventoryStatusChange($m);
        });
        
        self::updated(function ($m) {
            self::my_update($m);
            Service::update_fees($m->service);
        });

        self::creating(function ($m) {

            $term = Term::find($m->due_term_id);
            if ($term == null) {
                throw new Exception("Due term not found.", 1);
            }
            $service = Service::find($m->service_id);
            if ($service == null) {
                throw new Exception("Service Not Found.", 1);
            }

            //check if the user is already subscribed to the service in this term
            $s = ServiceSubscription::where([
                'service_id' => $m->service_id,
                'administrator_id' => $m->administrator_id,
                'due_term_id' => $m->due_term_id,
            ])->first();
            if ($s != null) {
                throw new Exception("This user is already subscribed to this service in this term.", 1);
            }

            $m->due_academic_year_id = $term->academic_year_id;
            $m->enterprise_id = $term->enterprise_id;
            $quantity = ((int)($m->quantity));
            if ($quantity < 0) {
                $m->quantity = $quantity;
            }
            $m->total = $service->fee * $m->quantity;
            
            // Auto-copy inventory fields from Service
            if (empty($m->to_be_managed_by_inventory) || $m->to_be_managed_by_inventory === 'No') {
                $m->to_be_managed_by_inventory = $service->to_be_managed_by_inventory ?? 'No';
            }
            if (empty($m->items_to_be_offered) && !empty($service->items_to_be_offered)) {
                $m->items_to_be_offered = $service->items_to_be_offered;
            }
            
            return $m;
        });


        self::deleting(function ($m) {
            //service_subscription_id delete transport_subscription
            TransportSubscription::where([
                'service_subscription_id' => $m->id,
            ])->delete();
        });
        self::deleting(function ($m) {

            $term = Term::find($m->due_term_id);
            if ($term == null) {
                throw new Exception("Due term not found.", 1);
            }
            $m->due_academic_year_id = $term->academic_year_id;

            /*  $s = ServiceSubscription::where([
                'service_id' => $m->service_id,
                'administrator_id' => $m->administrator_id,
            ])->first();

            if ($s != null) {
                return false;
            } */
            $quantity = ((int)($m->quantity));
            if ($quantity < 0) {
                $m->quantity = $quantity;
            }

            $t = new Transaction();
            $t->enterprise_id = $m->enterprise_id;
            $t->account_id = $m->sub->account->id;
            $t->amount = $m->total;
            $t->is_contra_entry     = 0;
            $t->payment_date = Carbon::now();
            $by = Auth::user();
            if ($by == null) {
                $by = Admin::user();
            }
            if ($by == null) {
                throw new Exception("User not found", 1);
            }
            $t->created_by_id = $by->id;
            $t->school_pay_transporter_id = "-";
            $t->description = "UGX " . number_format($t->amount) . " was added to this account because this account was removed from " . $m->service->name . " service.";

            $t->save();

            return $m;
        });
    }

    public function service()
    {

        return $this->belongsTo(Service::class);
    }

    public function due_term()
    {
        return $this->belongsTo(Term::class);
    }

    public function sub()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    // Alias for subscriber (used in tracking system)
    public function subscriber()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function getServiceTextAttribute()
    {
        $s = Service::find($this->service_id);
        if ($s == null) {
            return $this->service_id;
        }
        return $s->name;
    }
    public function getDueTermTextAttribute()
    {
        $s = Term::find($this->due_term_id);
        if ($s == null) {
            return $this->due_term_id;
        }
        return $s->name_text;
    }
    public function getAdministratorTextAttribute()
    {
        $s = Administrator::find($this->administrator_id);
        if ($s == null) {
            return $this->administrator_id;
        }
        return $s->name;
    }
    protected $appends = ['service_text', 'due_term_text', 'administrator_text'];

    //my update
    public static function my_update($m)
    {

        if ($m->link_with == 'Transport') {
            $t = TransportSubscription::where([
                'service_subscription_id' => $m->id,
            ])->first();
            if ($t == null) {
                $t = TransportSubscription::where([
                    'user_id' => $m->administrator_id,
                    'term_id' => $m->due_term_id,
                ])->first();
                if ($t == null) {
                    $t = new TransportSubscription();
                }
            }
            $t->service_subscription_id = $m->id;
            $t->enterprise_id = $m->enterprise_id;
            $t->user_id = $m->administrator_id;
            $t->transport_route_id = $m->transport_route_id;
            $t->term_id = $m->due_term_id;
            $t->status = 'Active';
            $t->trip_type = $m->trip_type;
            $t->amount = $m->total;
            $t->description = 'Generated from ' . $m->service->name . ' service subscription. REF: #' . $m->id . "";
            $t->save();
        } else {
            $t = TransportSubscription::where([
                'service_subscription_id' => $m->id,
            ])->first();

            if ($t != null) {
                $t = TransportSubscription::where([
                    'user_id' => $m->administrator_id,
                    'term_id' => $m->due_term_id,
                ])->first();
                if ($t == null) {
                    $t->delete();
                }
            }
        }
    }

    //has many service subscription items
    public function items()
    {
        return $this->hasMany(ServiceSubscriptionItem::class, 'service_subscription_id');
    }
    // Relationship to ServiceItemToBeOffered tracking records
    public function itemsToBeOffered()
    {
        return $this->hasMany(ServiceItemToBeOffered::class, 'service_subscription_id');
    }

    /**
     * Generate ServiceItemToBeOffered records for each stock item in items_to_be_offered
     * Called automatically when subscription is created or inventory is enabled
     */
    public function generateItemsToBeOffered()
    {
        // Only generate if inventory management is enabled
        if ($this->to_be_managed_by_inventory !== 'Yes') {
            return;
        }

        // Get items_to_be_offered array
        $itemIds = $this->items_to_be_offered;
        if (empty($itemIds) || !is_array($itemIds)) {
            return;
        }

        // Create a tracking record for each item
        foreach ($itemIds as $itemId) {
            // Check if already exists
            $exists = ServiceItemToBeOffered::where('service_subscription_id', $this->id)
                ->where('stock_item_category_id', $itemId)
                ->exists();

            if (!$exists) {
                ServiceItemToBeOffered::create([
                    'service_subscription_id' => $this->id,
                    'stock_item_category_id' => $itemId,
                    'quantity' => 1, // Default quantity
                    'is_service_offered' => 'No',
                    'user_id' => $this->user_id,
                    'enterprise_id' => $this->enterprise_id,
                ]);
            }
        }
    }

    /**
     * Regenerate ServiceItemToBeOffered records when items_to_be_offered changes
     * Adds new items and removes old ones (that haven't been offered yet)
     */
    public function regenerateItemsToBeOffered()
    {
        if ($this->to_be_managed_by_inventory !== 'Yes') {
            return;
        }

        $newItemIds = $this->items_to_be_offered ?? [];
        if (!is_array($newItemIds)) {
            return;
        }

        // Get existing tracking records
        $existingRecords = ServiceItemToBeOffered::where('service_subscription_id', $this->id)->get();
        $existingItemIds = $existingRecords->pluck('stock_item_category_id')->toArray();

        // Add new items
        $itemsToAdd = array_diff($newItemIds, $existingItemIds);
        foreach ($itemsToAdd as $itemId) {
            ServiceItemToBeOffered::create([
                'service_subscription_id' => $this->id,
                'stock_item_category_id' => $itemId,
                'quantity' => 1,
                'is_service_offered' => 'No',
                'user_id' => $this->user_id,
                'enterprise_id' => $this->enterprise_id,
            ]);
        }

        // Remove items that are no longer in the list (only if not yet offered)
        $itemsToRemove = array_diff($existingItemIds, $newItemIds);
        if (!empty($itemsToRemove)) {
            ServiceItemToBeOffered::where('service_subscription_id', $this->id)
                ->whereIn('stock_item_category_id', $itemsToRemove)
                ->where('is_service_offered', 'No') // Only remove if not yet offered
                ->delete();
        }
    }

    /**
     * Check if all items have been offered and update completion status
     * Called from ServiceItemToBeOffered::updated() event
     */
    public function checkAndUpdateCompletionStatus()
    {
        $totalItems = $this->itemsToBeOffered()->count();
        $offeredItems = $this->itemsToBeOffered()->where('is_service_offered', 'Yes')->count();

        if ($totalItems > 0 && $totalItems === $offeredItems) {
            // All items have been offered - mark subscription as completed
            $this->is_service_offered = 'Yes';
            $this->saveQuietly(); // Prevent triggering updated event again
        }
    }


    function do_process()
    {


        //$this->items is empty 
        if ($this->items->isEmpty()) {
            $this->is_processed = 'Yes';
            try {
                $this->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
            return;
        }

        foreach ($this->items as $key => $item) {
            if ($item->is_processed == 'Yes') {
                continue;
            }
            $newSub = new ServiceSubscription();
            $newSub->enterprise_id = $this->enterprise_id;
            $newSub->service_id = $item->service_id;
            $service = Service::find($item->service_id);
            if ($service == null) {
                continue;
            }
            $newSub->administrator_id = $this->administrator_id;
            $newSub->quantity = $item->quantity;
            $newSub->total = $service->fee * $item->quantity;
            $newSub->due_academic_year_id = $this->due_academic_year_id;
            $newSub->due_term_id = $this->due_term_id;
            $newSub->is_processed = 'Yes';

            try {
                $newSub->save();
            } catch (\Throwable $th) {
                // throw $th;
            }
            $item->is_processed = 'Yes';
            $item->save();
        }
        $this->is_processed = 'Yes';
        try {
            $this->save();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Handle inventory status changes and create/link stock records
     */
    public static function handleInventoryStatusChange($subscription)
    {
        // Only process if managed by inventory
        if ($subscription->to_be_managed_by_inventory !== 'Yes') {
            return;
        }

        $oldStatus = $subscription->getOriginal('is_service_offered');
        $newStatus = $subscription->is_service_offered;
        
        $needsSave = false;

        // If status changed to 'Yes' (Service Offered), create stock record
        if ($oldStatus !== 'Yes' && $newStatus === 'Yes') {
            // Validate required fields before creating stock record
            if (!$subscription->stock_batch_id) {
                throw new Exception("Stock batch must be selected before marking service as offered.");
            }
            if (!isset($subscription->provided_quantity) || $subscription->provided_quantity === null || $subscription->provided_quantity <= 0) {
                throw new Exception("Provided quantity must be greater than zero before marking service as offered.");
            }
            
            // Create stock record and update subscription fields
            $stockRecord = self::createStockOutRecord($subscription);
            $subscription->is_completed = 'Yes';
            $subscription->inventory_provided_date = now();
            $subscription->stock_record_id = $stockRecord->id; // Ensure stock_record_id is set
            
            $user = Auth::user() ?? Admin::user();
            if ($user) {
                $subscription->inventory_provided_by_id = $user->id;
            }
            // No need to save - changes will be saved by the ongoing update operation
        }
        // If status changed to 'Cancelled', mark as completed (no stock record needed)
        elseif ($newStatus === 'Cancelled') {
            $subscription->is_completed = 'Yes';
        }
        // If status is 'Pending' or 'No', mark as not completed
        elseif ($newStatus === 'Pending' || $newStatus === 'No') {
            $subscription->is_completed = 'No';
        }
    }

    /**
     * Create a stock-out record when inventory is provided to student
     */
    protected static function createStockOutRecord($subscription)
    {
        // Prevent duplicate stock records
        if ($subscription->stock_record_id) {
            $existingRecord = StockRecord::find($subscription->stock_record_id);
            if ($existingRecord) {
                return $existingRecord;
            }
        }

        // Check if stock record already exists for this subscription
        $existingRecord = StockRecord::where('service_subscription_id', $subscription->id)->first();
        if ($existingRecord) {
            $subscription->stock_record_id = $existingRecord->id;
            return $existingRecord;
        }

        $service = Service::find($subscription->service_id);
        if (!$service) {
            throw new Exception("Service not found for subscription ID: {$subscription->id}");
        }

        $student = Administrator::find($subscription->administrator_id);
        if (!$student) {
            throw new Exception("Student not found for subscription ID: {$subscription->id}");
        }

        // Use the user-selected stock batch
        $stockBatch = StockBatch::find($subscription->stock_batch_id);
        
        if (!$stockBatch) {
            throw new Exception("Stock batch ID {$subscription->stock_batch_id} not found. Cannot fulfill inventory request.");
        }
        
        // Verify the batch is not archived and belongs to the same enterprise
        if ($stockBatch->is_archived === 'Yes') {
            throw new Exception("Stock batch ID {$stockBatch->id} is archived and cannot be used.");
        }
        
        if ($stockBatch->enterprise_id != $subscription->enterprise_id) {
            throw new Exception("Stock batch does not belong to the same enterprise.");
        }

        // Use the provided_quantity field (actual quantity to be issued)
        $quantityToIssue = $subscription->provided_quantity;
        
        // Verify sufficient quantity in the batch
        if ($stockBatch->current_quantity < $quantityToIssue) {
            throw new Exception("Insufficient stock quantity in batch. Required: {$quantityToIssue}, Available: {$stockBatch->current_quantity}");
        }

        $user = Auth::user() ?? Admin::user();
        
        // Create stock-out record
        $stockRecord = new StockRecord();
        $stockRecord->enterprise_id = $subscription->enterprise_id;
        $stockRecord->stock_batch_id = $stockBatch->id;
        $stockRecord->stock_item_category_id = $stockBatch->stock_item_category_id;
        $stockRecord->service_subscription_id = $subscription->id;
        $stockRecord->created_by = $user ? $user->id : null;
        $stockRecord->received_by = $subscription->administrator_id; // Student receives the inventory
        $stockRecord->quanity = $quantityToIssue; // Use the provided quantity
        $stockRecord->type = 'OUT';
        $stockRecord->record_date = now();
        $stockRecord->due_term_id = $subscription->due_term_id;
        $stockRecord->description = "Stock issued for service subscription: {$service->name} (ID: {$subscription->id}) - Student: {$student->name} - Quantity: {$quantityToIssue} from Batch #{$stockBatch->id}";
        $stockRecord->save();

        // Link the stock record back to subscription
        $subscription->stock_record_id = $stockRecord->id;

        return $stockRecord;
    }

    /**
     * Find available stock batch for the service
     * This can be customized based on your inventory management logic
     */
    protected static function findAvailableStockBatch($subscription)
    {
        $service = $subscription->service;
        
        // Strategy 1: Look for stock batch with matching service name in description or category
        $stockBatch = \DB::table('stock_batches')
            ->join('stock_item_categories', 'stock_batches.stock_item_category_id', '=', 'stock_item_categories.id')
            ->where('stock_batches.enterprise_id', $subscription->enterprise_id)
            ->where('stock_batches.current_quantity', '>', 0)
            ->where(function($query) use ($service) {
                $query->where('stock_item_categories.name', 'LIKE', '%' . $service->name . '%')
                      ->orWhere('stock_batches.description', 'LIKE', '%' . $service->name . '%');
            })
            ->select('stock_batches.*')
            ->orderBy('stock_batches.created_at', 'asc') // FIFO
            ->first();

        if ($stockBatch) {
            return StockBatch::find($stockBatch->id);
        }

        // Strategy 2: If service has a specific stock_batch_id field (you may need to add this)
        // This would require linking services to specific stock batches in advance
        
        return null;
    }

    /**
     * Relationship to stock record
     */
    public function stockRecord()
    {
        return $this->belongsTo(StockRecord::class, 'stock_record_id');
    }

    /**
     * Relationship to user who provided inventory
     */
    public function inventoryProvidedBy()
    {
        return $this->belongsTo(Administrator::class, 'inventory_provided_by_id');
    }

    /**
     * Scope for subscriptions managed by inventory
     */
    public function scopeManagedByInventory($query)
    {
        return $query->where('to_be_managed_by_inventory', 'Yes');
    }

    /**
     * Scope for pending inventory fulfillment
     */
    public function scopePendingInventory($query)
    {
        return $query->where('to_be_managed_by_inventory', 'Yes')
                    ->whereIn('is_service_offered', ['No', 'Pending']);
    }

    /**
     * Scope for completed inventory fulfillment
     */
    public function scopeInventoryCompleted($query)
    {
        return $query->where('to_be_managed_by_inventory', 'Yes')
                    ->where('is_completed', 'Yes');
    }

    // items_to_be_offered getter to decode JSON
    public function getItemsToBeOfferedAttribute($value)
    {
        try {
            if (isset($value) && !empty($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // items_to_be_offered setter to encode array as JSON
    public function setItemsToBeOfferedAttribute($value)
    {
        try {
            if (is_array($value)) {
                $this->attributes['items_to_be_offered'] = json_encode($value);
            } elseif (is_string($value) && !empty($value)) {
                $this->attributes['items_to_be_offered'] = $value;
            } else {
                $this->attributes['items_to_be_offered'] = null;
            }
        } catch (\Exception $e) {
            $this->attributes['items_to_be_offered'] = null;
        }
    }

    // items_have_been_offered getter to decode JSON
    public function getItemsHaveBeenOfferedAttribute($value)
    {
        try {
            if (isset($value) && !empty($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // items_have_been_offered setter to encode array as JSON
    public function setItemsHaveBeenOfferedAttribute($value)
    {
        try {
            if (is_array($value)) {
                $this->attributes['items_have_been_offered'] = json_encode($value);
            } elseif (is_string($value) && !empty($value)) {
                $this->attributes['items_have_been_offered'] = $value;
            } else {
                $this->attributes['items_have_been_offered'] = null;
            }
        } catch (\Exception $e) {
            $this->attributes['items_have_been_offered'] = null;
        }
    }
}
