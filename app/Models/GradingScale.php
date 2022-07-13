<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory;

    public function grade_ranges()
    {
        return $this->hasMany(GradeRange::class);
    }
}
