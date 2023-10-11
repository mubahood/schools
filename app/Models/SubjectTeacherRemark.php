<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectTeacherRemark extends Model
{
    use HasFactory;

    //comments to comma separated string
    // public function setCommentsAttribute($value)
    // {
    //     //$this->attributes['comments'] = implode(',', $value);
    // } 
}
