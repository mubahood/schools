<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Mockery\Matcher\Subset;
use SebastianBergmann\Template\Template;

class Demo extends Model
{
    use HasFactory;

    public function teachers()
    {
        return $this->hasMany(Administrator::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($m) {
            Demo::my_delete($m);
        });

        self::created(function ($m) {
            Demo::my_update($m);
        });

        self::updated(function ($m) {
            Demo::my_update($m);
        });
    }

    public static function my_delete($m)
    {
        Administrator::where([
            'demo_id' => $m->id
        ])->delete();
        Course::where([
            'demo_id' => $m->id
        ])->delete();
        AcademicYear::where([
            'demo_id' => $m->id
        ])->delete();
        AcademicClass::where([
            'demo_id' => $m->id
        ])->delete();
        Subject::where([
            'demo_id' => $m->id
        ])->delete();
    }

    public static function my_update($m)
    {
        if (($m->generate_teachers == 1) && ($m->number_of_teachers > 1)) {
            Demo::do_generate_teachers($m);
        }
        if (($m->create_courses == 1)) {
            Demo::do_create_courses($m);
        }
        if (($m->create_academic_year == 1)) {
            Demo::do_create_academic_year($m);
        }
        if (($m->create_term == 1)) {
            Demo::do_create_term($m);
        }
        if (($m->create_classes == 1)) {
            Demo::do_create_classes($m);
        }
        if (($m->create_subjects == 1)) {
            Demo::do_create_subjects($m);
        }
        if (($m->create_grade_scale == 1)) {
            Demo::do_create_grade_scale($m);
        }
        if (($m->generate_students == 1)) {
            Demo::do_generate_students($m);
        }
        if (($m->generate_marks == 1)) {
            Demo::do_generate_marks($m);
        }
    }

    public static function do_generate_marks($m)
    {

        $marks = Mark::where([
            'enterprise_id' => $m->enterprise_id,
        ])->get();
        if (empty($marks)) {
            die("No marks found");
        }
        foreach ($marks as $mark) {
            $max_mark = ((int)($mark->exam->max_mark));
            if ($max_mark < 1) {
                die("Exam not foudn");
            }
            $mark->score = rand(0, $max_mark);
            $val  = Utils::convert_to_percentage($mark->score, $max_mark);
            if ($val < 20) {
                $mark->remarks = 'Poor';
            } else if ($val < 30) {
                $mark->remarks = 'Fair';
            } else if ($val < 50) {
                $mark->remarks = 'Good';
            } else if ($val < 70) {
                $mark->remarks = 'V.Good';
            } else {
                $mark->remarks = 'Excellent';
            }
            $mark->is_submitted = true;
            $mark->is_missed = false;
            $mark->save();
        }
 
        DB::table('demos')
            ->where('id', $m->id)
            ->update([
                'generate_marks' => 0,
            ]);
    }

    public static function do_create_grade_scale($m)
    {


        if ($m->grade_scale_type == 'primary') {
            $scale = new GradingScale();
            $scale->enterprise_id = $m->enterprise_id;
            $scale->demo_id = $m->id;
            $scale->name = 'Primary school grading';

            $ay = GradingScale::where([
                'name' => $scale->name,
                'enterprise_id' => $m->enterprise_id,
            ])->first();
            if ($ay != null) {
                return;
            }



            $scale->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'F9';
            $range->min_mark = 0;
            $range->max_mark = 49;
            $range->aggregates = 9;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'P8';
            $range->min_mark = 50;
            $range->max_mark = 55;
            $range->aggregates = 8;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'P7';
            $range->min_mark = 56;
            $range->max_mark = 59;
            $range->aggregates = 7;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C6';
            $range->min_mark = 60;
            $range->max_mark = 65;
            $range->aggregates = 6;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C5';
            $range->min_mark = 66;
            $range->max_mark = 69;
            $range->aggregates = 5;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C4';
            $range->min_mark = 70;
            $range->max_mark = 79;
            $range->aggregates = 4;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C3';
            $range->min_mark = 80;
            $range->max_mark = 89;
            $range->aggregates = 3;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'D2';
            $range->min_mark = 90;
            $range->max_mark = 94;
            $range->aggregates = 2;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'D1';
            $range->min_mark = 95;
            $range->max_mark = 100;
            $range->aggregates = 1;
            $range->demo_id = $m->id;
            $range->save();
        } else if ($m->grade_scale_type == 'o_level') {

            $scale = new GradingScale();
            $scale->enterprise_id = $m->enterprise_id;
            $scale->demo_id = $m->id;
            $scale->name = 'O\' Level grading';
            $ay = GradingScale::where([
                'name' => $scale->name,
                'enterprise_id' => $m->enterprise_id,
            ])->first();
            if ($ay != null) {
                return;
            }



            $scale->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'F9';
            $range->min_mark = 0;
            $range->max_mark = 49;
            $range->aggregates = 9;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'P8';
            $range->min_mark = 50;
            $range->max_mark = 55;
            $range->aggregates = 8;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'P7';
            $range->min_mark = 56;
            $range->max_mark = 59;
            $range->aggregates = 7;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C6';
            $range->min_mark = 60;
            $range->max_mark = 65;
            $range->aggregates = 6;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C5';
            $range->min_mark = 66;
            $range->max_mark = 69;
            $range->aggregates = 5;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C4';
            $range->min_mark = 70;
            $range->max_mark = 79;
            $range->aggregates = 4;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C3';
            $range->min_mark = 80;
            $range->max_mark = 89;
            $range->aggregates = 3;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'D2';
            $range->min_mark = 90;
            $range->max_mark = 94;
            $range->aggregates = 2;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'D1';
            $range->min_mark = 95;
            $range->max_mark = 100;
            $range->aggregates = 1;
            $range->demo_id = $m->id;
            $range->save();
        } else if ($m->grade_scale_type == 'a_level') {

            $scale = new GradingScale();
            $scale->enterprise_id = $m->enterprise_id;
            $scale->demo_id = $m->id;
            $scale->name = 'A\' Level grading';
            $ay = GradingScale::where([
                'name' => $scale->name,
                'enterprise_id' => $m->enterprise_id,
            ])->first();
            if ($ay != null) {
                return;
            }


            $scale->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'F9';
            $range->min_mark = 0;
            $range->max_mark = 49;
            $range->aggregates = 9;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'P8';
            $range->min_mark = 50;
            $range->max_mark = 55;
            $range->aggregates = 8;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'P7';
            $range->min_mark = 56;
            $range->max_mark = 59;
            $range->aggregates = 7;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C6';
            $range->min_mark = 60;
            $range->max_mark = 65;
            $range->aggregates = 6;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C5';
            $range->min_mark = 66;
            $range->max_mark = 69;
            $range->aggregates = 5;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C4';
            $range->min_mark = 70;
            $range->max_mark = 79;
            $range->aggregates = 4;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'C3';
            $range->min_mark = 80;
            $range->max_mark = 89;
            $range->aggregates = 3;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'D2';
            $range->min_mark = 90;
            $range->max_mark = 94;
            $range->aggregates = 2;
            $range->demo_id = $m->id;
            $range->save();

            $range = new GradeRange();
            $range->grading_scale_id = $scale->id;
            $range->enterprise_id = $m->id;
            $range->name = 'D1';
            $range->min_mark = 95;
            $range->max_mark = 100;
            $range->aggregates = 1;
            $range->demo_id = $m->id;
            $range->save();
        }

        DB::table('demos')
            ->where('id', $m->id)
            ->update([
                'create_grade_scale' => 0,
                'grade_scale_type' => 0,
            ]);
    }


    public static function do_create_subjects($m)
    {
        $ay = AcademicYear::where([
            'is_active' => 1,
            'enterprise_id' => $m->enterprise_id,
        ])->first();
        if ($ay == null) {
            Demo::do_create_academic_year($m);
        }
        $ay = AcademicYear::where([
            'is_active' => 1,
            'enterprise_id' => $m->enterprise_id,
        ])->first();
        if ($ay == null) {
            die("No active academic found.");
            return;
        }

        $classes = AcademicClass::where([
            'enterprise_id' => $m->enterprise_id,
            'academic_year_id' => $ay->id,
        ])->get();
        if ($classes == null) {
            die("No classes found. Please create one and try again.");
        }

        $teachers = Administrator::where([
            'user_type' => 'employee',
            'enterprise_id' => $m->enterprise_id,
        ])->get();

        $teachers_ids = [];
        foreach ($teachers as $key => $value) {
            $teachers_ids[] = $value->id;
        }
        if (empty($teachers_ids)) {
            die("No teacher found.");
        }

        $courses = Course::where([
            'enterprise_id' => $m->enterprise_id,
        ])->get();
        if ($courses->count() < 1) {
            die("No courses found.");
        }

        foreach ($classes as $class) {
            foreach ($courses as $course) {
                $s = Subject::where([
                    'course_id' => $course->id,
                    'enterprise_id' => $m->enterprise_id,
                    'academic_class_id' => $class->id,
                ])->first();
                if ($s != null) {
                    continue;
                }

                shuffle($teachers_ids);

                $sub = new Subject();
                $sub->enterprise_id = $m->enterprise_id;
                $sub->academic_class_id = $class->id;
                $sub->code = $class->short_name;
                $sub->subject_teacher = $teachers_ids[0];
                $sub->details = 'Generated subject...';
                $sub->course_id = $course->id;
                $sub->subject_name = $course->name;
                $sub->demo_id = $m->id;
                $sub->save();
            }

            DB::table('demos')
                ->where('id', $m->id)
                ->update([
                    'create_subjects' => 0,
                ]);
        }
    }

    public static function do_classess($m)
    {
    }

    public static function do_create_term($m)
    {
        $ay = AcademicYear::where([
            'is_active' => 1,
            'enterprise_id' => $m->enterprise_id,
        ])->first();
        if ($ay == null) {
            Demo::do_create_academic_year($m);
        }
        $ay = AcademicYear::where([
            'is_active' => 1,
            'enterprise_id' => $m->enterprise_id,
        ])->first();
        if ($ay == null) {
            die("No active academic found.");
            return;
        }

        $_term = Term::where([
            'is_active' => 1,
            'enterprise_id' => $m->enterprise_id,
        ])->first();
        if ($_term != null) {
            die("Already have active term.");
            return;
        }


        $term = new Term();
        $term->is_active = 1;
        $term->academic_year_id = $ay->id;
        $term->enterprise_id = $m->enterprise_id;
        $term->demo_id = $m->id;
        $term->details = 'Test term...';
        $term->name = 'Test term';
        $term->starts =  date('Y-m-d');
        $term->ends =  date('Y-m-d');
        $term->save();

        DB::table('demos')
            ->where('id', $m->id)
            ->update([
                'create_term' => 0,
            ]);
    }


    public static function do_create_academic_year($m)
    {
        $_ay = AcademicYear::where([
            'is_active' => 1,
            'enterprise_id' => $m->enterprise_id,
        ])->first();
        if ($_ay != null) {
            return;
        }
        $ay = new AcademicYear();
        $ay->is_active = 1;
        $ay->enterprise_id =  $m->enterprise_id;
        $ay->name =  date('Y');
        $ay->starts =  date('Y-m-d');
        $ay->ends =  (date('Y') + 1) . "-" . date('m-d');
        $ay->demo_id = $m->id;
        $ay->save();

        DB::table('demos')
            ->where('id', $m->id)
            ->update([
                'create_academic_year' => 0,
            ]);
    }


    public static function do_create_classes($m)
    {

        $classess = [];
        if ($m->classes_type == 'primary') {
            $classess = Utils::classes_primary();
        } else if ($m->classes_type == 'o_level') {
            $classess = Utils::classes_secondary();
        } else if ($m->classes_type == 'a_level') {
            $classess = Utils::classes_advanced();
        }

        foreach ($classess as $v) {
            $_c = AcademicClass::where([
                'enterprise_id' => $m->enterprise_id,
                'name' => $v['name'],
            ])->first();
            if ($_c != null) {
                continue;
            }

            $_c = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'name' => $v['name'],
            ])->first();
            if ($_c == null) {
                Demo::do_create_academic_year($m);
            }
            $ay = AcademicYear::where([
                'is_active' => 1,
                'enterprise_id' => $m->enterprise_id,
            ])->first();

            if ($ay == null) {
                die("No active academic year found.");
                return;
            }

            $teachers = Administrator::where([
                'user_type' => 'employee',
                'enterprise_id' => $m->enterprise_id,
            ])->get();

            $teachers_ids = [];
            foreach ($teachers as $key => $value) {
                $teachers_ids[] = $value->id;
            }
            if (empty($teachers_ids)) {
                die("No teacher found.");
            }
            shuffle($teachers_ids);

            $c = new AcademicClass();
            $c->enterprise_id =  $m->enterprise_id;
            $c->class_teahcer_id =  $teachers_ids[0];
            $c->name =  $v['name'];
            $c->short_name =  $v['short_name'];
            $c->demo_id = $m->id;
            $c->academic_year_id = $ay->id;
            $c->details = 'Generated class';
            $c->save();
        }

        DB::table('demos')
            ->where('id', $m->id)
            ->update([
                'create_classes' => 0,
                'classes_type' => '',
            ]);
    }


    public static function do_create_courses($m)
    {
        $courses = [];
        if ($m->courses_type == 'primary') {
            $courses = Utils::courses_primary();
        } else if ($m->courses_type == 'o_level') {
            $courses = Utils::courses_o_level();
        } else if ($m->courses_type == 'a_level') {
            $courses = Utils::courses_a_level();
        }
        foreach ($courses as $v) {
            $_c = Course::where([
                'enterprise_id' => $m->enterprise_id,
                'name' => $v['name'],
            ])->first();
            if ($_c != null) {
                continue;
            }
            $c = new Course();
            $c->enterprise_id =  $m->enterprise_id;
            $c->name =  $v['name'];
            $c->short_name =  $v['short_name'];
            $c->demo_id = $m->id;
            $c->save();
        }

        DB::table('demos')
            ->where('id', $m->id)
            ->update([
                'create_courses' => 0,
                'courses_type' => '',
            ]);
    }

    public static function do_generate_students($m)
    {


        if ($m->number_of_students < 1) {
            return;
        }


        $ay = AcademicYear::where([
            'is_active' => 1,
            'enterprise_id' => $m->enterprise_id,
        ])->first();
        if ($ay == null) {
            die("No active academic found.");
            return;
        }

        $classes = AcademicClass::where([
            'enterprise_id' => $m->enterprise_id,
            'academic_year_id' => $ay->id,
        ])->get();
        if ($classes == null) {
            die("No classes found. Please create one and try again.");
        }
        $class_ids = [];
        foreach ($classes as $v) {
            $class_ids[] = $v->id;
        }

        if (empty($class_ids)) {
            die("No classes found. Please create one and try again.");
        }

        $u = new Administrator();
        $f = Faker::create();

        for ($i = 0; $i < $m->number_of_students; $i++) {
            $sex = ['Male', 'Female'];
            $religion = ['Christian', 'Muslim'];
            $u = new Administrator();
            $num = Administrator::count();
            $num++;
            $u->demo_id = $m->id;
            $u->enterprise_id = $m->enterprise_id;
            $u->username = 'student' . $num . "@gmail.com";
            $u->email = $u->username;
            $u->password = password_hash('4321', PASSWORD_DEFAULT);
            $u->avatar = 'no_image.jpg';
            $u->first_name = $f->name(1);
            $u->emergency_person_name = $f->name(1);
            $u->father_name = $f->name(1);
            $u->mother_name = $f->name(1);
            $u->father_phone = $f->phoneNumber();
            $u->mother_phone = $f->phoneNumber();
            $u->emergency_person_phone = $f->phoneNumber();
            $u->phone_number_1 = $f->phoneNumber;
            $u->last_name = $u->first_name;
            $u->name = $u->first_name . " " . $u->last_name;
            $u->date_of_birth = '1994-08-14';
            $u->place_of_birth = 'Bwera, Kasese';
            $u->home_address = 'Bwera, Kasese';
            $u->current_address = 'Bwera, Kasese';
            $u->nationality = 'Ugandan';
            $u->national_id_number = '1210128991231';
            $u->user_type = 'student';
            shuffle($religion);
            $u->religion = $religion[0];
            shuffle($sex);
            $u->sex = $sex[0];
            $u->save();


            $role = new AdminRoleUser();
            $role->role_id = 4;
            $role->user_id = $u->id;
            $role->save();

            $has_class =  new StudentHasClass();
            $has_class->enterprise_id = $m->enterprise_id;
            shuffle($class_ids);
            $has_class->academic_class_id = $class_ids[0];
            $has_class->administrator_id = $u->id;
            $has_class->stream_id = 1;

            $class = AcademicClass::find($class_ids[0]);
            if ($class ==  null) {
                die("Class not found.");
            }
            $has_class->academic_year_id = $class->id;
            $has_class->save();
        }

        DB::table('demos')
            ->where('id', $m->id)
            ->update([
                'generate_students' => 0,
                'number_of_students' => 0,
            ]);
    }

    public static function do_generate_teachers($m)
    {
        if ($m->number_of_teachers < 1) {
            return;
        }
        $u = new Administrator();
        $f = Faker::create();

        for ($i = 0; $i < $m->number_of_teachers; $i++) {
            $sex = ['Male', 'Female'];
            $religion = ['Christian', 'Muslim'];
            $u = new Administrator();
            $num = Administrator::count();
            $num++;
            $u->demo_id = $m->id;
            $u->enterprise_id = $m->enterprise_id;
            $u->username = 'teacher' . $num . "@gmail.com";
            $u->email = $u->username;
            $u->password = password_hash('4321', PASSWORD_DEFAULT);
            $u->avatar = 'no_image.jpg';
            $u->first_name = $f->name(1);
            $u->emergency_person_name = $f->name(1);
            $u->father_name = $f->name(1);
            $u->mother_name = $f->name(1);
            $u->father_phone = $f->phoneNumber();
            $u->mother_phone = $f->phoneNumber();
            $u->emergency_person_phone = $f->phoneNumber();
            $u->phone_number_1 = $f->phoneNumber;
            $u->last_name = $u->first_name;
            $u->name = $u->first_name . " " . $u->last_name;
            $u->date_of_birth = '1994-08-14';
            $u->place_of_birth = 'Bwera, Kasese';
            $u->home_address = 'Bwera, Kasese';
            $u->current_address = 'Bwera, Kasese';
            $u->nationality = 'Ugandan';
            $u->national_id_number = '1210128991231';
            $u->user_type = 'employee';
            shuffle($religion);
            $u->religion = $religion[0];
            shuffle($sex);
            $u->sex = $sex[0];
            $u->save();

            $role = new AdminRoleUser();
            $role->role_id = 5;
            $role->user_id = $u->id;
            $role->save();
        }

        DB::table('demos')
            ->where('id', $m->id)
            ->update([
                'generate_teachers' => 0,
                'number_of_teachers' => 0,
            ]);
    }
}
