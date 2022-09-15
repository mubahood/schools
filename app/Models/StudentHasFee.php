<?php

namespace App\Models;

use Encore\Admin\Form\Field\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHasFee extends Model
{
    use HasFactory; 

    public function fee()
    {
        return $this->belongsTo(AcademicClassFee::class,'academic_class_fee_id');
    }

}
