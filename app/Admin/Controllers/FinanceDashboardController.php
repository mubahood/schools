<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\AccountParent;
use App\Models\CreditorRecord;
use App\Models\FinancialRecord;
use App\Models\Term;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function index(Content $content)
    {
        $u   = Admin::user();
        $eid = $u->enterprise_id;

        // Active term
        $activeTerm = Term::with('academic_year')
            ->where('enterprise_id', $eid)
            ->where('is_active', 1)
            ->first();

        // All terms for this enterprise (for selectors/charts)
        $allTerms = Term::with('academic_year')
            ->where('enterprise_id', $eid)
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        $termId = $activeTerm ? $activeTerm->id : 0;

        // ── KPI: this term ───────────────────────────────────────────────────
        $thisTermExp = FinancialRecord::where('enterprise_id', $eid)
            ->where('type', 'EXPENDITURE')
            ->where('term_id', $termId)
            ->sum('amount');
        $thisTermBudget = FinancialRecord::where('enterprise_id', $eid)
            ->where('type', 'BUDGET')
            ->where('term_id', $termId)
            ->sum('amount');
        $thisTermExpCount = FinancialRecord::where('enterprise_id', $eid)
            ->where('type', 'EXPENDITURE')
            ->where('term_id', $termId)
            ->count();

        // Last term KPI for comparison
        $prevTerm = Term::where('enterprise_id', $eid)
            ->where('id', '<', $termId)
            ->orderBy('id', 'desc')
            ->first();
        $prevTermExp = $prevTerm
            ? abs(FinancialRecord::where('enterprise_id', $eid)
                ->where('type', 'EXPENDITURE')
                ->where('term_id', $prevTerm->id)
                ->sum('amount'))
            : 0;

        // ── Creditor summary ─────────────────────────────────────────────────
        $creditorOutstanding = CreditorRecord::where('enterprise_id', $eid)
            ->whereIn('status', ['Pending', 'Partial'])
            ->sum('balance');
        $creditorCount = CreditorRecord::where('enterprise_id', $eid)
            ->whereIn('status', ['Pending', 'Partial'])
            ->count();
        $creditorPaid = CreditorRecord::where('enterprise_id', $eid)
            ->where('status', 'Paid')
            ->count();

        // ── Expenditure by vote/category (this term) ─────────────────────────
        $byVote = DB::table('financial_records as fr')
            ->join('account_parents as ap', 'ap.id', '=', 'fr.parent_account_id')
            ->where('fr.enterprise_id', $eid)
            ->where('fr.type', 'EXPENDITURE')
            ->where('fr.term_id', $termId)
            ->groupBy('ap.id', 'ap.name')
            ->select('ap.name', DB::raw('SUM(ABS(fr.amount)) as total'))
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // ── Budget vs Actual by vote ──────────────────────────────────────────
        $budgetByVote = DB::table('financial_records as fr')
            ->join('account_parents as ap', 'ap.id', '=', 'fr.parent_account_id')
            ->where('fr.enterprise_id', $eid)
            ->where('fr.type', 'BUDGET')
            ->where('fr.term_id', $termId)
            ->groupBy('ap.id', 'ap.name')
            ->select('ap.name', DB::raw('SUM(ABS(fr.amount)) as total'))
            ->get()
            ->keyBy('name');

        // Merge into comparison dataset
        $voteLabels = $byVote->pluck('name')->toArray();
        $voteActual = $byVote->pluck('total')->toArray();
        $voteBudget = array_map(fn($n) => $budgetByVote->get($n)?->total ?? 0, $voteLabels);

        // ── Monthly expenditure trend (last 6 months) ─────────────────────────
        $monthlyRaw = DB::table('financial_records')
            ->where('enterprise_id', $eid)
            ->where('type', 'EXPENDITURE')
            ->where('payment_date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->select(
                DB::raw("DATE_FORMAT(payment_date,'%b %Y') as month"),
                DB::raw("DATE_FORMAT(payment_date,'%Y%m') as sort_key"),
                DB::raw('SUM(ABS(amount)) as total')
            )
            ->orderBy('sort_key')
            ->get();

        $monthLabels  = $monthlyRaw->pluck('month')->toArray();
        $monthAmounts = $monthlyRaw->pluck('total')->toArray();

        // ── Payment method breakdown ──────────────────────────────────────────
        $payMethods = DB::table('financial_records')
            ->where('enterprise_id', $eid)
            ->where('type', 'EXPENDITURE')
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->groupBy('payment_method')
            ->select('payment_method', DB::raw('COUNT(*) as cnt'))
            ->get();

        // ── Recent expenditures ───────────────────────────────────────────────
        $recentExp = DB::table('financial_records as fr')
            ->leftJoin('account_parents as ap', 'ap.id', '=', 'fr.parent_account_id')
            ->leftJoin('accounts as a', 'a.id', '=', 'fr.account_id')
            ->where('fr.enterprise_id', $eid)
            ->where('fr.type', 'EXPENDITURE')
            ->orderBy('fr.id', 'desc')
            ->limit(8)
            ->select(
                'fr.id', 'fr.payment_date', 'fr.description', 'fr.amount',
                'fr.payment_method', 'ap.name as vote', 'a.name as account'
            )
            ->get();

        // ── Top creditors outstanding ─────────────────────────────────────────
        $topCreditors = DB::table('creditor_records as cr')
            ->leftJoin('admin_users as u', 'u.id', '=', 'cr.supplier_id')
            ->where('cr.enterprise_id', $eid)
            ->whereIn('cr.status', ['Pending', 'Partial'])
            ->select(
                'cr.id', 'cr.description', 'cr.original_amount',
                'cr.paid_amount', 'cr.balance', 'cr.status',
                'u.name as supplier'
            )
            ->orderByDesc('cr.balance')
            ->limit(5)
            ->get();

        // ── All-time totals ───────────────────────────────────────────────────
        $allTimeExp = abs(FinancialRecord::where('enterprise_id', $eid)
            ->where('type', 'EXPENDITURE')->sum('amount'));
        $allTimeBudget = FinancialRecord::where('enterprise_id', $eid)
            ->where('type', 'BUDGET')->sum('amount');

        return $content
            ->title('Finance Dashboard')
            ->breadcrumb(['text' => 'Finance', 'url' => '#'], ['text' => 'Dashboard'])
            ->body(view('admin.finance.dashboard', compact(
                'activeTerm', 'allTerms', 'thisTermExp', 'thisTermBudget',
                'thisTermExpCount', 'prevTermExp', 'creditorOutstanding',
                'creditorCount', 'creditorPaid', 'byVote',
                'voteLabels', 'voteActual', 'voteBudget',
                'monthLabels', 'monthAmounts', 'payMethods',
                'recentExp', 'topCreditors', 'allTimeExp', 'allTimeBudget'
            )));
    }
}
