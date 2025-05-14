<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Account;
use App\Models\Enterprise;
use App\Models\Manifest;
use App\Models\MenuItem;
use App\Models\ReportCard;
use App\Models\ReportFinanceModel;
use App\Models\StockBatch;
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

use App\Models\PassengerRecord;
use App\Models\TransportSubscription;
use App\Models\TransportRoute;
use App\Models\Trip;
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
 
    public function transportStats(Content $content)
    {
        $u            = Admin::user();
        $eid          = $u->enterprise_id;
        $now          = Carbon::now();
        $currStart    = $now->copy()->startOfMonth();
        $currEnd      = $now;
        $lastStart    = $now->copy()->subMonth()->startOfMonth();
        $lastEnd      = $now->copy()->subMonth()->endOfMonth();
        $twoStart     = $now->copy()->subMonths(2)->startOfMonth();
        $twoEnd       = $now->copy()->subMonths(2)->endOfMonth();

        //
        // 1. Subscription & Billing
        //

        // A) Active subscriptions by trip type
        $currByType = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','active')
            ->whereBetween('created_at', [$currStart,$currEnd])
            ->groupBy('trip_type')
            ->select('trip_type', DB::raw('count(*) as cnt'))
            ->pluck('cnt','trip_type');

        $lastByType = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','active')
            ->whereBetween('created_at', [$lastStart,$lastEnd])
            ->groupBy('trip_type')
            ->select('trip_type', DB::raw('count(*) as cnt'))
            ->pluck('cnt','trip_type');

        // B) New vs Renewals
        $currUsers = TransportSubscription::where('enterprise_id',$eid)
            ->whereBetween('created_at', [$currStart,$currEnd])
            ->pluck('user_id')->unique();

        $lastUsers = TransportSubscription::where('enterprise_id',$eid)
            ->whereBetween('created_at', [$lastStart,$lastEnd])
            ->pluck('user_id')->unique();

        $prevUsers = TransportSubscription::where('enterprise_id',$eid)
            ->whereBetween('created_at', [$twoStart,$twoEnd])
            ->pluck('user_id')->unique();

        [$currNew, $currRenew] = [
          $currUsers->diff($lastUsers)->count(),
          $currUsers->intersect($lastUsers)->count(),
        ];
        [$lastNew, $lastRenew] = [
          $lastUsers->diff($prevUsers)->count(),
          $lastUsers->intersect($prevUsers)->count(),
        ];

        // C) Revenue breakdown
        $currRevenue = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','active')
            ->whereBetween('created_at', [$currStart,$currEnd])
            ->sum('amount');
        $lastRevenue = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','active')
            ->whereBetween('created_at', [$lastStart,$lastEnd])
            ->sum('amount');

        $currTop = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','active')
            ->whereBetween('created_at', [$currStart,$currEnd])
            ->groupBy('transport_route_id')
            ->select('transport_route_id', DB::raw('sum(amount) as rev'))
            ->orderByDesc('rev')
            ->first();
        $lastTop = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','active')
            ->whereBetween('created_at', [$lastStart,$lastEnd])
            ->groupBy('transport_route_id')
            ->select('transport_route_id', DB::raw('sum(amount) as rev'))
            ->orderByDesc('rev')
            ->first();

        $currTopName = $currTop
            ? TransportRoute::find($currTop->transport_route_id)->name
            : '—';
        $lastTopName = $lastTop
            ? TransportRoute::find($lastTop->transport_route_id)->name
            : '—';

        // D) Outstanding balances
        $currTotalSubs    = TransportSubscription::where('enterprise_id',$eid)
            ->whereBetween('created_at',[$currStart,$currEnd])->count();
        $currOutCount     = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','inactive')
            ->whereBetween('created_at',[$currStart,$currEnd])->count();
        $lastTotalSubs    = TransportSubscription::where('enterprise_id',$eid)
            ->whereBetween('created_at',[$lastStart,$lastEnd])->count();
        $lastOutCount     = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','inactive')
            ->whereBetween('created_at',[$lastStart,$lastEnd])->count();

        $currOutPercent = $currTotalSubs
            ? round($currOutCount/$currTotalSubs*100,1)
            : 0;
        $lastOutPercent = $lastTotalSubs
            ? round($lastOutCount/$lastTotalSubs*100,1)
            : 0;

        //
        // 2. Trip & Passenger Insights
        //
        $currTrips = Trip::where('enterprise_id',$eid)
            ->whereBetween('date',[$currStart,$currEnd])->get();
        $lastTrips = Trip::where('enterprise_id',$eid)
            ->whereBetween('date',[$lastStart,$lastEnd])->get();

        $currTotalTrips = $currTrips->count();
        $lastTotalTrips = $lastTrips->count();

        $currCompleted = $currTrips->where('status','Completed');
        $lastCompleted = $lastTrips->where('status','Completed');

        $currAvgLoad   = $currCompleted->count()
            ? round($currCompleted->sum('actual_passengers')/$currCompleted->count(),1)
            : 0;
        $lastAvgLoad   = $lastCompleted->count()
            ? round($lastCompleted->sum('actual_passengers')/$lastCompleted->count(),1)
            : 0;

        $currNoShow   = $currCompleted->sum('absent_passengers');
        $lastNoShow   = $lastCompleted->sum('absent_passengers');
        $currExpSum   = $currCompleted->sum('expected_passengers') ?: 1;
        $lastExpSum   = $lastCompleted->sum('expected_passengers') ?: 1;

        $currNoShowRate = round($currNoShow/$currExpSum*100,1);
        $lastNoShowRate = round($lastNoShow/$lastExpSum*100,1);

        $currDirCounts = $currTrips->groupBy('trip_direction')->map->count();
        $lastDirCounts = $lastTrips->groupBy('trip_direction')->map->count();

        $currDirPerc = [
          'To School'   => round(($currDirCounts['To School'] ?? 0)/($currTotalTrips?:1)*100,1),
          'From School' => round(($currDirCounts['From School'] ?? 0)/($currTotalTrips?:1)*100,1),
        ];
        $lastDirPerc = [
          'To School'   => round(($lastDirCounts['To School'] ?? 0)/($lastTotalTrips?:1)*100,1),
          'From School' => round(($lastDirCounts['From School'] ?? 0)/($lastTotalTrips?:1)*100,1),
        ];

        //
        // 3. Charts (2 examples)
        //
        // A) Daily boardings (last 7 days)
        $chartA_labels = [];
        $chartA_data   = [];
        for ($i=6; $i>=0; $i--) {
            $day = Carbon::today()->subDays($i);
            $chartA_labels[] = $day->format('d M');
            $chartA_data[]   = PassengerRecord::whereHas('trip', fn($q)=> 
                                    $q->where('enterprise_id',$eid))
                                ->whereDate('created_at',$day)
                                ->whereIn('status',['Onboard','Arrived'])
                                ->count();
        }
        // B) Revenue by route (current month)
        $revRows = TransportSubscription::where('enterprise_id',$eid)
            ->where('status','active')
            ->whereBetween('created_at',[$currStart,$currEnd])
            ->groupBy('transport_route_id')
            ->select('transport_route_id', DB::raw('sum(amount) as revenue'))
            ->get();
        $chartB_labels = $revRows->map(fn($r)=>TransportRoute::find($r->transport_route_id)->name)->toArray();
        $chartB_data   = $revRows->pluck('revenue')->toArray();

        return $content
            ->header('Transport Dashboard')
            ->description('Current vs Last Month — key KPIs at a glance')
            ->body(view('admin.transport.stats', compact(
              'currByType','lastByType',
              'currNew','currRenew','lastNew','lastRenew',
              'currRevenue','lastRevenue',
              'currTopName','lastTopName',
              'currOutCount','lastOutCount','currOutPercent','lastOutPercent',
              'currTotalTrips','lastTotalTrips',
              'currAvgLoad','lastAvgLoad',
              'currNoShowRate','lastNoShowRate',
              'currDirPerc','lastDirPerc',
              'chartA_labels','chartA_data','chartB_labels','chartB_data'
            )));
    } 


    
    public function stats(Content $content)
    {

        $u = Admin::user();

        //$warnings = Utils::get_system_warnings($u->ent);

        if (!empty($warnings)) {
            $content->row(function (Row $row) use ($warnings) {
                $row->column(12, function (Column $column) use ($warnings) {
                    $column->append(view('widgets.system-warnings', [
                        'warnings' => $warnings
                    ]));
                });
            });
        }
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
                    if ($r == null) {
                        $r = new ReportFinanceModel();
                        $r->enterprise_id = $term->enterprise_id;
                        $r->term_id = $term->id;
                        $r->academic_year_id = $term->academic_year_id;
                        $r->save();
                        $r = ReportFinanceModel::where([
                            'enterprise_id' => $term->enterprise_id,
                            'term_id' => $term->id
                        ])->first();
                    }


                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Expected Services Fees',
                        'sub_title' => 'Total sum of service subscription fees of this term.',
                        'number' => "<small>UGX</small>" . number_format(
                            Manifest::get_total_expected_service_fees(Admin::user()),
                            0,
                            '.',
                            ','
                        ),
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
                        $val = ($r->total_bursaries_funds);
                    }
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Total Bursaries Offered',
                        'sub_title' => 'Total sum of bursaries offered this term.',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('transactions')
                    ]));
                });
                $row->column(3, function (Column $column) {

                    $xpected_fees = Manifest::get_total_expected_tuition(Auth::user());
                    $xpected_services = Manifest::get_total_expected_service_fees(Admin::user());
                    $val = $xpected_fees + $xpected_services;

                    $column->append(view('widgets.box-5', [
                        'is_dark' => true,
                        'title' => 'Total Expected Income',
                        'sub_title' => 'Sum of tution fees and services subscriptions fees..',
                        'number' => "<small>UGX</small>" . number_format($val),
                        'link' => admin_url('transactions')
                    ]));
                });

                $row->column(3, function (Column $column) {
                    /* $total_expected = (Manifest::get_total_expected_tuition(Auth::user()) + Manifest::get_total_expected_tuition(Auth::user()));
                    $total_balance = Manifest::get_total_fees_balance(Auth::user());
                    $paid = $total_expected + $total_balance; */
                    $u = Admin::user();
                    $ent = $u->ent;
                    $term = $ent->active_term();
                    if ($term == null) {
                        $column->append(view('widgets.box-5', [
                            'is_dark' => true,
                            'title' => 'Total Income',
                            'sub_title' => 'Total sum of all payments made this term.',
                            'number' => "<small>UGX</small>" . number_format(0),
                            'link' => admin_url('transactions')
                        ]));
                        return;
                    }
                    //postive transactions made this term
                    $transsactions_tot = Transaction::where([
                        'enterprise_id' => $ent->id,
                        'term_id' => $term->id,
                    ])
                        ->where('amount', '>', 0)
                        ->sum('amount');

                    $column->append(view('widgets.box-5', [
                        'is_dark' => true,
                        'title' => 'Total Income',
                        'sub_title' => 'Total sum of all payments made this term.',
                        'number' => "<small>UGX</small>" . number_format($transsactions_tot),
                        'link' => admin_url('transactions')
                    ]));
                });



                $row->column(3, function (Column $column) {
                    $u = Admin::user();
                    //ids of active students
                    $students = User::where([
                        'user_type' => 'STUDENT',
                        'status' => 1,
                        'enterprise_id' => $u->enterprise_id
                    ])->get()->pluck('id')->toArray();

                    $total_balance_of_students = Account::whereIn('administrator_id', $students)->sum('balance');

                    $column->append(view('widgets.box-5', [
                        'is_dark' => true,
                        'title' => 'School Fees Balance',
                        'sub_title' => 'Total school fees balance of all active students.',
                        'number' => "<small>UGX</small>" . number_format(
                            $total_balance_of_students
                        ),
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
                    $val = StockBatch::where([
                        'enterprise_id' => $term->enterprise_id,
                        'term_id' => $term->id
                    ])->sum('worth');
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
