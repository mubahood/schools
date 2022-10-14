<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\MenuItem;
use App\Models\Transaction;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

class HomeController extends Controller
{
    public function index(Content $content)
    {
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

        Admin::style('.content-header {display: none;}');
        $ent = Utils::ent();
        Utils::reconcile_in_background(Admin::user()->enterprise_id);

        return $content
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
                    $u->isRole('bursar') ||
                    $u->isRole('dos')
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
            })
            ->row(function (Row $row) {

                $u = Admin::user();
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
    }
}
