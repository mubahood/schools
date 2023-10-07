<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryReportCard extends Model
{
    use HasFactory;

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
}
