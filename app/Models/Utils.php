<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utils  extends Model
{
    public static function ent()
    {
        $subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
        $subdomain = 'sudais';
        $ent = Enterprise::where([ 
            'subdomain' => $subdomain
        ])->first();
        if ($ent == null) {
            $ent = Enterprise::find(1);
        }

        return $ent;
    }
}
/* $conn = new mysqli(
    env('DB_HOST'),
    env('DB_USERNAME'),
    env('DB_PASSWORD'),
    env('DB_DATABASE'),
);
die(env('DB_DATABASE')); */
