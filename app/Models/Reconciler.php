<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reconciler extends Model
{
    use HasFactory;

    //enterprise
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }
}
