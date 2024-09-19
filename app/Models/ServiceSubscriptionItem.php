<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSubscriptionItem extends Model
{
    use HasFactory;
    //fillables
    protected $fillable = [
        'service_subscription_id',
        'is_processed',
        'enterprise_id',
        'service_id',
        'administrator_id',
        'quantity',
        'total',
    ];

    //belongs to a service subscription
    public function service_subscription()
    {
        return $this->belongsTo(ServiceSubscription::class);
    }
}
