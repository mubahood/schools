<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkRecord extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $old = MarkRecord::where([
                //'termly_report_card_id' => $m->termly_report_card_id,
                'term_id' => $m->term_id,
                'academic_class_id' => $m->academic_class_id,
                'subject_id' => $m->subject_id,
                'administrator_id' => $m->administrator_id,
            ])->first();
            if ($old) {
                throw new Exception("Mark record already exists.", 1);
            }
        });
        
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function termlyReportCard()
    {
        return $this->belongsTo(TermlyReportCard::class);
    }
    public function term()
    {
        return $this->belongsTo(Term::class);
    }
    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }
    public function student()
    {
        return $this->belongsTo(Administrator::class);
    }
}
