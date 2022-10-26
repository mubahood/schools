<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;
    function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    function theology_classes()
    {
        return $this->hasMany(TheologyClass::class, 'academic_year_id');
    }

    function classes()
    {
        return $this->hasMany(AcademicClass::class, 'academic_year_id');
    }
    function terms()
    {
        return $this->hasMany(Term::class);
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });



        self::created(function ($m) {

            $terms = [1, 2, 3];
            foreach ($terms as $t) {
                $term = new Term();
                $term->enterprise_id = $m->enterprise_id;
                $term->academic_year_id = $m->id;
                $term->name = $t;
                $term->starts = $m->starts;
                $term->ends = $m->ends;
                $term->demo_id = 0;
                $term->details = "Term $t - " . $m->name;
                if ($t == 1) {
                    $term->is_active = 1;
                } else {
                    $term->is_active = 0;
                }
                $term->save();
            }

            $ent = Enterprise::find($m->enterprise_id);
            if ($ent == null) {
                die("Ent not found");
            }
            $classes = [];

            if ($ent->type == 'Primary') {
                $classes[] = 'P.1';
                $classes[] = 'P.2';
                $classes[] = 'P.3';
                $classes[] = 'P.4';
                $classes[] = 'P.5';
                $classes[] = 'P.6';
                $classes[] = 'P.7';
            } else if ($ent->type == 'Secondary') {
                $classes[] = 'S.1';
                $classes[] = 'S.2';
                $classes[] = 'S.3';
                $classes[] = 'S.4';
            } else if ($ent->type == 'Advanced') {
                $classes[] = 'S.1';
                $classes[] = 'S.2';
                $classes[] = 'S.3';
                $classes[] = 'S.4';
                $classes[] = 'S.5';
                $classes[] = 'S.6';
            }

            foreach ($classes as $class) {

                $c = new AcademicClass();
                $c->enterprise_id = $ent->id;
                $c->academic_year_id = $m->id;
                $c->class_teahcer_id = $ent->administrator_id;
                $c->name = $class . " - " . $m->name;
                $c->short_name = $class;
                $c->details = $class . " - " . $m->name;
                $c->save();
            }
        });


        self::creating(function ($m) {
            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                $m->is_active = 0;
            }
        });

        self::updating(function ($m) {
            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                if ($_m->id != $m->id) {
                    if ($_m->is_active == 1) {
                        $m->is_active = 0;
                        admin_error('Warning', "You cannot have two active academic years deativate the other first.");
                    }
                }
            }
        });
    }
}
