<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\MenuItem;
use App\Models\StudentHasClass;
use App\Models\StudentHasTheologyClass;
use App\Models\Subject;
use App\Models\Transaction;
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
 
        /*
        set_time_limit(-1);
        $u = Auth::user();
        $ent = $u->ent;
        $classes = [48, 49, 50, 51, 52, 53];
        $count = 0;

        foreach (Administrator::where(['enterprise_id' => 7,'user_type' => 'student'])->get() as $key => $stud) {
            $count++;

            if($count < 30){
                continue;
            }
          
            $x = new Administrator();
            $x->name = $stud->name;
            $x->mother_name = $stud->name;
            $x->first_name = $stud->first_name;
            $x->father_name = $stud->first_name;
            $x->father_phone = '0772' . rand(100, 900) . rand(100, 900) . rand(100, 900);
            $x->mother_phone = '0772' . rand(100, 900) . rand(100, 900) . rand(100, 900);
            $dob = Carbon::now()->subYears(rand(15, 25));
            $dob = $dob->subMonths(rand(1, 11));
            $dob = $dob->subDay(rand(1, 28));
            $x->date_of_birth = $dob;
            $x->last_name = $stud->last_name;
            $x->sex = $stud->sex;
            $x->avatar = $stud->avatar;
            $x->home_address = $stud->home_address;
            $x->current_address = $stud->home_address;
            $x->phone_number_1 = $stud->phone_number_1;
            $x->emergency_person_name = $stud->emergency_person_name;
            $x->emergency_person_phone = $stud->emergency_person_phone;
            $x->email =  $stud->name . rand(100, 1000) . "@gmail.com";
            $x->nationality =  "Ugandan";
            $x->languages =  "Swahili,English";
            $x->enterprise_id = $ent->id;
           
            $x->user_type = 'Student';
            $x->status = 1;
            $x->email = rand(100,1000).$stud->email;
            $x->username = rand(100,1000).$stud->email;
           
            $x->save();

            shuffle($classes);
            shuffle($classes);
            shuffle($classes);
            shuffle($classes);
            shuffle($classes);
            $class = new StudentHasClass();
            $class->enterprise_id =  $ent->id;
            $class->administrator_id =  $x->id;
            $class->academic_class_id =  $classes[rand(0, 4)];
            $class->save(); 
            if ($count > 500) {
                die("done!");
            }
        }*/
     

        /* 

	
	
	
	
updated_at	
created_at	
	
done_selecting_option_courses	
	


    "" => "+256782117770"
    "national_id_number" => "-"
    "passport_number" => "-"
    "tin" => null
    "nssf_number" => null
    "bank_name" => null
    "bank_account_number" => null
    "primary_school_name" => null
    "primary_school_year_graduated" => null
    "seconday_school_name" => null
    "seconday_school_year_graduated" => null
    "high_school_name" => null
    "high_school_year_graduated" => null
    "degree_university_name" => null
    "degree_university_year_graduated" => null
    "masters_university_name" => null
    "masters_university_year_graduated" => null
    "phd_university_name" => null
    "phd_university_year_graduated" => null
    "user_type" => "student"
    "demo_id" => 0
    "user_id" => "3839865"
    "user_batch_importer_id" => 16
    "school_pay_account_id" => "3839865"
    "school_pay_payment_code" => "1003839865"
    "given_name" => "Rahman"
    "deleted_at" => null
    "marital_status" => null
    "verification" => 1
    "current_class_id" => 19


*/
        $u = Admin::user();

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
