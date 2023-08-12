<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyMarkRecord extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $old = TheologyMarkRecord::where([
                //'termly_report_card_id' => $m->termly_report_card_id,
                'term_id' => $m->term_id,
                'theology_subject_id' => $m->theology_subject_id,
                'administrator_id' => $m->administrator_id,
            ])->first();
            if ($old) {
                throw new Exception("Mark record already exists.", 1);
            }
        });

        self::updating(function ($m) {
        });
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }
    public function subject()
    {
        return $this->belongsTo(TheologySubject::class, 'theology_subject_id');
    }
    public function stream()
    {
        return $this->belongsTo(TheologyStream::class, 'theology_stream_id');
    }
    public function academicClass()
    {
        return $this->belongsTo(TheologyClass::class, 'theology_class_id');
    }

    public function termlyReportCard()
    {
        return $this->belongsTo(TheologyTermlyReportCard::class, 'theology_termly_report_card_id');
    }
    public function term()
    {
        return $this->belongsTo(Term::class);
    }
    public function administrator()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
    public function student()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
}
