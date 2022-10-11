<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRecord extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::created(function ($m) {
            StockItemCategory::update_quantity($m->enterprise_id);
        });

        self::updated(function ($m) {
            StockItemCategory::update_quantity($m->enterprise_id);
        });

        self::creating(function ($m) {

            $batch = StockBatch::find($m->stock_batch_id);
            if ($batch == null) {
                die("Sock batch not found");
            }

            if ($m->quanity > $batch->current_quantity) {
                die("Issufitient amount of stock available.");
            }

            $batch->current_quantity -= $m->quanity;
            $batch->save();

            $m->stock_item_category_id = $batch->cat->id;
            return $m;
        });
    }


    public function getCreatedAtAttribute($v)
    {
        return Carbon::parse($v)->format('d-M-Y');
    }

    function batch()
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }
    function createdBy()
    {
        return $this->belongsTo(Administrator::class, 'created_by');
    }
    function receivedBy()
    {
        return $this->belongsTo(Administrator::class, 'received_by');
    }

    public function cat()
    {
        return $this->belongsTo(StockItemCategory::class, 'stock_item_category_id');
    }
}
