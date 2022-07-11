<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermlyReportCard extends Model
{
    use HasFactory;

    public static function boot()
    {

        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $_m = Term::find($m->term_id);
            if ($_m == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $_m->academic_year_id;
            return $m;
        });

        self::updating(function ($m) {
            $_m = Term::find($m->academic_class_id);
            if ($_m == null) {
                die("Class not found.");
            }
            $m->academic_year_id = $_m->academic_year_id;
            return $m;
        });
    }
}
