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
