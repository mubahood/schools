<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            if ($m->type == 'CASH_ACCOUNT') {
                $cash_acc = Account::where([
                    'type' => 'CASH_ACCOUNT',
                    'enterprise_id' => $m->enterprise_id,
                ])->first();
                if ($cash_acc != null) {
                    return false;
                }
            }
            if ($m->type == 'FEES_ACCOUNT') {
                $acc = Account::where([
                    'type' => 'FEES_ACCOUNT',
                    'enterprise_id' => $m->enterprise_id,
                ])->first();
                if ($acc != null) {
                    return false;
                }
            }
        });
    }

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
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
