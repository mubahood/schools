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
        $u   = Admin::user();
        $eid = $u->enterprise_id;
        $ent = $eid ? Enterprise::find($eid) : null;

        // Detect primary role (ordered by specificity)
        $role = 'generic';
        $roleMap = [
            'super-admin' => 'super-admin',
            'hm'          => 'hm',
            'deputy-hm'   => 'hm',
            'dos'         => 'dos',
            'admin'       => 'admin',
            'bursar'      => 'bursar',
            'finance'     => 'bursar',
            'teacher'     => 'teacher',
            'parent'      => 'parent',
            'student'     => 'student',
            'librarian'   => 'librarian',
            'store'       => 'store',
            'nurse'       => 'nurse',
            'warden'      => 'warden',
            'driver'      => 'transport',
            'security'    => 'transport',
            'gate'        => 'transport',
            'receptionist'=> 'receptionist',
            'purchaser'   => 'store',
            'supervisor'  => 'admin',
            'secretary'   => 'admin',
        ];
        foreach ($roleMap as $slug => $mapped) {
            if ($u->isRole($slug)) { $role = $mapped; break; }
        }

        $dash = [];
        try {
            switch ($role) {
                case 'super-admin':  $dash = $this->dashSuperAdmin(); break;
                case 'hm':           $dash = $this->dashHm($u, $eid); break;
                case 'dos':          $dash = $this->dashDos($u, $eid); break;
                case 'admin':        $dash = $this->dashAdmin($u, $eid); break;
                case 'bursar':       $dash = $this->dashBursar($u, $eid); break;
                case 'teacher':      $dash = $this->dashTeacher($u, $eid); break;
                case 'parent':       $dash = $this->dashParent($u, $eid); break;
                case 'student':      $dash = $this->dashStudent($u, $eid); break;
                case 'librarian':    $dash = $this->dashLibrarian($u, $eid); break;
                case 'store':        $dash = $this->dashStore($u, $eid); break;
                case 'nurse':        $dash = $this->dashNurse($u, $eid); break;
                case 'warden':       $dash = $this->dashWarden($u, $eid); break;
                case 'transport':    $dash = $this->dashTransport($u, $eid); break;
                case 'receptionist': $dash = $this->dashReceptionist($u, $eid); break;
                default:             $dash = $this->dashGeneric($u, $eid); break;
            }
        } catch (\Throwable $e) {
            $dash = ['error' => $e->getMessage()];
        }

        return $content->view('admin.index', [
            'u'    => $u,
            'ent'  => $ent,
            'role' => $role,
            'dash' => $dash,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function activeYear($eid)
    {
        return \App\Models\AcademicYear::where(['enterprise_id' => $eid, 'is_active' => 1])->first();
    }

    private function activeTerm($eid)
    {
        return \App\Models\Term::where(['enterprise_id' => $eid, 'is_active' => 1])->first();
    }

    private function activeTermlyCard($eid, $termId)
    {
        return \App\Models\TermlyReportCard::where(['enterprise_id' => $eid, 'term_id' => $termId])->first();
    }

    // ── Super Admin ───────────────────────────────────────────────────────────
    private function dashSuperAdmin(): array
    {
        return [
            'enterprises'  => Enterprise::count(),
            'total_users'  => DB::table('admin_users')->count(),
            'total_stud'   => DB::table('admin_users')->where('user_type', 'student')->count(),
            'total_emp'    => DB::table('admin_users')->where('user_type', 'employee')->count(),
            'recent_ents'  => Enterprise::orderBy('id','desc')->take(8)->get(['id','name','created_at']),
        ];
    }

    // ── Headmaster ────────────────────────────────────────────────────────────
    private function dashHm($u, $eid): array
    {
        $term = $this->activeTerm($eid);
        $year = $this->activeYear($eid);
        $tid  = $term?->id;

        $totalStudents  = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'student','status'=>1])->count();
        $totalEmployees = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'employee'])->count();

        // Grade distribution (student_report_cards for active term)
        $grades = DB::table('student_report_cards')
            ->where('enterprise_id', $eid)
            ->when($tid, fn($q) => $q->where('term_id', $tid))
            ->selectRaw("grade, count(*) as cnt")
            ->groupBy('grade')->get()
            ->pluck('cnt', 'grade')->toArray();

        // Class enrollment
        $classes = AcademicClass::where(['enterprise_id'=>$eid,'academic_year_id'=>$year?->id])
            ->withCount(['students as student_count'])
            ->orderBy('name')->get(['id','name']);

        // Mark submission overview for current term — single query
        $mrStats = DB::table('mark_records')
            ->where('enterprise_id', $eid)
            ->when($tid, fn($q) => $q->where('term_id', $tid))
            ->selectRaw("COUNT(*) as total,
                SUM(CASE WHEN bot_is_submitted='Yes' THEN 1 ELSE 0 END) as bot_submitted,
                SUM(CASE WHEN mot_is_submitted='Yes' THEN 1 ELSE 0 END) as mot_submitted,
                SUM(CASE WHEN eot_is_submitted='Yes' THEN 1 ELSE 0 END) as eot_submitted")
            ->first();
        $totalMR      = $mrStats->total ?? 0;
        $submittedBOT = $mrStats->bot_submitted ?? 0;
        $submittedMOT = $mrStats->mot_submitted ?? 0;
        $submittedEOT = $mrStats->eot_submitted ?? 0;

        // Assessment sheets
        $totalSheets = \App\Models\AssessmentSheet::where('enterprise_id',$eid)->count();

        // Progressive assessments
        $paReports = DB::table('student_progressive_reports')->where('enterprise_id',$eid)->count();

        // Top 5 classes by grade-1 students
        $top5 = DB::table('student_report_cards')
            ->where(['enterprise_id'=>$eid,'grade'=>'1'])
            ->when($tid, fn($q)=>$q->where('term_id',$tid))
            ->selectRaw('academic_class_id, count(*) as g1_count')
            ->groupBy('academic_class_id')
            ->orderByDesc('g1_count')->take(5)->get();

        return compact(
            'term','year','totalStudents','totalEmployees','grades','classes',
            'totalMR','submittedBOT','submittedMOT','submittedEOT',
            'totalSheets','paReports','top5'
        );
    }

    // ── Director of Studies ───────────────────────────────────────────────────
    private function dashDos($u, $eid): array
    {
        $term = $this->activeTerm($eid);
        $year = $this->activeYear($eid);
        $tid  = $term?->id;
        $trc  = $tid ? $this->activeTermlyCard($eid, $tid) : null;

        // Mark submission rates — single query
        $mrStats2  = DB::table('mark_records')
            ->where('enterprise_id', $eid)
            ->when($tid, fn($q) => $q->where('term_id', $tid))
            ->selectRaw("COUNT(*) as total,
                SUM(CASE WHEN bot_is_submitted='Yes' THEN 1 ELSE 0 END) as bot_submitted,
                SUM(CASE WHEN mot_is_submitted='Yes' THEN 1 ELSE 0 END) as mot_submitted,
                SUM(CASE WHEN eot_is_submitted='Yes' THEN 1 ELSE 0 END) as eot_submitted")
            ->first();
        $totalMR   = $mrStats2->total ?? 0;
        $botSubmit  = $mrStats2->bot_submitted ?? 0;
        $motSubmit  = $mrStats2->mot_submitted ?? 0;
        $eotSubmit  = $mrStats2->eot_submitted ?? 0;

        // Per-class mark completion — single grouped query instead of 2× per class
        $yearId = $year?->id;
        $classes = AcademicClass::where(['enterprise_id' => $eid])
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->get(['id', 'name']);

        $classIds = $classes->pluck('id')->toArray();
        $classMarkStats = DB::table('mark_records')
            ->where('enterprise_id', $eid)
            ->when($tid, fn($q) => $q->where('term_id', $tid))
            ->whereIn('academic_class_id', $classIds)
            ->selectRaw("academic_class_id, COUNT(*) as total,
                SUM(CASE WHEN bot_is_submitted='Yes' THEN 1 ELSE 0 END) as sub")
            ->groupBy('academic_class_id')
            ->get()->keyBy('academic_class_id');

        $classStats = [];
        foreach ($classes->take(12) as $cls) {
            $stat  = $classMarkStats->get($cls->id);
            $total = $stat->total ?? 0;
            $sub   = $stat->sub ?? 0;
            $classStats[] = [
                'name'  => $cls->name,
                'total' => $total,
                'sub'   => $sub,
                'pct'   => $total > 0 ? round($sub / $total * 100) : 0,
            ];
        }

        // Students with grade U or X this term
        $atRisk = DB::table('student_report_cards')
            ->where('enterprise_id',$eid)
            ->when($tid, fn($q)=>$q->where('term_id',$tid))
            ->whereIn('grade',['U','X'])
            ->count();

        $gradeOnePct = 0;
        $totalCards  = DB::table('student_report_cards')->where('enterprise_id',$eid)->when($tid, fn($q)=>$q->where('term_id',$tid))->count();
        $grade1      = DB::table('student_report_cards')->where(['enterprise_id'=>$eid,'grade'=>'1'])->when($tid, fn($q)=>$q->where('term_id',$tid))->count();
        if ($totalCards > 0) $gradeOnePct = round($grade1 / $totalCards * 100);

        // Subjects with lowest submission rates
        $laggingSubjects = DB::table('mark_records')
            ->join('subjects','mark_records.subject_id','=','subjects.id')
            ->where('mark_records.enterprise_id',$eid)
            ->when($tid, fn($q)=>$q->where('mark_records.term_id',$tid))
            ->selectRaw('subjects.subject_name, count(*) as total, sum(case when mark_records.bot_is_submitted="Yes" then 1 else 0 end) as submitted')
            ->groupBy('subjects.subject_name')
            ->havingRaw('count(*) > 0')
            ->orderByRaw('(sum(case when mark_records.bot_is_submitted="Yes" then 1 else 0 end)/count(*)) asc')
            ->take(6)->get();

        // Assessment sheets summary
        $sheets = \App\Models\AssessmentSheet::where('enterprise_id',$eid)
            ->when($tid, fn($q)=>$q->where('term_id',$tid))
            ->count();
        $sheetsGenerated = \App\Models\AssessmentSheet::where(['enterprise_id'=>$eid,'generated'=>'Yes'])
            ->when($tid, fn($q)=>$q->where('term_id',$tid))
            ->count();

        // PA sheets
        $paSheets = DB::table('progressive_assessment_sheets')->where('enterprise_id',$eid)->count();

        $totalStudents = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'student','status'=>1])->count();

        return compact(
            'term','year','trc','totalMR','botSubmit','motSubmit','eotSubmit',
            'classStats','atRisk','totalCards','grade1','gradeOnePct',
            'laggingSubjects','sheets','sheetsGenerated','paSheets','totalStudents'
        );
    }

    // ── Admin (Enterprise Admin) ───────────────────────────────────────────────
    private function dashAdmin($u, $eid): array
    {
        $totalStudents  = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'student','status'=>1])->count();
        $totalEmployees = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'employee'])->count();
        $totalParents   = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'parent'])->count();

        // Role distribution for employees
        $roleCount = DB::table('admin_role_users')
            ->join('admin_roles','admin_role_users.role_id','=','admin_roles.id')
            ->join('admin_users','admin_role_users.user_id','=','admin_users.id')
            ->where('admin_users.enterprise_id',$eid)
            ->where('admin_users.user_type','employee')
            ->selectRaw('admin_roles.name as role_name, count(*) as cnt')
            ->groupBy('admin_roles.name')
            ->orderByDesc('cnt')->take(8)->get();

        $year    = $this->activeYear($eid);
        $classes = AcademicClass::where(['enterprise_id'=>$eid])
            ->when($year?->id, fn($q)=>$q->where('academic_year_id',$year->id))
            ->count();

        $newStudents = DB::table('admin_users')
            ->where(['enterprise_id'=>$eid,'user_type'=>'student'])
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $recentEmployees = DB::table('admin_users')
            ->where(['enterprise_id'=>$eid,'user_type'=>'employee'])
            ->orderBy('created_at','desc')->take(6)
            ->get(['name','created_at']);

        return compact('totalStudents','totalEmployees','totalParents','roleCount','classes','newStudents','recentEmployees','year');
    }

    // ── Bursar / Finance ───────────────────────────────────────────────────────
    private function dashBursar($u, $eid): array
    {
        $term = $this->activeTerm($eid);
        $tid  = $term?->id;

        // Fees / accounts
        $totalAccounts  = DB::table('accounts')->where('enterprise_id',$eid)->count();
        $totalDebt      = abs(DB::table('accounts')->where('enterprise_id',$eid)->where('balance','<',0)->sum('balance'));
        $totalCredit    = DB::table('accounts')->where('enterprise_id',$eid)->where('balance','>',0)->sum('balance');
        $debtorCount    = DB::table('accounts')->where('enterprise_id',$eid)->where('balance','<',0)->count();
        $paidCount      = DB::table('accounts')->where('enterprise_id',$eid)->where('balance','>=',0)->count();

        // Transactions this term
        $txnBase       = DB::table('transactions')->where('enterprise_id',$eid);
        $totalTxns     = (clone $txnBase)->count();
        $recentTxns    = (clone $txnBase)->orderBy('id','desc')->take(8)->get(['id','source','amount','type','created_at']);

        // Financial records (budget/expenditure)
        $totalBudget   = DB::table('financial_records')->where(['enterprise_id'=>$eid,'type'=>'BUDGET'])->sum('amount');
        $totalExpend   = abs(DB::table('financial_records')->where(['enterprise_id'=>$eid,'type'=>'EXPENDITURE'])->sum('amount'));

        // Top debtors
        $topDebtors = DB::table('accounts')
            ->join('admin_users','accounts.administrator_id','=','admin_users.id')
            ->where('accounts.enterprise_id',$eid)
            ->where('accounts.balance','<',0)
            ->orderBy('accounts.balance')
            ->take(6)
            ->get(['admin_users.name','accounts.balance']);

        return compact('term','totalAccounts','totalDebt','totalCredit','debtorCount',
            'paidCount','totalTxns','recentTxns','totalBudget','totalExpend','topDebtors');
    }

    // ── Teacher ───────────────────────────────────────────────────────────────
    private function dashTeacher($u, $eid): array
    {
        $term = $this->activeTerm($eid);
        $year = $this->activeYear($eid);
        $tid  = $term?->id;
        $uid  = $u->id;

        // Subjects this teacher is assigned to
        $mySubjects = Subject::where('enterprise_id',$eid)
            ->where(fn($q) => $q->where('subject_teacher',$uid)
                ->orWhere('teacher_1',$uid)->orWhere('teacher_2',$uid)->orWhere('teacher_3',$uid))
            ->get(['id','subject_name','academic_class_id']);

        // Mark submission per subject
        $subjectStats = [];
        $classIds = $mySubjects->pluck('academic_class_id')->unique()->filter();

        foreach ($mySubjects as $subj) {
            $base  = DB::table('mark_records')->where(['enterprise_id'=>$eid,'subject_id'=>$subj->id])->when($tid, fn($q)=>$q->where('term_id',$tid));
            $total = (clone $base)->count();
            $bot   = (clone $base)->where('bot_is_submitted','Yes')->count();
            $mot   = (clone $base)->where('mot_is_submitted','Yes')->count();
            $eot   = (clone $base)->where('eot_is_submitted','Yes')->count();
            $cls   = AcademicClass::find($subj->academic_class_id);
            $subjectStats[] = [
                'name'      => $subj->subject_name,
                'class'     => $cls?->name ?? '—',
                'total_stu' => $total,
                'bot'       => $bot,
                'mot'       => $mot,
                'eot'       => $eot,
                'id'        => $subj->id,
            ];
        }

        // My students' performance overview
        $myStudentCount = $classIds->count() > 0
            ? DB::table('student_has_classes')
                ->join('academic_classes','student_has_classes.academic_class_id','=','academic_classes.id')
                ->whereIn('student_has_classes.academic_class_id', $classIds)
                ->where('academic_classes.enterprise_id',$eid)
                ->when($year?->id, fn($q)=>$q->where('student_has_classes.academic_year_id',$year->id))
                ->distinct('student_has_classes.administrator_id')
                ->count('student_has_classes.administrator_id')
            : 0;

        // Grade distribution for my subjects
        $myGrades = DB::table('student_report_cards')
            ->where('enterprise_id',$eid)
            ->when($tid, fn($q)=>$q->where('term_id',$tid))
            ->whereIn('academic_class_id', $classIds->toArray())
            ->selectRaw('grade, count(*) as cnt')->groupBy('grade')
            ->get()->pluck('cnt','grade')->toArray();

        $totalSubjects = $mySubjects->count();

        return compact('term','year','mySubjects','subjectStats','myStudentCount','myGrades','totalSubjects','classIds');
    }

    // ── Parent ────────────────────────────────────────────────────────────────
    private function dashParent($u, $eid): array
    {
        $term = $this->activeTerm($eid);
        $tid  = $term?->id;

        // Find children
        $children = DB::table('admin_users')->where('parent_id',$u->id)->get(['id','name','status','avatar']);

        $childrenData = [];
        foreach ($children as $child) {
            // Latest report card
            $report = DB::table('student_report_cards')
                ->where('student_id',$child->id)
                ->when($tid, fn($q)=>$q->where('term_id',$tid))
                ->orderBy('id','desc')->first();

            // Current class
            $hasClass = DB::table('student_has_classes')
                ->join('academic_classes','student_has_classes.academic_class_id','=','academic_classes.id')
                ->where('student_has_classes.administrator_id',$child->id)
                ->orderBy('student_has_classes.academic_year_id','desc')
                ->first(['academic_classes.name as class_name']);

            // Fee balance
            $account = DB::table('accounts')->where(['administrator_id'=>$child->id,'enterprise_id'=>$eid])->first();

            $childrenData[] = [
                'id'         => $child->id,
                'name'       => $child->name,
                'status'     => $child->status,
                'class'      => $hasClass?->class_name ?? '—',
                'grade'      => $report?->grade ?? '—',
                'position'   => $report?->position ?? '—',
                'total_stu'  => $report?->total_students ?? '—',
                'total_marks'=> $report?->total_marks ?? '—',
                'balance'    => $account?->balance ?? 0,
            ];
        }

        return compact('term','children','childrenData');
    }

    // ── Student ───────────────────────────────────────────────────────────────
    private function dashStudent($u, $eid): array
    {
        $term = $this->activeTerm($eid);
        $tid  = $term?->id;

        // Latest report
        $latestReport = DB::table('student_report_cards')
            ->where('student_id',$u->id)
            ->when($tid, fn($q)=>$q->where('term_id',$tid))
            ->orderBy('id','desc')->first();

        // Historical reports
        $history = DB::table('student_report_cards')
            ->where('student_id',$u->id)
            ->join('terms','student_report_cards.term_id','=','terms.id')
            ->orderBy('student_report_cards.id','desc')
            ->take(6)
            ->get(['student_report_cards.total_marks','student_report_cards.grade','student_report_cards.position','terms.name as term_name']);

        // Current class
        $hasClass = DB::table('student_has_classes')
            ->join('academic_classes','student_has_classes.academic_class_id','=','academic_classes.id')
            ->where('student_has_classes.administrator_id',$u->id)
            ->orderBy('student_has_classes.academic_year_id','desc')
            ->first(['academic_classes.name as class_name']);

        // Fee account
        $account = DB::table('accounts')->where(['administrator_id'=>$u->id,'enterprise_id'=>$eid])->first();

        // PA report
        $paReport = DB::table('student_progressive_reports')
            ->where('student_id',$u->id)
            ->orderBy('id','desc')->first();

        return compact('term','latestReport','history','hasClass','account','paReport');
    }

    // ── Librarian ─────────────────────────────────────────────────────────────
    private function dashLibrarian($u, $eid): array
    {
        $totalBooks     = DB::table('books')->where('enterprise_id',$eid)->count();
        $totalBorrowed  = DB::table('book_borrows')->where('enterprise_id',$eid)->where('status','Borrowed')->count();
        $overdue        = DB::table('book_borrows')->where('enterprise_id',$eid)->where('status','Overdue')->count();
        $categories     = DB::table('books_categories')->where('enterprise_id',$eid)->count();
        $recentBorrows  = DB::table('book_borrows')
            ->join('admin_users','book_borrows.borrowed_by','=','admin_users.id')
            ->where('book_borrows.enterprise_id',$eid)
            ->orderBy('book_borrows.id','desc')->take(8)
            ->get(['admin_users.name','book_borrows.status','book_borrows.return_date','book_borrows.created_at']);

        return compact('totalBooks','totalBorrowed','overdue','categories','recentBorrows');
    }

    // ── Store Keeper ─────────────────────────────────────────────────────────
    private function dashStore($u, $eid): array
    {
        $totalCategories = DB::table('stock_item_categories')->where('enterprise_id',$eid)->count();
        $totalBatches    = DB::table('stock_batches')->where('enterprise_id',$eid)->count();
        $lowStock        = DB::table('stock_item_categories')
            ->where('enterprise_id',$eid)
            ->whereRaw('quantity <= reorder_level AND reorder_level > 0')
            ->count();
        $outOfStock      = DB::table('stock_item_categories')->where(['enterprise_id'=>$eid,'quantity'=>0])->count();
        $recentBatches   = DB::table('stock_batches')
            ->join('stock_item_categories','stock_batches.stock_item_category_id','=','stock_item_categories.id')
            ->where('stock_batches.enterprise_id',$eid)
            ->orderBy('stock_batches.id','desc')->take(8)
            ->get(['stock_item_categories.name','stock_batches.current_quantity as quantity','stock_batches.worth','stock_batches.created_at']);
        $lowStockItems   = DB::table('stock_item_categories')
            ->where('enterprise_id',$eid)
            ->whereRaw('quantity <= reorder_level AND reorder_level > 0')
            ->orderBy('quantity')->take(6)
            ->get(['name','quantity','reorder_level']);

        return compact('totalCategories','totalBatches','lowStock','outOfStock','recentBatches','lowStockItems');
    }

    // ── Nurse ─────────────────────────────────────────────────────────────────
    private function dashNurse($u, $eid): array
    {
        $totalStudents = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'student','status'=>1])->count();
        $visitors      = DB::table('visitor_records')->where('enterprise_id',$eid)->count();
        $todayVisitors = DB::table('visitor_records')->where('enterprise_id',$eid)->whereDate('created_at',now())->count();
        return compact('totalStudents','visitors','todayVisitors');
    }

    // ── Warden ────────────────────────────────────────────────────────────────
    private function dashWarden($u, $eid): array
    {
        $totalStudents = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'student','status'=>1])->count();
        return compact('totalStudents');
    }

    // ── Transport (Driver/Security/Gate) ──────────────────────────────────────
    private function dashTransport($u, $eid): array
    {
        $totalRoutes  = DB::table('transport_routes')->where('enterprise_id',$eid)->count();
        $totalSubs    = DB::table('transport_subscriptions')->where('enterprise_id',$eid)->count();
        $totalTrips   = DB::table('trips')->where('enterprise_id',$eid)->count();
        $todayTrips   = DB::table('trips')->where('enterprise_id',$eid)->whereDate('created_at',now())->count();
        $recentTrips  = DB::table('trips')->where('enterprise_id',$eid)->orderBy('id','desc')->take(6)->get();
        return compact('totalRoutes','totalSubs','totalTrips','todayTrips','recentTrips');
    }

    // ── Receptionist ─────────────────────────────────────────────────────────
    private function dashReceptionist($u, $eid): array
    {
        $todayVisitors = DB::table('visitor_records')->where('enterprise_id',$eid)->whereDate('created_at',now())->count();
        $totalVisitors = DB::table('visitor_records')->where('enterprise_id',$eid)->count();
        $totalStudents = DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'student','status'=>1])->count();
        $recentVisitors= DB::table('visitor_records')->where('enterprise_id',$eid)->orderBy('id','desc')->take(8)->get();
        return compact('todayVisitors','totalVisitors','totalStudents','recentVisitors');
    }

    // ── Generic fallback ──────────────────────────────────────────────────────
    private function dashGeneric($u = null, $eid = null): array
    {
        return [
            'totalStudents' => $eid ? DB::table('admin_users')->where(['enterprise_id'=>$eid,'user_type'=>'student','status'=>1])->count() : 0,
        ];
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
        $u   = Admin::user();
        $eid = $u->enterprise_id;

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
                            'title' => 'Service Fees',
                            'icon' => 'file-text-o',
                            'sub_title' => 'Subscription fees this term',
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
                            'title' => 'Bursaries',
                            'icon' => 'gift',
                            'sub_title' => 'Offered this term',
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
                            'title' => 'Expected Revenue',
                            'icon' => 'line-chart',
                            'sub_title' => 'Tuition + service fees',
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
                                'title' => 'Income Received',
                                'icon' => 'check-circle',
                                'sub_title' => 'Payments this term',
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
                            'title' => 'Income Received',
                            'icon' => 'check-circle',
                            'sub_title' => 'Payments this term',
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
                            'title' => 'Fees Balance',
                            'icon' => 'balance-scale',
                            'sub_title' => 'All active students',
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
                            'title' => 'Term Budget',
                            'icon' => 'calculator',
                            'sub_title' => 'Planned spending',
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
                            'title' => 'Expenditure',
                            'icon' => 'credit-card',
                            'sub_title' => 'Spent this term',
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
                            'icon' => 'cubes',
                            'sub_title' => 'Current store value',
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


            // Unclassed students alert
            $content->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append(Dashboard::unclassedStudents());
                });
            });

            // Class enrollment stats with chart
            $content->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append(Dashboard::classEnrollmentStats());
                });
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

            // Commitment reminders widget — shown to admin/dos/hm/bursar
            $content->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append(view('dashboard.commitment-reminders'));
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


        // ── Role-based rich dashboard for roles without existing content ──────
        $roleDashRoles = ['dos','teacher','parent','student','nurse','warden',
                          'driver','security','gate','receptionist','marketing-agent',
                          'administrative-assistant','secretary','supervisor','purchaser'];
        $needsRichDash = false;
        foreach ($roleDashRoles as $_slug) {
            if ($u->isRole($_slug)) { $needsRichDash = true; break; }
        }
        // Also inject for hm/admin/bursar roles as supplementary top panel
        if (!$needsRichDash && ($u->isRole('hm') || $u->isRole('deputy-hm') || $u->isRole('admin') || $u->isRole('bursar') || $u->isRole('finance'))) {
            $needsRichDash = true;
        }

        if ($needsRichDash) {
            $roleMap2 = [
                'super-admin'=>'super-admin','hm'=>'hm','deputy-hm'=>'hm','dos'=>'dos',
                'admin'=>'admin','bursar'=>'bursar','finance'=>'bursar',
                'teacher'=>'teacher','parent'=>'parent','student'=>'student',
                'librarian'=>'librarian','store'=>'store','nurse'=>'nurse','warden'=>'warden',
                'driver'=>'transport','security'=>'transport','gate'=>'transport',
                'receptionist'=>'receptionist','purchaser'=>'store','supervisor'=>'admin',
                'secretary'=>'admin','marketing-agent'=>'admin',
                'administrative-assistant'=>'admin',
            ];
            $detectedRole = 'generic';
            foreach ($roleMap2 as $_s => $_r) {
                if ($u->isRole($_s)) { $detectedRole = $_r; break; }
            }
            try {
                $richDash = match($detectedRole) {
                    'super-admin' => $this->dashSuperAdmin(),
                    'hm'         => $this->dashHm($u, $eid),
                    'dos'        => $this->dashDos($u, $eid),
                    'admin'      => $this->dashAdmin($u, $eid),
                    'bursar'     => $this->dashBursar($u, $eid),
                    'teacher'    => $this->dashTeacher($u, $eid),
                    'parent'     => $this->dashParent($u, $eid),
                    'student'    => $this->dashStudent($u, $eid),
                    'librarian'  => $this->dashLibrarian($u, $eid),
                    'store'      => $this->dashStore($u, $eid),
                    'nurse'      => $this->dashNurse($u, $eid),
                    'warden'     => $this->dashWarden($u, $eid),
                    'transport'  => $this->dashTransport($u, $eid),
                    'receptionist'=> $this->dashReceptionist($u, $eid),
                    default      => $this->dashGeneric($u, $eid),
                };
            } catch (\Throwable $_e) {
                $richDash = ['error' => $_e->getMessage()];
            }
            $_ent2 = $ent;
            $_role2 = $detectedRole;
            $_u2    = $u;
            $_dash2 = $richDash;
            $content->row(function (Row $row) use ($_u2, $_ent2, $_role2, $_dash2) {
                $row->column(12, function (Column $column) use ($_u2, $_ent2, $_role2, $_dash2) {
                    $column->append(view('admin.index', [
                        'u'    => $_u2,
                        'ent'  => $_ent2,
                        'role' => $_role2,
                        'dash' => $_dash2,
                    ]));
                });
            });
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
