<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenericSkill extends Model
{
    use HasFactory;

    //multiselect for comments setter
    public function setCommentsAttribute($value)
    {
        $this->attributes['comments'] = json_encode($value);
    } 
    //getter for comments
    public function getCommentsAttribute($value)
    {
        return json_decode($value, true);
    }

}
