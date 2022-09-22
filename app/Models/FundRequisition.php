<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form\Field\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FundRequisition extends Model
{
    use SoftDeletes;
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::updated(function ($m) {
            if ($m->status != 'Approved') {
                return $m;
            }
            $old = StockBatch::where([
                'fund_requisition_id' => $m->id
            ])->first();

            if ($old != null) {
                return $m;
            }
            $s = new StockBatch();
            $s->enterprise_id = $m->enterprise_id;
            $s->stock_item_category_id = $m->stock_item_category_id;
            $s->original_quantity = $m->quantity;
            $s->current_quantity = $m->quantity;
            $s->fund_requisition_id = $m->id;

            $s->description = "Stocked in " . number_format($m->quantity) . " " . Str::plural($m->cat->measuring_unit) . " " . $m->cat->name;
            $s->photo = null;
            $s->save();
        });
    }


    public function getCreatedAtAttribute($v)
    {
        return Carbon::parse($v)->format('d-M-Y');
    }

    public function cat()
    {
        return $this->belongsTo(StockItemCategory::class, 'stock_item_category_id');
    }

    public function appliedBy()
    {
        return $this->belongsTo(Administrator::class, 'applied_by');
    }
    public function approvedBy()
    {
        return $this->belongsTo(Administrator::class, 'approved_by');
    }
}
