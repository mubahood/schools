<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\AccountParent;
use App\Models\CreditorPayment;
use App\Models\CreditorRecord;
use App\Models\FinancialRecord;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\Finance\FinanceException;
use App\Services\Finance\FinanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceController extends Controller
{
    // ─── Shared helpers ────────────────────────────────────────────────

    private function eid(): int
    {
        return Admin::user()->enterprise_id;
    }

    private function terms()
    {
        return Term::with('academic_year')
            ->where('enterprise_id', $this->eid())
            ->orderBy('id', 'desc')->get();
    }

    private function activeTerm(): ?Term
    {
        return Term::where(['enterprise_id' => $this->eid(), 'is_active' => 1])->first();
    }

    private function votes()
    {
        return AccountParent::where('enterprise_id', $this->eid())
            ->orderBy('name')->get(['id', 'name']);
    }

    private function accounts()
    {
        return Account::where([
            'enterprise_id' => $this->eid(),
            'type'          => 'OTHER_ACCOUNT',
        ])->whereNotNull('account_parent_id')
          ->orderBy('name')->get(['id', 'name', 'account_parent_id']);
    }

    private function suppliersJson(): string
    {
        return Administrator::where([
            'enterprise_id' => $this->eid(),
            'user_type'     => 'supplier',
        ])->orderBy('name')->get(['id', 'name'])
          ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
          ->toJson();
    }

    /**
     * Rules shared by budgets and expenditures. Every foreign key is checked
     * against THIS school, so a crafted request cannot file our money under
     * another school's account or term.
     */
    private function rules(bool $expenditure): array
    {
        $eid = $this->eid();
        $mine = fn ($table) => function ($attr, $value, $fail) use ($table, $eid) {
            if ($value && !DB::table($table)->where('id', $value)->where('enterprise_id', $eid)->exists()) {
                $fail('That ' . Str::singular(str_replace('_', ' ', $table)) . ' does not belong to your school.');
            }
        };

        $rules = [
            'term_id'      => ['required', 'integer', $mine('terms')],
            'payment_date' => 'required|date|before_or_equal:' . now()->addDay()->toDateString(),
            'account_id'   => ['required', 'integer', $mine('accounts')],
            'quantity'     => 'required|integer|min:1|max:1000000',
            'unit_price'   => 'required|integer|min:0|max:100000000000',
            'description'  => 'required|string|min:4|max:500',
        ];
        if ($expenditure) {
            $rules += [
                'supplier_id'    => ['nullable', 'integer', $mine('admin_users')],
                'payment_method' => 'nullable|string|max:50',
                'is_credit'      => 'nullable|in:Yes,No',
                'credit_amount'  => 'nullable|integer|min:0',
            ];
        }

        return $rules;
    }

    /** One page of a filtered query, with the totals computed in the database. */
    private function page($query, Request $request, callable $fmt): array
    {
        $perPage = min(500, max(10, (int) $request->get('per_page', 50)));
        $page    = max(1, (int) $request->get('page', 1));

        $totals = (clone $query)->selectRaw('COUNT(*) n, COALESCE(SUM(ABS(amount)),0) total')->first();
        $rows   = $query->orderByDesc('payment_date')->orderByDesc('id')
            ->forPage($page, $perPage)->get()->map($fmt);

        return [
            'data'  => $rows,
            'meta'  => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => (int) $totals->n,
                'last_page' => max(1, (int) ceil($totals->n / $perPage)),
                'sum'       => (float) $totals->total,
            ],
        ];
    }

    private function fail(FinanceException $e): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }

    private function fmtExp(FinancialRecord $r): array
    {
        return [
            'id'             => $r->id,
            'payment_date'   => $r->payment_date ? substr($r->payment_date, 0, 10) : '',
            'description'    => $r->description ?? '',
            'quantity'       => (float)$r->quantity,
            'unit_price'     => (float)$r->unit_price,
            'amount'         => abs((float)$r->amount),
            'account_id'     => $r->account_id,
            'account'        => optional($r->account)->name ?? '—',
            'vote_id'        => $r->parent_account_id,
            'vote'           => optional($r->par)->name ?? '—',
            'supplier_id'    => $r->supplier_id,
            'supplier'       => optional($r->supplier)->name ?? '—',
            'payment_method' => $r->payment_method ?? '',
            'term_id'        => $r->term_id,
            'term'           => optional($r->term)->name_text ?? '—',
            'is_credit'      => $r->is_credit ?? 'No',
            'credit_amount'  => $r->credit_amount,
            'has_creditor'   => $r->creditor_record !== null,
            'creditor_id'    => optional($r->creditor_record)->id,
        ];
    }

    private function fmtBud(FinancialRecord $r): array
    {
        return [
            'id'           => $r->id,
            'payment_date' => $r->payment_date ? substr($r->payment_date, 0, 10) : '',
            'description'  => $r->description ?? '',
            'quantity'     => (float)$r->quantity,
            'unit_price'   => (float)$r->unit_price,
            'amount'       => abs((float)$r->amount),
            'account_id'   => $r->account_id,
            'account'      => optional($r->account)->name ?? '—',
            'vote_id'      => $r->parent_account_id,
            'vote'         => optional($r->par)->name ?? '—',
            'term_id'      => $r->term_id,
            'term'         => optional($r->term)->name_text ?? '—',
        ];
    }

    private function fmtCred(CreditorRecord $r): array
    {
        return [
            'id'              => $r->id,
            'supplier_id'     => $r->supplier_id,
            'supplier'        => optional($r->supplier)->name ?? '—',
            'description'     => $r->description ?? '',
            'original_amount' => (float)$r->original_amount,
            'paid_amount'     => (float)$r->paid_amount,
            'balance'         => (float)$r->balance,
            'status'          => $r->status ?? 'Pending',
            'due_date'        => $r->due_date ? substr($r->due_date, 0, 10) : '',
            'payment_method'  => $r->payment_method ?? '',
            'notes'           => $r->notes ?? '',
            'term_id'         => $r->term_id,
            'term'            => optional($r->term)->name_text ?? '—',
            'fin_record_id'   => $r->financial_record_id,
        ];
    }

    private function fmtPay(CreditorPayment $p): array
    {
        return [
            'id'             => $p->id,
            'payment_date'   => $p->payment_date ? substr($p->payment_date, 0, 10) : '',
            'amount_paid'    => (float)$p->amount_paid,
            'payment_method' => $p->payment_method ?? '',
            'reference'      => $p->reference ?? '',
            'notes'          => $p->notes ?? '',
        ];
    }

    // ─── Pages ─────────────────────────────────────────────────────────

    public function expenditures(Content $content)
    {
        $activeTerm   = $this->activeTerm();
        $terms        = $this->terms();
        $votes        = $this->votes();
        $accounts     = $this->accounts();
        $suppliersJson = $this->suppliersJson();
        $API          = admin_url('finance/api/expenditures');
        $ACC_API      = admin_url('finance/api/accounts-by-vote');
        $CSRF         = csrf_token();
        $activeTermId = $activeTerm ? $activeTerm->id : 0;

        return $content
            ->title('Expenditures')
            ->breadcrumb(['text' => 'Finance', 'url' => '#'], ['text' => 'Expenditures'])
            ->body(view('admin.finance.expenditures', compact(
                'activeTerm', 'activeTermId', 'terms', 'votes', 'accounts',
                'suppliersJson', 'API', 'ACC_API', 'CSRF'
            )));
    }

    public function budgets(Content $content)
    {
        $activeTerm   = $this->activeTerm();
        $terms        = $this->terms();
        $votes        = $this->votes();
        $accounts     = $this->accounts();
        $API          = admin_url('finance/api/budgets');
        $ACC_API      = admin_url('finance/api/accounts-by-vote');
        $CSRF         = csrf_token();
        $activeTermId = $activeTerm ? $activeTerm->id : 0;

        return $content
            ->title('Budget')
            ->breadcrumb(['text' => 'Finance', 'url' => '#'], ['text' => 'Budget'])
            ->body(view('admin.finance.budgets', compact(
                'activeTerm', 'activeTermId', 'terms', 'votes', 'accounts',
                'API', 'ACC_API', 'CSRF'
            )));
    }

    public function creditors(Content $content)
    {
        $activeTerm   = $this->activeTerm();
        $terms        = $this->terms();
        $suppliersJson = $this->suppliersJson();
        $CRED_API     = admin_url('finance/api/creditors');
        $PAY_API      = admin_url('finance/api/creditor-payments');
        $CSRF         = csrf_token();
        $activeTermId = $activeTerm ? $activeTerm->id : 0;

        return $content
            ->title('Creditors')
            ->breadcrumb(['text' => 'Finance', 'url' => '#'], ['text' => 'Creditors'])
            ->body(view('admin.finance.creditors', compact(
                'activeTerm', 'activeTermId', 'terms', 'suppliersJson',
                'CRED_API', 'PAY_API', 'CSRF'
            )));
    }

    // ─── Expenditure API ───────────────────────────────────────────────

    public function apiExpList(Request $request): JsonResponse
    {
        $q = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_EXPENDITURE])
            ->with(['account', 'par', 'term', 'supplier', 'creditor_record']);

        if ($request->term_id)        $q->where('term_id', $request->term_id);
        if ($request->vote_id)        $q->where('parent_account_id', $request->vote_id);
        if ($request->account_id)     $q->where('account_id', $request->account_id);
        if ($request->supplier_id)    $q->where('supplier_id', $request->supplier_id);
        if ($request->payment_method) $q->where('payment_method', $request->payment_method);
        if ($request->date_from)      $q->whereDate('payment_date', '>=', $request->date_from);
        if ($request->date_to)        $q->whereDate('payment_date', '<=', $request->date_to);
        if ($request->q)              $q->where('description', 'like', '%' . $request->q . '%');

        return response()->json($this->page($q, $request, fn ($r) => $this->fmtExp($r)));
    }

    public function apiExpShow($id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_EXPENDITURE])
            ->with(['account', 'par', 'term', 'supplier', 'creditor_record'])->findOrFail($id);
        return response()->json($this->fmtExp($r));
    }

    public function apiExpStore(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(true));
        $data['enterprise_id'] = $this->eid();
        $data['created_by_id'] = Admin::user()->id;
        $data['is_credit']     = $data['is_credit'] ?? 'No';
        if (empty($data['supplier_id'])) $data['supplier_id'] = null;

        try {
            $r = FinanceService::create($data, FinanceService::TYPE_EXPENDITURE);
        } catch (FinanceException $e) {
            return $this->fail($e);
        }
        $r->load(['account', 'par', 'term', 'supplier', 'creditor_record']);

        return response()->json(['success' => true, 'record' => $this->fmtExp($r)]);
    }

    public function apiExpUpdate(Request $request, $id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_EXPENDITURE])
            ->findOrFail($id);
        $data = $request->validate($this->rules(true));
        $data['is_credit'] = $data['is_credit'] ?? 'No';
        if (empty($data['supplier_id'])) $data['supplier_id'] = null;

        try {
            $r = FinanceService::update($r, $data);
        } catch (FinanceException $e) {
            return $this->fail($e);
        }
        $r = $r->fresh(['account', 'par', 'term', 'supplier', 'creditor_record']);

        return response()->json(['success' => true, 'record' => $this->fmtExp($r)]);
    }

    public function apiExpDestroy($id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_EXPENDITURE])
            ->findOrFail($id);
        try {
            FinanceService::delete($r);
        } catch (FinanceException $e) {
            return $this->fail($e);
        }

        return response()->json(['success' => true]);
    }

    public function apiExpDuplicate($id): JsonResponse
    {
        $orig = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_EXPENDITURE])
            ->findOrFail($id);

        $copy = FinanceService::create([
            'enterprise_id'  => $orig->enterprise_id,
            'account_id'     => $orig->account_id,
            'term_id'        => $orig->term_id,
            'supplier_id'    => $orig->supplier_id,
            'created_by_id'  => Admin::user()->id,
            'description'    => $orig->description,
            // A copy is today's spending, not a second copy of an old date.
            'payment_date'   => now()->toDateString(),
            'payment_method' => $orig->payment_method,
            'quantity'       => $orig->quantity,
            'unit_price'     => $orig->unit_price,
            'is_credit'      => 'No',
        ], FinanceService::TYPE_EXPENDITURE);

        $copy->load(['account', 'par', 'term', 'supplier', 'creditor_record']);

        return response()->json(['success' => true, 'record' => $this->fmtExp($copy)]);
    }

    // ─── Budget API ────────────────────────────────────────────────────

    public function apiBudList(Request $request): JsonResponse
    {
        $q = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_BUDGET])
            ->with(['account', 'par', 'term']);

        if ($request->term_id)    $q->where('term_id', $request->term_id);
        if ($request->vote_id)    $q->where('parent_account_id', $request->vote_id);
        if ($request->account_id) $q->where('account_id', $request->account_id);
        if ($request->q)          $q->where('description', 'like', '%' . $request->q . '%');

        return response()->json($this->page($q, $request, fn ($r) => $this->fmtBud($r)));
    }

    public function apiBudShow($id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_BUDGET])
            ->with(['account', 'par', 'term'])->findOrFail($id);
        return response()->json($this->fmtBud($r));
    }

    public function apiBudStore(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(false));
        $data['enterprise_id'] = $this->eid();
        $data['created_by_id'] = Admin::user()->id;

        try {
            $r = FinanceService::create($data, FinanceService::TYPE_BUDGET);
        } catch (FinanceException $e) {
            return $this->fail($e);
        }
        $r->load(['account', 'par', 'term']);

        return response()->json(['success' => true, 'record' => $this->fmtBud($r)]);
    }

    public function apiBudUpdate(Request $request, $id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_BUDGET])
            ->findOrFail($id);
        try {
            $r = FinanceService::update($r, $request->validate($this->rules(false)));
        } catch (FinanceException $e) {
            return $this->fail($e);
        }
        $r = $r->fresh(['account', 'par', 'term']);

        return response()->json(['success' => true, 'record' => $this->fmtBud($r)]);
    }

    public function apiBudDestroy($id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_BUDGET])
            ->findOrFail($id);
        try {
            FinanceService::delete($r);
        } catch (FinanceException $e) {
            return $this->fail($e);
        }

        return response()->json(['success' => true]);
    }

    public function apiBudDuplicate($id): JsonResponse
    {
        $orig = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => FinanceService::TYPE_BUDGET])
            ->findOrFail($id);

        $copy = FinanceService::create([
            'enterprise_id' => $orig->enterprise_id,
            'account_id'    => $orig->account_id,
            'term_id'       => $orig->term_id,
            'created_by_id' => Admin::user()->id,
            'description'   => $orig->description,
            'payment_date'  => now()->toDateString(),
            'quantity'      => $orig->quantity,
            'unit_price'    => $orig->unit_price,
        ], FinanceService::TYPE_BUDGET);

        $copy->load(['account', 'par', 'term']);

        return response()->json(['success' => true, 'record' => $this->fmtBud($copy)]);
    }

    // ─── Creditor API ──────────────────────────────────────────────────

    public function apiCredList(Request $request): JsonResponse
    {
        $eid = $this->eid();

        // Auto-mark overdue first
        CreditorRecord::where('enterprise_id', $eid)
            ->whereIn('status', ['Pending', 'Partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'Overdue']);

        $q = CreditorRecord::where('enterprise_id', $eid)->with(['supplier', 'term']);

        if ($request->status && $request->status !== 'all')
            $q->where('status', $request->status);
        if ($request->supplier_id)
            $q->where('supplier_id', $request->supplier_id);
        if ($request->q)
            $q->where('description', 'like', '%'.$request->q.'%');

        $q->orderByRaw("FIELD(status,'Overdue','Pending','Partial','Paid')")
            ->orderBy('due_date')->orderBy('id', 'desc');

        $perPage = min(500, max(10, (int) $request->get('per_page', 50)));
        $page    = max(1, (int) $request->get('page', 1));
        $totals  = (clone $q)->selectRaw('COUNT(*) n, COALESCE(SUM(balance),0) total')->first();

        return response()->json([
            'data' => $q->forPage($page, $perPage)->get()->map(fn ($r) => $this->fmtCred($r)),
            'meta' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => (int) $totals->n,
                'last_page' => max(1, (int) ceil($totals->n / $perPage)),
                'sum'       => (float) $totals->total,
            ],
        ]);
    }

    public function apiCredShow($id): JsonResponse
    {
        $r = CreditorRecord::where('enterprise_id', $this->eid())
            ->with(['supplier', 'term', 'payments'])->findOrFail($id);
        $data = $this->fmtCred($r);
        $data['payments'] = $r->payments->sortByDesc('id')
            ->map(fn($p) => $this->fmtPay($p))->values();
        return response()->json($data);
    }

    public function apiCredStore(Request $request): JsonResponse
    {
        $u    = Admin::user();
        $data = $request->validate([
            'supplier_id'     => 'nullable|integer',
            'description'     => 'required|string|max:500',
            'original_amount' => 'required|numeric|min:1',
            'due_date'        => 'nullable|date',
            'payment_method'  => 'nullable|string|max:50',
            'notes'           => 'nullable|string',
            'term_id'         => 'required|integer',
        ]);

        if (empty($data['supplier_id'])) $data['supplier_id'] = null;
        $data['enterprise_id'] = $u->enterprise_id;
        $data['created_by_id'] = $u->id;
        $data['paid_amount']   = 0;
        $data['balance']       = $data['original_amount'];
        $data['status']        = 'Pending';

        $r = CreditorRecord::create($data);
        $r->load(['supplier', 'term']);
        return response()->json(['success' => true, 'record' => $this->fmtCred($r)]);
    }

    public function apiCredUpdate(Request $request, $id): JsonResponse
    {
        $r = CreditorRecord::where('enterprise_id', $this->eid())->findOrFail($id);

        $data = $request->validate([
            'supplier_id'    => 'nullable|integer',
            'description'    => 'required|string|max:500',
            'due_date'       => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        if (empty($data['supplier_id'])) $data['supplier_id'] = null;
        $r->update($data);
        $r->refreshStatus();
        $r->load(['supplier', 'term']);
        return response()->json(['success' => true, 'record' => $this->fmtCred($r)]);
    }

    public function apiCredDestroy($id): JsonResponse
    {
        $r = CreditorRecord::where('enterprise_id', $this->eid())->findOrFail($id);
        $r->payments()->delete();
        $r->delete();
        return response()->json(['success' => true]);
    }

    // ─── Creditor Payment API ──────────────────────────────────────────

    public function apiPayList(Request $request): JsonResponse
    {
        $q = CreditorPayment::where('enterprise_id', $this->eid());
        if ($request->creditor_record_id)
            $q->where('creditor_record_id', $request->creditor_record_id);
        return response()->json($q->orderBy('id', 'desc')->get()->map(fn($p) => $this->fmtPay($p)));
    }

    public function apiPayStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'creditor_record_id' => 'required|integer',
            'amount_paid'        => 'required|integer|min:1',
            'payment_date'       => 'required|date|before_or_equal:' . now()->addDay()->toDateString(),
            'payment_method'     => 'nullable|string|max:50',
            'reference'          => 'nullable|string|max:200',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $cred = CreditorRecord::where('enterprise_id', $this->eid())->findOrFail($data['creditor_record_id']);
        $data['created_by_id'] = Admin::user()->id;

        try {
            $p = FinanceService::payCreditor($cred, $data);
        } catch (FinanceException $e) {
            return $this->fail($e);
        }
        $cred = $cred->fresh(['supplier', 'term']);

        return response()->json([
            'success'  => true,
            'payment'  => $this->fmtPay($p),
            'creditor' => $this->fmtCred($cred),
        ]);
    }

    public function apiPayDestroy($id): JsonResponse
    {
        $p    = CreditorPayment::where('enterprise_id', $this->eid())->findOrFail($id);
        $cred = FinanceService::deleteCreditorPayment($p);
        if ($cred) {
            $cred = $cred->fresh(['supplier', 'term']);
            return response()->json(['success' => true, 'creditor' => $this->fmtCred($cred)]);
        }

        return response()->json(['success' => true]);
    }

    // ─── Supplier helpers ──────────────────────────────────────────────

    private function fetchSupRow(int $eid, int $id)
    {
        return DB::table('admin_users as u')
            ->leftJoin(DB::raw("(
                SELECT supplier_id,
                       SUM(ABS(amount)) AS total_exp,
                       COUNT(*)         AS exp_count
                FROM financial_records
                WHERE enterprise_id = {$eid}
                  AND type = 'EXPENDITURE'
                  AND supplier_id IS NOT NULL
                GROUP BY supplier_id
            ) exp"), 'exp.supplier_id', '=', 'u.id')
            ->leftJoin(DB::raw("(
                SELECT supplier_id,
                       SUM(balance) AS outstanding,
                       COUNT(*)     AS cred_count
                FROM creditor_records
                WHERE enterprise_id = {$eid}
                  AND status IN ('Pending','Partial','Overdue')
                  AND supplier_id IS NOT NULL
                GROUP BY supplier_id
            ) cr"), 'cr.supplier_id', '=', 'u.id')
            ->where('u.enterprise_id', $eid)
            ->where('u.user_type', 'supplier')
            ->where('u.id', $id)
            ->select(
                'u.id', 'u.name', 'u.phone_number_1', 'u.phone_number_2',
                'u.email', 'u.current_address',
                DB::raw('COALESCE(exp.total_exp,  0) AS total_expenditure'),
                DB::raw('COALESCE(exp.exp_count,  0) AS exp_count'),
                DB::raw('COALESCE(cr.outstanding, 0) AS outstanding_credit'),
                DB::raw('COALESCE(cr.cred_count,  0) AS cred_count')
            )
            ->first();
    }

    private function fmtSup(\stdClass $s): array
    {
        return [
            'id'                 => $s->id,
            'name'               => $s->name ?? '',
            'phone_number_1'     => $s->phone_number_1 ?? '',
            'phone_number_2'     => $s->phone_number_2 ?? '',
            'email'              => $s->email ?? '',
            'current_address'    => $s->current_address ?? '',
            'description'        => $s->description ?? '',
            'total_expenditure'  => (float) $s->total_expenditure,
            'exp_count'          => (int)   $s->exp_count,
            'outstanding_credit' => (float) $s->outstanding_credit,
            'cred_count'         => (int)   $s->cred_count,
        ];
    }

    // ─── Suppliers page ────────────────────────────────────────────────

    public function suppliers(Content $content)
    {
        $SUP_API = admin_url('finance/api/suppliers');
        $CSRF    = csrf_token();

        return $content
            ->title('Suppliers')
            ->breadcrumb(['text' => 'Finance', 'url' => '#'], ['text' => 'Suppliers'])
            ->body(view('admin.finance.suppliers', compact('SUP_API', 'CSRF')));
    }

    // ─── Suppliers API ─────────────────────────────────────────────────

    public function apiSupList(Request $request): JsonResponse
    {
        $eid = (int) $this->eid();
        $q   = $request->q;

        $rows = DB::table('admin_users as u')
            ->leftJoin(DB::raw("(
                SELECT supplier_id,
                       SUM(ABS(amount)) AS total_exp,
                       COUNT(*)         AS exp_count
                FROM financial_records
                WHERE enterprise_id = {$eid}
                  AND type = 'EXPENDITURE'
                  AND supplier_id IS NOT NULL
                GROUP BY supplier_id
            ) exp"), 'exp.supplier_id', '=', 'u.id')
            ->leftJoin(DB::raw("(
                SELECT supplier_id,
                       SUM(balance) AS outstanding,
                       COUNT(*)     AS cred_count
                FROM creditor_records
                WHERE enterprise_id = {$eid}
                  AND status IN ('Pending','Partial','Overdue')
                  AND supplier_id IS NOT NULL
                GROUP BY supplier_id
            ) cr"), 'cr.supplier_id', '=', 'u.id')
            ->where('u.enterprise_id', $eid)
            ->where('u.user_type', 'supplier')
            ->select(
                'u.id', 'u.name', 'u.phone_number_1', 'u.phone_number_2',
                'u.email', 'u.current_address',
                DB::raw('COALESCE(exp.total_exp,  0) AS total_expenditure'),
                DB::raw('COALESCE(exp.exp_count,  0) AS exp_count'),
                DB::raw('COALESCE(cr.outstanding, 0) AS outstanding_credit'),
                DB::raw('COALESCE(cr.cred_count,  0) AS cred_count')
            )
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('u.name', 'like', "%{$q}%")
                        ->orWhere('u.phone_number_1', 'like', "%{$q}%")
                        ->orWhere('u.email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc(DB::raw('COALESCE(exp.total_exp, 0)'))
            ->orderBy('u.name')
            ->get();

        return response()->json($rows->map(fn($s) => $this->fmtSup($s)));
    }

    public function apiSupShow($id): JsonResponse
    {
        $s = $this->fetchSupRow((int) $this->eid(), (int) $id);
        abort_if(!$s, 404);
        return response()->json($this->fmtSup($s));
    }

    public function apiSupStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:200',
            'phone_number_1'  => 'required|string|max:30',
            'phone_number_2'  => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:150',
            'current_address' => 'nullable|string|max:300',
        ]);

        $eid  = $this->eid();
        $base = 'sup_'.preg_replace('/[^a-z0-9]/', '', strtolower($data['name']));
        $base = substr($base, 0, 20);
        do {
            $username = $base.'_'.rand(1000, 9999);
        } while (Administrator::where('username', $username)->exists());

        $parts = explode(' ', trim($data['name']), 2);

        // Assigned column by column rather than via Administrator::create().
        // Administrator is a vendor model with no $fillable, so Laravel falls
        // back to $guarded = ['*'] and mass assignment throws. Adding $fillable
        // there would mean editing vendor code, which composer install wipes.
        $supplier                  = new Administrator();
        $supplier->name            = $data['name'];
        $supplier->first_name      = $parts[0];
        $supplier->last_name       = $parts[1] ?? $parts[0];
        $supplier->username        = $username;
        $supplier->password        = bcrypt(Str::random(12));
        $supplier->email           = $data['email'] ?? null;
        $supplier->phone_number_1  = $data['phone_number_1'];
        $supplier->phone_number_2  = $data['phone_number_2'] ?? null;
        $supplier->current_address = $data['current_address'] ?? null;
        // NOTE: no `description` write. admin_users is already at MySQL's
        // 65535-byte row limit, so the column cannot be added; see apiSupStore
        // notes. Supplier notes need their own table if the feature is wanted.
        $supplier->user_type       = 'supplier';
        $supplier->enterprise_id   = $eid;
        $supplier->save();

        $row = $this->fetchSupRow($eid, $supplier->id) ?: (object) [
            'id'                 => $supplier->id,
            'name'               => $supplier->name,
            'phone_number_1'     => $supplier->phone_number_1,
            'phone_number_2'     => $supplier->phone_number_2,
            'email'              => $supplier->email,
            'current_address'    => $supplier->current_address,
            'description'        => $supplier->description,
            'total_expenditure'  => 0,
            'exp_count'          => 0,
            'outstanding_credit' => 0,
            'cred_count'         => 0,
        ];

        return response()->json(['success' => true, 'record' => $this->fmtSup($row)]);
    }

    public function apiSupUpdate(Request $request, $id): JsonResponse
    {
        $supplier = Administrator::where([
            'enterprise_id' => $this->eid(),
            'user_type'     => 'supplier',
        ])->findOrFail($id);

        $data = $request->validate([
            'name'            => 'required|string|max:200',
            'phone_number_1'  => 'required|string|max:30',
            'phone_number_2'  => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:150',
            'current_address' => 'nullable|string|max:300',
        ]);

        $parts = explode(' ', trim($data['name']), 2);
        // Column-by-column for the same reason as the create above: Administrator
        // is a vendor model with no $fillable, so ->update([...]) would throw.
        $supplier->name            = $data['name'];
        $supplier->first_name      = $parts[0];
        $supplier->last_name       = $parts[1] ?? $parts[0];
        $supplier->email           = $data['email'] ?? null;
        $supplier->phone_number_1  = $data['phone_number_1'];
        $supplier->phone_number_2  = $data['phone_number_2'] ?? null;
        $supplier->current_address = $data['current_address'] ?? null;
        $supplier->save();

        $row = $this->fetchSupRow((int) $this->eid(), (int) $id);
        return response()->json(['success' => true, 'record' => $this->fmtSup($row)]);
    }

    public function apiSupDestroy($id): JsonResponse
    {
        $eid      = $this->eid();
        $supplier = Administrator::where([
            'enterprise_id' => $eid,
            'user_type'     => 'supplier',
        ])->findOrFail($id);

        $expCount  = FinancialRecord::where(['enterprise_id' => $eid, 'supplier_id' => $id])->count();
        $credCount = CreditorRecord::where(['enterprise_id' => $eid, 'supplier_id' => $id])->count();

        if ($expCount > 0 || $credCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete: this supplier has {$expCount} expenditure(s) and {$credCount} creditor record(s). Reassign or delete those records first.",
            ], 422);
        }

        $supplier->delete();
        return response()->json(['success' => true]);
    }

    // ─── Cascade / Lookup APIs ─────────────────────────────────────────

    public function apiAccountsByVote(Request $request): JsonResponse
    {
        $q = Account::where([
            'enterprise_id' => $this->eid(),
            'type'          => 'OTHER_ACCOUNT',
        ])->whereNotNull('account_parent_id')->orderBy('name');

        if ($request->vote_id) {
            $q->where('account_parent_id', $request->vote_id);
        }

        return response()->json($q->get(['id', 'name', 'account_parent_id'])->values());
    }
}
