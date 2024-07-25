<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bursary extends Model
{
    use HasFactory;

    public function beneficiaries()
    {
        return  $this->hasMany(BursaryBeneficiary::class, 'bursary_id');
    }

    //name_text
    public function getNameTextAttribute()
    {
        return $this->name . " (UGX " . number_format($this->fund) . ")";
    }
}
