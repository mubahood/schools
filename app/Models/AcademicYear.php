<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
            die("You cannot delete this item.");
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
            try {
                AcademicYear::generate_classes($m);
            } catch (\Throwable $th) {
            }
        });


        self::creating(function ($m) {

            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                throw new Exception("You cannot have to active academic years.", 1);
            }
            $m->process_data = 'Yes';
        });

        self::updating(function ($m) {
            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();

            if ($_m != null) {
                if ($_m->id != $m->id) {
                    if ($_m->is_active == 1) {
                        DB::update("update academic_years set is_active = ? where id = ? ", [0, $_m->id]);
                    }
                }
            }

            return $m;
        });

        self::updated(function ($m) {

            if ($m->process_data != 'Yes') {
                return $m;
            }

            if (((int)($m->is_active)) != 1) {
                foreach ($m->terms as $t) {
                    $t->is_active = 0;
                    $t->save();
                }
                foreach ($m->classes as $class) {
                    foreach ($class->students as $student) {
                        $a = $student->student;
                        $is_final = false;
                        if ($a->current_class != null) {
                            if ($a->current_class->level != null) {
                                $is_final = $a->current_class->level->is_final_class;
                            }
                        }

                        if ($is_final) {
                            $a->status = STATUS_NOT_ACTIVE;
                        } else {
                            $a->status = STATUS_PENDING;
                        }
                        $a->save();
                    }
                }
            } else {
                $has_active_term = false;
                foreach ($m->terms as $t) {
                    if ($t->is_active) {
                        $has_active_term = true;
                    }
                }
                if (!$has_active_term) {
                    foreach ($m->terms as $t) {
                        $t->is_active = true;
                        $t->save();
                        break;
                    }
                }

                /* try {
                    AcademicYear::generate_classes($m);   
                } catch (\Throwable $th) {
 
                } */
            }
        });
    }
    /* 

    "id" => 4
    "created_at" => "2022-12-14 20:51:40"
    "updated_at" => "2022-12-14 20:51:40"
    "name" => "Primary one"
    "category" => "Primary"
    "details" => "Primary one"
    "short_name" => "P.1"
    "is_final_class" => 0
    
    */

    public static function generate_classes($m)
    {

        $ent = Enterprise::find($m->enterprise_id);
        if ($ent == null) {
            die("Ent not found");
        }
        $classes = [];

        if ($ent->type == 'Primary') {
            foreach (AcademicClassLevel::where(
                'category',
                'Primary'
            )->orwhere(
                'category',
                'Nursery'
            )->get() as $level) {
                $classes[] = $level;
            }
        } else if ($ent->type == 'Secondary') {
            foreach (AcademicClassLevel::where(
                'category',
                'Secondary'
            )->get() as $level) {
                $classes[] = $level;
            }
        } else if ($ent->type == 'Advanced') {
            foreach (AcademicClassLevel::where(
                'category',
                'Secondary'
            )->orwhere(
                'category',
                'A-Level'
            )->get() as $level) {
                $classes[] = $level;
            }
        }

        foreach ($classes as $class) {

            $_class = AcademicClass::where([
                'enterprise_id' =>  $ent->id,
                'academic_year_id' =>  $m->id,
                'academic_class_level_id' => $class->id,
            ])->first();

            if ($_class != null) {
                AcademicClass::generate_subjects($_class);
                continue;
            }

            $c = new AcademicClass();
            $c->enterprise_id = $ent->id;
            $c->academic_year_id = $m->id;
            $c->class_teahcer_id = $ent->administrator_id;
            $c->name = $class->name;
            $c->short_name = $class->short_name;
            $c->academic_class_level_id = $class->id;
            $c->details = $class->name;
            $c->save();
            AcademicClass::generate_subjects($c);
        }
    }
}
