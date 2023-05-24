<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    use HasFactory;

    public function supplier_order_items()
    {
        return $this->hasMany(SupplierOrderItem::class, 'supplier_order_id');
    }
}
