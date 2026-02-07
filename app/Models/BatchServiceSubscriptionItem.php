<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchServiceSubscriptionItem extends Model
{
    protected $table = 'batch_service_subscription_items';

    protected $fillable = [
        'batch_service_subscription_id',
        'stock_item_category_id',
        'quantity',
    ];

    public function batchServiceSubscription()
    {
        return $this->belongsTo(BatchServiceSubscription::class);
    }

    public function stockItemCategory()
    {
        return $this->belongsTo(StockItemCategory::class);
    }
}
