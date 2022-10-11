<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItemCategory extends Model
{
    use SoftDeletes;
    use HasFactory;

    public static function update_quantity($enterprise_id)
    {
        foreach (StockItemCategory::where([
            'enterprise_id' => $enterprise_id,
        ])->get() as $item) {
            $quantity = StockBatch::where([
                'stock_item_category_id' => $item->id
            ])->sum('current_quantity');
            $item->quantity = $quantity;
            if ($quantity > $item->reorder_level) {
                $item->status = 1;
            } else {
                $item->status = 0;
            }
            $item->save();
        }
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            StockBatch::where([
                'stock_item_category_id' => $m->id
            ])->delete();
        });
    }
}
