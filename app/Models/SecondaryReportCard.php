<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryReportCard extends Model
{
    use HasFactory;

    function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }
    function owner()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
    function items()
    {
        return $this->hasMany(SecondaryReportCardItem::class);
    }
}
