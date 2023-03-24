<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Account;
use App\Models\Enterprise;
use App\Models\MenuItem;
use App\Models\ReportCard;
use App\Models\StudentHasClass;
use App\Models\StudentHasTheologyClass;
use App\Models\StudentReportCard;
use App\Models\Subject;
use App\Models\TermlyReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyMark;
use App\Models\TheologyTermlyReportCard;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDO;

class HomeController extends Controller
{
    public function index(Content $content)
    {




        /*
        set_time_limit(-1);
        $x = 0;
         foreach (StudentHasClass::where('academic_class_id', 17)->get() as $key => $s) {
            $x++;
            StudentHasTheologyClass::where([
                'administrator_id' => $s->administrator_id
            ])->delete();

            $th = new StudentHasTheologyClass();
            $th->enterprise_id = $s->enterprise_id;
            $th->administrator_id = $s->administrator_id;
            $th->theology_class_id = 8;
            $th->save();
            echo $x . "<hr>";
        }


        
        Utils::sync_classes(7);
        die("done $x");
     

                 $x =0; 
        foreach (StudentHasClass::where('academic_class_id', 16)->get() as $key => $s) {
            $x++;
            StudentHasTheologyClass::where([
                'administrator_id' => $s->administrator_id
            ])->delete();

            $th = new StudentHasTheologyClass();
            $th->enterprise_id = $s->enterprise_id;
            $th->administrator_id = $s->administrator_id;
            $th->theology_class_id = 9;
            $th->save();
            echo $x . "<hr>";
        }

        
        $x = 0;
        foreach (StudentHasClass::where('academic_class_id', 15)->get() as $key => $s) {
            $x++;
            StudentHasTheologyClass::where([
                'administrator_id' => $s->administrator_id
            ])->delete();

            $th = new StudentHasTheologyClass();
            $th->enterprise_id = $s->enterprise_id;
            $th->administrator_id = $s->administrator_id;
            $th->theology_class_id = 10;
            $th->save();
            echo $x . "<hr>";
        }

        //middle
        foreach (StudentHasClass::where('academic_class_id', 16)->get() as $key => $s) {
            $x++;
            StudentHasTheologyClass::where([
                'administrator_id' => $s->administrator_id
            ])->delete();

            $th = new StudentHasTheologyClass();
            $th->enterprise_id = $s->enterprise_id;
            $th->administrator_id = $s->administrator_id;
            $th->theology_class_id = 9;
            $th->save();
            echo $x . "<hr>";
        }

        //upper
        foreach (StudentHasClass::where('academic_class_id', 17)->get() as $key => $s) {
            $x++;
            StudentHasTheologyClass::where([
                'administrator_id' => $s->administrator_id
            ])->delete();

            $th = new StudentHasTheologyClass();
            $th->enterprise_id = $s->enterprise_id;
            $th->administrator_id = $s->administrator_id;
            $th->theology_class_id = 1;
            $th->save();
            echo $x . "<hr>";
        }


        die("DONE");*/
        /* $i = 0;


        foreach (Transaction::where([])->orderBy('payment_date', 'asc')->get() as $key => $a) {
            $d = Carbon::parse($a->payment_date);
            $min_data = Carbon::parse('15-08-2022');
            if(!$d->isBefore($min_data)){
                continue;  
            }
            $a->delete();
            $i++; 
            echo $d->format('d-M-Y') . "<hr>";
        }
        die("romina => $i"); */

        Admin::style('.content-header {display: none;}');
        $u = Admin::user();
        return $content->view('admin.index', [
            'u' => $u
        ]);
    }
    public function stats(Content $content)
    {

        $u = Admin::user();
        $year = $u->ent->active_academic_year();
        $classes = TheologyClass::where([
            'enterprise_id' => $u->enterprise_id,
            'academic_year_id' => $year->id,
        ])->get();

        $classes_ids = [];
        foreach ($classes as $class) {
            $classes_ids[] = $class->id;
        }

    
        $sql = "SELECT * FROM theology_marks WHERE theology_class_id in (13,
        14,
        15,
        20,
        21,
        22,
        23,
        24,
        25,
        26,
        27)";
        $marks=DB::select($sql);
        foreach ($marks as $mark) {
            $hasClass = StudentHasTheologyClass::where([
                'administrator_id' => $mark->student_id,
                'theology_class_id' => $mark->theology_class_id,
            ])->first();
            if($hasClass == null){
                TheologyMark::where(['id'=>$mark->id])->delete();

                echo "NOOOO";
            }else{
                echo "YESS";
            } 
            echo "<hr>";
        }
        dd("done");
        
        /*
          +"id": 4876
  +"created_at": "2023-03-15 23:07:33"
  +"updated_at": "2023-03-16 21:38:44"
  +"enterprise_id": 7
  +"theology_exam_id": 3
  +"theology_class_id": 13
  +"theology_subject_id": 49
  +"student_id": 3057
  +"teacher_id": 2206
  +"score": 75.0
  +"remarks": "Improve"
  +"is_submitted": 1
  +"is_missed": 1

        theology_marks */


        dd(count($marks));
        dd($classes_ids);

        set_time_limit(-1);
        $users = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'student',
            'status' => 1,
        ])->get();

        foreach ($users as $student) {
            foreach ($classes as $class) {
                $has_classes = StudentHasTheologyClass::where([
                    'theology_class_id' => $class->id,
                    'administrator_id' => $student->id,
                ])->get();
                if (count($has_classes) < 2) {
                    continue;
                }
                echo count($has_classes) . "<hr>";
            }
        }

        dd("done");

        dd(count($users));

        if (
            $u->isRole('admin') ||
            $u->isRole('dos')
        ) {
            $content->row(function (Row $row) {



                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::students());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::teachers());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::staff());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::school_population());
                });
            });
        }




        /*       if (
            $u->isRole('bursar') ||
        ) {
            $u = Admin::user();

            $content
                ->title($u->ent->short_name . ' - Dashboard')
                ->description('Hello ' . $u->name . "!");

            $content->row(function (Row $row) {
                $u = Admin::user();
                if (
                    $u->isRole('admin') ||
                    $u->isRole('bursar')
                ) {
                    $row->column(6, function (Column $column) {
                        $column->append(Dashboard::bursarFeesExpected());
                    });
                    $row->column(6, function (Column $column) {
                        $column->append(Dashboard::bursarFeesPaid());
                    });
                }
            });

            $content->row(function (Row $row) {
                $u = Admin::user();
                if (
                    $u->isRole('admin') ||
                    $u->isRole('bursar')
                ) {
                    $row->column(6, function (Column $column) {
                        $column->append(Dashboard::bursarServices());
                    });
                }
            });

            return $content;
        }
 */

        Admin::style('.content-header {display: none;}');
        $ent = Utils::ent();
        //Utils::reconcile_in_background(Admin::user()->enterprise_id);

        /*       $content
            ->title($ent->name)
            ->description('Dashboard')
            ->row(function (Row $row) {
                $u = Admin::user();

                if (
                    $u->isRole('super-admin')
                ) {
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::all_users());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::all_teachers());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::all_students());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::enterprises());
                    });
                }

                if (
                    $u->isRole('admin') ||
                    $u->isRole('bursar')
                ) {
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::students());
                    });

                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::teachers());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::parents());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::fees());
                    });
                }

                if (
                    $u->isRole('admin') ||
                    $u->isRole('bursar')
                ) {
                    $row->column(6, function (Column $column) {
                        $column->append(Dashboard::income_vs_expenses());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::fees_collected());
                    });
                    $row->column(3, function (Column $column) {
                        $column->append(Dashboard::help_videos());
                    });
                }
            });
 */


        if (
            $u->isRole('admin') ||
            $u->isRole('bursar')
        ) {
            $content->row(function (Row $row) {

                $man = Utils::manifest(Auth::user()->ent);
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::count_expected_fees());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::count_paid_fees());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::count_unpaid_fees());
                });

                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::count_percentage_paid_fees());
                });


                /*    $row->column(3, function (Column $column) {
                    $column->append(Dashboard::students());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::teachers());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::staff());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::school_population());
                }); */
            });
        }


        if (
            $u->isRole('bursar')
        ) {

            $content->row(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::bursarFeesExpected());
                });
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::bursarFeesPaid());
                });
            });
        }




        if ($u->isRole('teacher')) {
            $content->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::teacher_marks());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::theology_teacher_marks());
                });
            });
        }

        return $content;
    }
}
