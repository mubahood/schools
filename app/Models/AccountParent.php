<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountParent extends Model
{
    use HasFactory; 

    public static function boot()
    {
        parent::boot();

        self::booting(function ($m) {
            die("You cannot delete this account.");
            if ($m->name == 'Other') {
            }
        });
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
