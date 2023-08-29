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

    public function theology_class()
    {
        return $this->belongsTo(TheologyClass::class, 'theology_class_id',);
    }

    public function studentHasTheologyClasses(){
        return $this->hasMany(StudentHasTheologyClass::class,'theology_stream_id');
    }
}
