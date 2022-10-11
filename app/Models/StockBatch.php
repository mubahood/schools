<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockBatch extends Model
{
    use HasFactory;
    use SoftDeletes;
    public function cat()
    {
        return $this->belongsTo(StockItemCategory::class, 'stock_item_category_id');
    }
    public function supplier()
    {
        return $this->belongsTo(Administrator::class, 'supplier_id');
    }
   
    public function stock_manager()
    {
        return $this->belongsTo(Administrator::class, 'manager');
    }

    public function getCreatedAtAttribute($v)
    {
        return Carbon::parse($v)->format('d-M-Y');
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $m->current_quantity = $m->original_quantity;
            return $m;
        });



        self::deleting(function ($m) {
            StockRecord::where([
                'stock_batch_id' => $m->id
            ])->delete();
        });

        self::created(function ($m) {
            StockItemCategory::update_quantity($m->enterprise_id);
        });

        self::updated(function ($m) {
            StockItemCategory::update_quantity($m->enterprise_id);
        });

        self::deleted(function ($m) {
            StockItemCategory::update_quantity($m->enterprise_id);
        });
    }
}
