<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Account;
use App\Models\Enterprise;
use App\Models\MenuItem;
use App\Models\ReportCard;
use App\Models\ReportFinanceModel;
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
        Admin::style('.content-header {display: none;}');
        $u = Admin::user();
        return $content->view('admin.index', [
            'u' => $u
        ]);
    }


    public function stats(Content $content)
    {

        $u = Admin::user();

        /*       if (
            true
        ) {
            $content->row(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::bursarServices()); 
                });
            }); 
        } */

        if (
            $u->isRole('admin') ||
            $u->isRole('hm') ||
            $u->isRole('bursar')
        ) {

            $content->row(function (Row $row) {

                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::students());
                });

                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::teachers());
                });

                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::count_expected_fees());
                });
                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $val = 0;
                    if ($r) {
                        $val = $r->total_expected_service_fees;
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Expected Services Fees',
                        'sub_title' => 'Total sum of service subscription fees of this term.',
                        'number' => "<small>UGX</small>" . number_format($val, 0, '.', ','),
                        'link' => admin_url('service-subscriptions')
                    ]));
                });
                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $val = 0;
                    if ($r) {
                        $val = ($r->total_expected_service_fees + $r->total_expected_tuition);
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => true,
                        'title' => 'Total Expected Income',
                        'sub_title' => 'Sum of tution fees and services subscriptions fees..',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('transactions')
                    ]));
                });

                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $val = 0;
                    if ($r) {
                        $val = ($r->total_payment_total);
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => true,
                        'title' => 'Total Income',
                        'sub_title' => 'Total sum of all payments made this term.',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('transactions')
                    ]));
                });

                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $val = 0;
                    if ($r) {
                        $val = ($r->total_bursaries_funds);
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Total Bursaries Offered',
                        'sub_title' => 'Total sum of all payments made this term.',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('transactions')
                    ]));
                });

                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $val = 0;
                    if ($r) {
                        $val = ($r->total_school_fees_balance);
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => true,
                        'title' => 'School Fees Balance',
                        'sub_title' => 'Total school fees balance of all active students.',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('students-financial-accounts')
                    ]));
                });

                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $val = 0;
                    if ($r) {
                        $val = ($r->total_budget);
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Total Budget',
                        'sub_title' => 'Planned to be spent this term.',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('financial-records-budget')
                    ]));
                });

                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $val = 0;
                    if ($r) {
                        $val = ($r->total_expense);
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Total Expenditure',
                        'sub_title' => 'Total amount of money spent this term.',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('financial-records-expenditure')
                    ]));
                });
                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $val = 0;
                    if ($r) {
                        $val = ($r->total_stock_value);
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Stock Value',
                        'sub_title' => 'Current total stock value in stores.',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('stock-batches')
                    ]));
                });


                $row->column(3, function (Column $column) {
                    $term = Auth::user()->ent->dpTerm();
                    $r = ReportFinanceModel::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->first();
                    $column->append(view('widgets.print-financial-report', [
                        'enterprise_id' => $r->id,
                    ]));
                });

                /*                 
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::count_paid_fees());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::count_unpaid_fees());
                });


                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::staff());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::school_population());
                }); */
            });


            $content->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::recent_fees_payment());
                });
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::fees_collection());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::recent_fees_bills());
                });
            });


            $content->row(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::expenditure());
                });
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::budget());
                });
            });
        }

        /* 
        if (
            $u->isRole('bursar')
        ) {


            $content->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append(Dashboard::bursarFeesServices());
                });
            });

            $content->row(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::bursarFeesExpected());
                });
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::bursarFeesPaid());
                });
            });
        }

 */


        /*         if ($u->isRole('teacher')) {
            $content->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::teacher_marks());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::theology_teacher_marks());
                });
            });
        } */

        return $content;
    }
}
