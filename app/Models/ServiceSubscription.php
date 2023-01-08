<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSubscription extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            Service::update_fees($m->service);
        });
        self::creating(function ($m) {

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
            return $m;
        });
    }

    public function service()
    {

        return $this->belongsTo(Service::class);
    }

    public function sub()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
}
