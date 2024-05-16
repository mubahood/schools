<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCardPrint extends Model
{
    use HasFactory;

    //belongs to enterprise
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }
}
