<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'supplier_order_id',
        'supplier_product_id',
        'name',
        'quantity',
        'unit_price',
        'total',
    ];

    public function supplier_order(){
        return $this->belongsTo(SupplierOrder::class,'supplier_order_id');
    }
}
