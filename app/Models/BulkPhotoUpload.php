<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkPhotoUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'academic_class_id',
        'file_path',
        'file_name',
        'naming_type',
        'status',
        'error_message',
        'total_images',
        'success_images',
        'failed_images',
        'file_type',
        'images',
        'delete_old_photo',
        'max_image_kb',
        'max_width',
        'max_height',
        'jpeg_quality',
    ];

    protected $casts = [
        'images' => 'array',
        'delete_old_photo' => 'boolean',
    ];


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
