<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkPhotoUpload extends Model
{
    use HasFactory;


    //setter images to json
    public function setImagesAttribute($value)
    {
        $this->attributes['images'] = json_encode($value ?? []);
    }

    //getter images to array
    public function getImagesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }
}
