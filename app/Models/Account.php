<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    public static function create($administrator_id)
    {
        $admin = Administrator::where([
            'id' => $administrator_id
        ])->first();
        if ($admin == null) {
            die("Account was not created because admin account was not found.");
        }
        $acc = Account::where(['administrator_id' => $administrator_id])->first();
        if ($acc != null) {
            return $acc;
        }

        $acc =  new Account();
        $acc->enterprise_id = $admin->enterprise_id;
        $acc->name = $admin->name;
        $acc->administrator_id = $administrator_id;
        $acc->save();
        return $acc;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-M-Y');
    }

    function owner()
    {
        return $this->belongsTo(Administrator::class,'administrator_id');
    }
}
