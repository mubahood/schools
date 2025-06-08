<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolReport extends Model
{
    use HasFactory;

    //belongs to term
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    //bvelongs to enterprise
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    } 
}
