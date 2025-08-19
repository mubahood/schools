<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryReportCard extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();
        //creating
        self::creating(function ($m) {
            $reportCard = SecondaryReportCard::where([
                'secondary_termly_report_card_id' => $m->secondary_termly_report_card_id,
                'administrator_id' => $m->administrator_id
            ])->first();
            if ($reportCard != null) {
                return false;
            }
        });
    }

    //toDropdownArray
    public static function toDropdownArray($enterprise_id)
    {

        $data = [];
        $items = SecondaryReportCard::where('enterprise_id', $enterprise_id)->get();
        foreach ($items as $item) {
            $pre  = "";
            if ($item->academic_class != null) {
                $pre = $item->academic_class->name_text . ", ";
            }
            if ($item->term != null) {
                $pre .= "Term " . $item->term->name;
            }
            if ($item->owner != null) {
                $pre .= " - " . $item->owner->name;
            }
            $data[$item->id] = $pre;
        }
        return $data;
    }

    //academic_class 
    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    //belongs to secondary_termly_report_card_id relationship
    public function secondary_termly_report_card()
    {
        return $this->belongsTo(TermlySecondaryReportCard::class, 'secondary_termly_report_card_id');
    }


    //belongs to term
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }
    function owner()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    //termly_report_card

    function items()
    {
        return $this->hasMany(SecondaryReportCardItem::class);
    }

    //belongs to termly_SEcondary_report_card_id
    public function termly_secondary_report_card()
    {
        return $this->belongsTo(TermlySecondaryReportCard::class, 'secondary_termly_report_card_id');
    }   

    function get_report_card_items()
    {
        $items =  SecondaryReportCardItem::where([
            'termly_examination_id' => $this->secondary_termly_report_card_id,
            'administrator_id' => $this->administrator_id
        ])->get();
        return $items;
    }
}
