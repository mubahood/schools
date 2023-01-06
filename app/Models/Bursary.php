<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bursary extends Model
{
    use HasFactory;

    public function beneficiaries()
    {
        return  $this->hasMany(BursaryBeneficiary::class,'bursary_id');
    }
}
