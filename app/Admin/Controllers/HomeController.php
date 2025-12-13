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
use App\Models\StockItemCategory;
use App\Models\StockRecord;
use App\Models\StudentHasSemeter;
use App\Models\TransportSubscription;
use App\Models\TransportRoute;
use App\Models\Trip;
use App\Models\UniversityProgramme;
use App\Services\OnboardingProgressService;
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
        $currByType = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'active')
            ->whereBetween('created_at', [$currStart, $currEnd])
            ->groupBy('trip_type')
            ->select('trip_type', DB::raw('count(*) as cnt'))
            ->pluck('cnt', 'trip_type');

        $lastByType = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'active')
            ->whereBetween('created_at', [$lastStart, $lastEnd])
            ->groupBy('trip_type')
            ->select('trip_type', DB::raw('count(*) as cnt'))
            ->pluck('cnt', 'trip_type');

        // B) New vs Renewals
        $currUsers = TransportSubscription::where('enterprise_id', $eid)
            ->whereBetween('created_at', [$currStart, $currEnd])
            ->pluck('user_id')->unique();

        $lastUsers = TransportSubscription::where('enterprise_id', $eid)
            ->whereBetween('created_at', [$lastStart, $lastEnd])
            ->pluck('user_id')->unique();

        $prevUsers = TransportSubscription::where('enterprise_id', $eid)
            ->whereBetween('created_at', [$twoStart, $twoEnd])
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
        $currRevenue = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'active')
            ->whereBetween('created_at', [$currStart, $currEnd])
            ->sum('amount');
        $lastRevenue = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'active')
            ->whereBetween('created_at', [$lastStart, $lastEnd])
            ->sum('amount');

        $currTop = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'active')
            ->whereBetween('created_at', [$currStart, $currEnd])
            ->groupBy('transport_route_id')
            ->select('transport_route_id', DB::raw('sum(amount) as rev'))
            ->orderByDesc('rev')
            ->first();
        $lastTop = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'active')
            ->whereBetween('created_at', [$lastStart, $lastEnd])
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
        $currTotalSubs    = TransportSubscription::where('enterprise_id', $eid)
            ->whereBetween('created_at', [$currStart, $currEnd])->count();
        $currOutCount     = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'inactive')
            ->whereBetween('created_at', [$currStart, $currEnd])->count();
        $lastTotalSubs    = TransportSubscription::where('enterprise_id', $eid)
            ->whereBetween('created_at', [$lastStart, $lastEnd])->count();
        $lastOutCount     = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'inactive')
            ->whereBetween('created_at', [$lastStart, $lastEnd])->count();

        $currOutPercent = $currTotalSubs
            ? round($currOutCount / $currTotalSubs * 100, 1)
            : 0;
        $lastOutPercent = $lastTotalSubs
            ? round($lastOutCount / $lastTotalSubs * 100, 1)
            : 0;

        //
        // 2. Trip & Passenger Insights
        //
        $currTrips = Trip::where('enterprise_id', $eid)
            ->whereBetween('date', [$currStart, $currEnd])->get();
        $lastTrips = Trip::where('enterprise_id', $eid)
            ->whereBetween('date', [$lastStart, $lastEnd])->get();

        $currTotalTrips = $currTrips->count();
        $lastTotalTrips = $lastTrips->count();

        $currCompleted = $currTrips->where('status', 'Completed');
        $lastCompleted = $lastTrips->where('status', 'Completed');

        $currAvgLoad   = $currCompleted->count()
            ? round($currCompleted->sum('actual_passengers') / $currCompleted->count(), 1)
            : 0;
        $lastAvgLoad   = $lastCompleted->count()
            ? round($lastCompleted->sum('actual_passengers') / $lastCompleted->count(), 1)
            : 0;

        $currNoShow   = $currCompleted->sum('absent_passengers');
        $lastNoShow   = $lastCompleted->sum('absent_passengers');
        $currExpSum   = $currCompleted->sum('expected_passengers') ?: 1;
        $lastExpSum   = $lastCompleted->sum('expected_passengers') ?: 1;

        $currNoShowRate = round($currNoShow / $currExpSum * 100, 1);
        $lastNoShowRate = round($lastNoShow / $lastExpSum * 100, 1);

        $currDirCounts = $currTrips->groupBy('trip_direction')->map->count();
        $lastDirCounts = $lastTrips->groupBy('trip_direction')->map->count();

        $currDirPerc = [
            'To School'   => round(($currDirCounts['To School'] ?? 0) / ($currTotalTrips ?: 1) * 100, 1),
            'From School' => round(($currDirCounts['From School'] ?? 0) / ($currTotalTrips ?: 1) * 100, 1),
        ];
        $lastDirPerc = [
            'To School'   => round(($lastDirCounts['To School'] ?? 0) / ($lastTotalTrips ?: 1) * 100, 1),
            'From School' => round(($lastDirCounts['From School'] ?? 0) / ($lastTotalTrips ?: 1) * 100, 1),
        ];

        //
        // 3. Charts (2 examples)
        //
        // A) Daily boardings (last 7 days)
        $chartA_labels = [];
        $chartA_data   = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $chartA_labels[] = $day->format('d M');
            $chartA_data[]   = PassengerRecord::whereHas('trip', fn($q) =>
            $q->where('enterprise_id', $eid))
                ->whereDate('created_at', $day)
                ->whereIn('status', ['Onboard', 'Arrived'])
                ->count();
        }
        // B) Revenue by route (current month)
        $revRows = TransportSubscription::where('enterprise_id', $eid)
            ->where('status', 'active')
            ->whereBetween('created_at', [$currStart, $currEnd])
            ->groupBy('transport_route_id')
            ->select('transport_route_id', DB::raw('sum(amount) as revenue'))
            ->get();
        $chartB_labels = $revRows->map(fn($r) => TransportRoute::find($r->transport_route_id)->name)->toArray();
        $chartB_data   = $revRows->pluck('revenue')->toArray();

        return $content
            ->header('Transport Dashboard')
            ->description('Current vs Last Month — key KPIs at a glance')
            ->body(view('admin.transport.stats', compact(
                'currByType',
                'lastByType',
                'currNew',
                'currRenew',
                'lastNew',
                'lastRenew',
                'currRevenue',
                'lastRevenue',
                'currTopName',
                'lastTopName',
                'currOutCount',
                'lastOutCount',
                'currOutPercent',
                'lastOutPercent',
                'currTotalTrips',
                'lastTotalTrips',
                'currAvgLoad',
                'lastAvgLoad',
                'currNoShowRate',
                'lastNoShowRate',
                'currDirPerc',
                'lastDirPerc',
                'chartA_labels',
                'chartA_data',
                'chartB_labels',
                'chartB_data'
            )));
    }



    public function stats(Content $content)
    {

        $u = Admin::user();

        $content->header($u->ent->name . ' - Dashboard');

        // Check for onboarding progress (only for enterprise owners)
        $onboardingData = OnboardingProgressService::getDashboardSummary($u);

        $ent = $u->ent;
        if ($ent) {
            if ($ent->type == 'University') {
                $number_of_active_students_but_not_enrolled = User::where([
                    'user_type' => 'student',
                    'status' => 1,
                    'enterprise_id' => $u->enterprise_id
                ])->where('is_enrolled', '!=', 'YES')
                    ->count();
                $number_of_pending_students_but_not_enrolled = User::where([
                    'user_type' => 'student',
                    'status' => 2,
                    'enterprise_id' => $u->enterprise_id
                ])->where('is_enrolled', '!=', 'YES')
                    ->count();
                $message = '';
                if ($number_of_pending_students_but_not_enrolled > 0) {
                    $message = 'There are <strong><a href="' . admin_url('pending-students?is_enrolled%5B%5D=No') . '" style="color:#fff; text-decoration:underline;">' . number_format($number_of_pending_students_but_not_enrolled) . '</a></strong> pending students not enrolled this semester.';
                }

                if ($number_of_active_students_but_not_enrolled > 0) {
                    $message .= ' There are <strong><a href="' . admin_url('students?is_enrolled%5B%5D=No') . '" style="color:#fff; text-decoration:underline;">' . number_format($number_of_active_students_but_not_enrolled) . '</a></strong> active students not enrolled this semester.';
                }

                if (strlen($message) > 3) {
                    $content->row(function (Row $row) use ($message) {
                        $row->column(12, function (Column $column) use ($message) {
                            $column->append(
                                "<div style='
                                    background: linear-gradient(90deg, #ff6a00 0%, #ee0979 100%);
                                    color: #fff;
                                    padding: 18px 28px;
                                    border-radius: 10px;
                                    font-weight: 500;
                                    font-size: 1.08em;
                                    box-shadow: 0 4px 18px rgba(238,9,121,0.10);
                                    margin-bottom: 22px;
                                    display: flex;
                                    align-items: center;
                                    gap: 18px;
                                    border-left: 6px solid #fff;
                                '>
                                    <span style='
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        background: rgba(255,255,255,0.13);
                                        border-radius: 50%;
                                        width: 38px;
                                        height: 38px;
                                        font-size: 1.5em;
                                        box-shadow: 0 2px 6px rgba(255,106,0,0.08);
                                    '>
                                        <i class='fa fa-exclamation-circle'></i>
                                    </span>
                                    <span>{$message}</span>
                                </div>"
                            );
                        });
                    });
                }
            }
        }


        // Only for universities
        if ($ent->type === 'University') {
            // Get active term
            $term = $ent->active_term();
            if (! $term) {
                throw new \Exception("No active term found for enterprise: {$ent->name}");
            }

            // 1. Students Summary
            $pending    = User::where(['enterprise_id' => $ent->id, 'user_type' => 'student', 'status' => 2])->count();
            $active     = User::where(['enterprise_id' => $ent->id, 'user_type' => 'student', 'status' => 1])->count();
            $enrolled   = User::where(['enterprise_id' => $ent->id, 'user_type' => 'student', 'is_enrolled' => 'Yes'])->count();
            $toEnroll   = User::where(['enterprise_id' => $ent->id, 'user_type' => 'student', 'status' => 1])
                ->where('is_enrolled', '!=', 'Yes')->count();

            // 2. Programme Enrollments
            $progEnroll = [];
            foreach (UniversityProgramme::where('enterprise_id', $ent->id)->get() as $p) {
                $cnt = StudentHasSemeter::where([
                    'term_id' => $term->id
                ])->whereHas('student.current_class', function ($q) use ($p) {
                    $q->where('university_programme_id', $p->id);
                })->count();
                $progEnroll[$p->code] = $cnt;
            }

            // 3. Billing
            $lastBal     = Transaction::where([
                'enterprise_id' => $ent->id,
                'term_id' => $term->id,
                'is_last_term_balance' => 'Yes'
            ])->sum('amount');
            $tuitionBill = Transaction::where([
                'enterprise_id' => $ent->id,
                'term_id' => $term->id,
                'type' => 'FEES_BILL',
                'is_tuition' => 'Yes'
            ])->sum('amount');
            $serviceBill = Transaction::where([
                'enterprise_id' => $ent->id,
                'term_id' => $term->id,
                'is_service' => 'Yes'
            ])->sum('amount');
            $grandBill   = $lastBal + $tuitionBill + $serviceBill;

            // 4. Payments
            $schoolPay = Transaction::where([
                'enterprise_id' => $ent->id,
                'term_id' => $term->id,
                'source' => 'SCHOOLPAY'
            ])->sum('amount');
            $pegPay    = Transaction::where([
                'enterprise_id' => $ent->id,
                'term_id' => $term->id,
                'source' => 'PEGPAY'
            ])->sum('amount');
            $grandPay = $schoolPay + $pegPay;
            $balance  = $grandBill - $grandPay; // bills negative, payments positive

            // Now render our 4‐column summary row:
            $content->row(function (Row $row)
            use (
                $pending,
                $active,
                $enrolled,
                $toEnroll,
                $progEnroll,
                $lastBal,
                $tuitionBill,
                $serviceBill,
                $grandBill,
                $schoolPay,
                $pegPay,
                $grandPay,
                $balance
            ) {
                // Students
                $row->column(3, function (Column $col)
                use ($pending, $active, $enrolled, $toEnroll) {
                    $html = "
                  <ul style='list-style:none;padding:0;margin:0;'>
                    <li>🕒 Pending: {$pending}</li>
                    <li>✅ Active: {$active}</li>
                    <li>🎓 Enrolled: {$enrolled}</li>
                    <li>📝 To Enroll: {$toEnroll}</li>
                  </ul>";
                    $col->append(new \Encore\Admin\Widgets\Box('Students Summary', $html));
                });

                // Programme enrollments
                $row->column(3, function (Column $col) use ($progEnroll) {
                    $items = '';
                    $max = 4;
                    $_count = 0;
                    foreach ($progEnroll as $code => $cnt) {
                        $_count++;
                        $items .= "<li>{$code}: {$cnt}</li>";
                        if ($_count >= $max) {
                            break;
                        }
                    }
                    $html = "<ul style='list-style:none;padding:0;margin:0;'>{$items}</ul>";
                    $col->append(new \Encore\Admin\Widgets\Box('Programme Enrollments', $html));
                });

                // Billing
                $html = "
              <ul style='list-style:none;padding:0;margin:0;'>
                <li>🔸 Last Bal: " . number_format($lastBal) . "</li>
                <li>🔸 Tuition: " . number_format($tuitionBill) . "</li>
                <li>🔸 Services: " . number_format($serviceBill) . "</li>
                <li>🔸 Grand Bill: " . number_format($grandBill) . "</li>
              </ul>";
                $row->column(3, fn($col) => $col->append(new \Encore\Admin\Widgets\Box('Billing Overview (UGX)', $html)));

                // Payments
                $html = "
              <ul style='list-style:none;padding:0;margin:0;'>
                <li>🔸 SchoolPay: " . number_format($schoolPay) . "</li>
                <li>🔸 PegPay: " . number_format($pegPay) . "</li>
                <li>🔸 Paid Total: " . number_format($grandPay) . "</li>
                <li>🔸 Balance: " . number_format($balance) . "</li>
              </ul>";
                $row->column(3, fn($col) => $col->append(new \Encore\Admin\Widgets\Box('Payment Summary (UGX)', $html)));
            });
        }

        // Add onboarding progress widget for enterprise owners
        if ($onboardingData) {
            $content->row(function (Row $row) use ($onboardingData) {
                $row->column(12, function (Column $column) use ($onboardingData) {
                    $column->append(view('widgets.onboarding-progress', [
                        'onboardingData' => $onboardingData
                    ]));
                });
            });
        }

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
            if ($ent->type != 'University') {
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
                });
            }



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



        // STORE‐KEEPER DASH
        if ($u->isRole('store')) {

            // 1. Totals
            $totalCategories = StockItemCategory::where('enterprise_id', $ent->id)
                ->count();
            $totalBatches    = StockBatch::where('enterprise_id', $ent->id)
                ->where('is_archived', 'No')
                ->count();
            $totalValue      = StockBatch::where('enterprise_id', $ent->id)
                ->where('is_archived', 'No')
                ->sum('worth');
            $totalQty        = StockBatch::where('enterprise_id', $ent->id)
                ->where('is_archived', 'No')
                ->sum('current_quantity');

            // 2. Out-of-stock & Low-stock categories
            $cats = StockItemCategory::where('enterprise_id', $ent->id)->get();
            $outOfStockCount = $cats->filter(function ($c) {
                return StockBatch::where('stock_item_category_id', $c->id)
                    ->where('is_archived', 'No')
                    ->sum('current_quantity') <= 0;
            })->count();
            $lowStockCount   = $cats->filter(function ($c) {
                $qty = StockBatch::where('stock_item_category_id', $c->id)
                    ->where('is_archived', 'No')
                    ->sum('current_quantity');
                return $qty > 0 && $qty < $c->reorder_level;
            })->count();

            // 3. Push to a custom widget
            $content->row(view('widgets.stock-dashboard', compact(
                'totalCategories',
                'totalBatches',
                'totalQty',
                'totalValue',
                'outOfStockCount',
                'lowStockCount'
            )));
        }


        return $content;
    }



    public function stockStats(Content $content)
    {
        $eid = Admin::user()->enterprise_id;

        // Only non-archived batches & records
        $batchesQ = StockBatch::where('enterprise_id', $eid)
            ->where('is_archived', 'No');
        $recordsQ = StockRecord::where('enterprise_id', $eid)
            ->where('is_archived', 'No');

        // 1. Counts & totals
        $totalCategories    = StockItemCategory::where('enterprise_id', $eid)->count();
        $totalBatches       = $batchesQ->count();
        $totalRecords       = $recordsQ->count();
        $inRecordsCount     = (clone $recordsQ)->where('type', 'IN')->count();
        $outRecordsCount    = (clone $recordsQ)->where('type', 'OUT')->count();

        $currentValue       = $batchesQ->sum('worth');
        $currentQuantity    = $batchesQ->sum('current_quantity');

        // 2. Health
        $outOfStockCats     = StockItemCategory::where('enterprise_id', $eid)
            ->whereRaw('(SELECT COALESCE(SUM(current_quantity),0)
                         FROM stock_batches WHERE stock_batches.stock_item_category_id = stock_item_categories.id
                           AND stock_batches.is_archived = \'No\') = 0')
            ->count();

        $lowStockCats       = StockItemCategory::where('enterprise_id', $eid)
            ->whereRaw('(SELECT COALESCE(SUM(current_quantity),0)
                         FROM stock_batches WHERE stock_batches.stock_item_category_id = stock_item_categories.id
                           AND stock_batches.is_archived = \'No\') < reorder_level')
            ->count();

        // 3. Averages
        $avgValuePerCategory = $totalCategories
            ? round($currentValue / $totalCategories)
            : 0;

        // 4. Top 3 categories by value
        $topCategories = StockItemCategory::select('name')
            ->selectRaw('(SELECT COALESCE(SUM(worth),0)
                         FROM stock_batches
                        WHERE stock_batches.stock_item_category_id = stock_item_categories.id
                          AND stock_batches.is_archived = \'No\') as total_worth')
            ->where('enterprise_id', $eid)
            ->orderByDesc('total_worth')
            ->limit(3)
            ->get();

        // 5. Recent 5 stock records
        $recentRecords = $recordsQ
            ->orderByDesc('record_date')
            ->limit(5)
            ->get(['record_date', 'type', 'stock_batch_id', 'description'])
            ->load(['batch.cat']);

        // 6. Running-low categories detail
        $lowCats = StockItemCategory::select('id', 'name', 'reorder_level')
            ->where('enterprise_id', $eid)
            ->get()
            ->map(function ($cat) {
                $qty = DB::table('stock_batches')
                    ->where('stock_item_category_id', $cat->id)
                    ->where('is_archived', 'No')
                    ->sum('current_quantity');
                return [
                    'name'          => $cat->name,
                    'total_qty'     => $qty,
                    'reorder_level' => $cat->reorder_level,
                ];
            })
            ->filter(fn($c) => $c['total_qty'] < $c['reorder_level'])
            ->values();

        // ==================== SERVICE ITEMS TRACKING STATS ====================
        
        // 7. Service Item Tracking Stats (New Approach)
        $serviceItemsQ = \App\Models\ServiceItemToBeOffered::where('enterprise_id', $eid);
        
        $totalServiceItems = (clone $serviceItemsQ)->count();
        $itemsOffered = (clone $serviceItemsQ)->where('is_service_offered', 'Yes')->count();
        $itemsPending = (clone $serviceItemsQ)->where('is_service_offered', 'No')->count();
        
        // Total quantity allocated (offered items)
        $totalAllocatedQuantity = (clone $serviceItemsQ)
            ->where('is_service_offered', 'Yes')
            ->sum('quantity');
        
        // Service Subscription Stats (Parent subscriptions)
        $inventorySubscriptionsQ = \App\Models\ServiceSubscription::where('enterprise_id', $eid)
            ->where('to_be_managed_by_inventory', 'Yes');
        
        $totalInventorySubscriptions = (clone $inventorySubscriptionsQ)->count();
        $subscriptionsCompleted = (clone $inventorySubscriptionsQ)->where('is_completed', 'Yes')->count();
        $subscriptionsIncomplete = (clone $inventorySubscriptionsQ)->where('is_completed', 'No')->count();
        
        // 8. Items pending by category (shows what stock items are needed most)
        $pendingItemsByCategory = DB::table('service_items_to_be_offered')
            ->join('stock_item_categories', 'service_items_to_be_offered.stock_item_category_id', '=', 'stock_item_categories.id')
            ->where('service_items_to_be_offered.enterprise_id', $eid)
            ->where('service_items_to_be_offered.is_service_offered', 'No')
            ->select(
                'stock_item_categories.name as item_name',
                'stock_item_categories.id as category_id',
                DB::raw('COUNT(service_items_to_be_offered.id) as pending_items_count'),
                DB::raw('SUM(service_items_to_be_offered.quantity) as total_quantity_needed')
            )
            ->groupBy('stock_item_categories.id', 'stock_item_categories.name')
            ->orderByDesc('pending_items_count')
            ->limit(10)
            ->get()
            ->map(function ($item) use ($eid) {
                // Get available stock for this category
                $availableStock = DB::table('stock_batches')
                    ->where('stock_item_category_id', $item->category_id)
                    ->where('enterprise_id', $eid)
                    ->where('is_archived', 'No')
                    ->sum('current_quantity');
                
                return [
                    'item_name' => $item->item_name,
                    'pending_count' => $item->pending_items_count,
                    'quantity_needed' => $item->total_quantity_needed ?? 0,
                    'available_stock' => $availableStock,
                    'status' => $availableStock >= ($item->total_quantity_needed ?? 0) ? 'sufficient' : 'insufficient'
                ];
            });
        
        // 9. Latest 10 incomplete service subscriptions with tracking items
        $latestInventorySubscriptions = \App\Models\ServiceSubscription::where('enterprise_id', $eid)
            ->where('to_be_managed_by_inventory', 'Yes')
            ->where('is_completed', 'No')
            ->with(['sub', 'service', 'due_term', 'itemsToBeOffered.stockItemCategory'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($sub) {
                // Ensure itemsToBeOffered is a collection
                $items = collect($sub->itemsToBeOffered ?? []);
                $totalItems = $items->count();
                $offeredItems = $items->where('is_service_offered', 'Yes')->count();
                
                return [
                    'id' => $sub->id,
                    'student_name' => optional($sub->sub)->name ?? 'N/A',
                    'service_name' => optional($sub->service)->name ?? 'N/A',
                    'term_name' => optional($sub->due_term)->name_text ?? 'N/A',
                    'total_items' => $totalItems,
                    'offered_items' => $offeredItems,
                    'pending_items' => $totalItems - $offeredItems,
                    'progress_percent' => $totalItems > 0 ? round(($offeredItems / $totalItems) * 100) : 0,
                    'created_at' => $sub->created_at
                ];
            });
        
        // 10. Recently offered items (last 10)
        $recentlyOfferedItems = \App\Models\ServiceItemToBeOffered::where('enterprise_id', $eid)
            ->where('is_service_offered', 'Yes')
            ->whereNotNull('offered_at')
            ->with(['stockItemCategory', 'serviceSubscription.subscriber', 'offeredBy'])
            ->orderByDesc('offered_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => optional($item->stockItemCategory)->name ?? 'N/A',
                    'student_name' => optional(optional($item->serviceSubscription)->subscriber)->name ?? 'N/A',
                    'quantity' => $item->quantity ?? 0,
                    'offered_by' => optional($item->offeredBy)->name ?? 'System',
                    'offered_at' => $item->offered_at ?? now()
                ];
            });

        return $content
            ->header('Stock Dashboard')
            ->description('Key inventory KPIs at a glance')
            ->view('admin.stock.stats', compact(
                'totalCategories',
                'totalBatches',
                'totalRecords',
                'inRecordsCount',
                'outRecordsCount',
                'currentValue',
                'currentQuantity',
                'outOfStockCats',
                'lowStockCats',
                'avgValuePerCategory',
                'topCategories',
                'recentRecords',
                'lowCats',
                // Service Items Tracking Stats (New)
                'totalServiceItems',
                'itemsOffered',
                'itemsPending',
                'totalAllocatedQuantity',
                
                // Service Subscriptions (Parent)
                'totalInventorySubscriptions',
                'subscriptionsCompleted',
                'subscriptionsIncomplete',
                
                // Detailed breakdowns
                'pendingItemsByCategory',
                'latestInventorySubscriptions',
                'recentlyOfferedItems'
            ));
    }
}
