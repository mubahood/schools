<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;


    public static function update_data($m)
    {
        if (
            $m->transfer_keyword != null &&
            $m->want_to_transfer != null &&
            (strlen($m->transfer_keyword) > 2) &&
            $m->want_to_transfer == true
        ) {
            $services = Service::where([
                'enterprise_id' => $m->enterprise_id
            ])
                ->where('name', 'like', '%' . $m->transfer_keyword . '%')
                ->get();

            foreach ($services as $key => $service) {
                $service->service_category_id = $m->id;
                $service->save();
            }
            /* $m->balance =  Service::where([
                'account_id' => $m->id
            ])->sum('amount'); */
            $m->want_to_transfer = null;
            $m->transfer_keyword = null;
            $m->save();
        }
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            ServiceCategory::update_data($m);
        });
        self::updated(function ($m) {
            ServiceCategory::update_data($m);
        });
        self::deleting(function ($m) {
            die("You cannot delete this account.");
            if ($m->name == 'Other') {
            }
        });
    }
}
