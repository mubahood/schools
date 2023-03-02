<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceCategory extends Model
{
    use HasFactory;
 
    public function income()
    {  
        $ent = Enterprise::find($this->enterprise_id);
        if($ent == null){
            return 0;
        }
        $dp = $ent->dpYear();
        if($dp == null){
            return 0;
        }

        $services = "SELECT id FROM services WHERE service_category_id = {$this->id} AND enterprise_id = {$dp->enterprise_id}";
        $active_services = "SELECT sum(total) as tot FROM service_subscriptions WHERE due_academic_year_id = {$dp->id} AND service_id in ($services) AND enterprise_id = {$dp->enterprise_id}";
        $res =DB::select($active_services);
        $tot = 0;
        if(isset($res[0]) && isset($res[0]->tot)){
            $tot = $res[0]->tot;
        }
    
        return $tot;
    }


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
