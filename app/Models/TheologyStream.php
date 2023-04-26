<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'name',
    ];

}
