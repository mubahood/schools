<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryReportCardItem extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        
        self::creating(function ($m) {
            $reportItem = SecondaryReportCardItem::where([
                'secondary_report_card_id' => $m->secondary_report_card_id,
                'secondary_subject_id' => $m->idsecondary_subject_id,
            ])->first();
            if($reportItem != null){
                return false;
            }
        });

        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }

} 
