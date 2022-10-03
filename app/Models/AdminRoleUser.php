<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRoleUser extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $role = AdminRoleUser::where([
                'role_id' => $m->role_id,
                'user_id' => $m->user_id,
            ])->first();

            if ($role != null) {
                return false;
            }
        });
    }
}
