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
        $q = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'EXPENDITURE'])
            ->with(['account', 'par', 'term', 'supplier', 'creditor_record']);

        if ($request->term_id)        $q->where('term_id', $request->term_id);
        if ($request->vote_id)        $q->where('parent_account_id', $request->vote_id);
        if ($request->account_id)     $q->where('account_id', $request->account_id);
        if ($request->supplier_id)    $q->where('supplier_id', $request->supplier_id);
        if ($request->payment_method) $q->where('payment_method', $request->payment_method);
        if ($request->q)              $q->where('description', 'like', '%'.$request->q.'%');

        $rows = $q->orderBy('id', 'desc')->get()->map(fn($r) => $this->fmtExp($r));
        return response()->json($rows);
    }

    public function apiExpShow($id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'EXPENDITURE'])
            ->with(['account', 'par', 'term', 'supplier', 'creditor_record'])->findOrFail($id);
        return response()->json($this->fmtExp($r));
    }

    public function apiExpStore(Request $request): JsonResponse
    {
        $u    = Admin::user();
        $data = $request->validate([
            'term_id'        => 'required|integer',
            'payment_date'   => 'required|date',
            'account_id'     => 'required|integer',
            'supplier_id'    => 'nullable|integer',
            'payment_method' => 'nullable|string|max:50',
            'quantity'       => 'required|numeric|min:0.001',
            'unit_price'     => 'required|numeric|min:0',
            'description'    => 'required|string|min:4|max:500',
            'is_credit'      => 'nullable|in:Yes,No',
            'credit_amount'  => 'nullable|numeric|min:0',
        ]);

        $data['enterprise_id'] = $u->enterprise_id;
        $data['created_by_id'] = $u->id;
        $data['type']          = 'EXPENDITURE';
        $data['is_credit']     = $data['is_credit'] ?? 'No';
        if (empty($data['supplier_id'])) $data['supplier_id'] = null;

        $r = FinancialRecord::create($data);
        $r->load(['account', 'par', 'term', 'supplier', 'creditor_record']);
        return response()->json(['success' => true, 'record' => $this->fmtExp($r)]);
    }

    public function apiExpUpdate(Request $request, $id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'EXPENDITURE'])
            ->findOrFail($id);

        $data = $request->validate([
            'term_id'        => 'required|integer',
            'payment_date'   => 'required|date',
            'account_id'     => 'required|integer',
            'supplier_id'    => 'nullable|integer',
            'payment_method' => 'nullable|string|max:50',
            'quantity'       => 'required|numeric|min:0.001',
            'unit_price'     => 'required|numeric|min:0',
            'description'    => 'required|string|min:4|max:500',
            'is_credit'      => 'nullable|in:Yes,No',
            'credit_amount'  => 'nullable|numeric|min:0',
        ]);

        $data['is_credit'] = $data['is_credit'] ?? 'No';
        if (empty($data['supplier_id'])) $data['supplier_id'] = null;

        $r->update($data);
        $r = $r->fresh(['account', 'par', 'term', 'supplier', 'creditor_record']);
        return response()->json(['success' => true, 'record' => $this->fmtExp($r)]);
    }

    public function apiExpDestroy($id): JsonResponse
    {
        FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'EXPENDITURE'])
            ->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function apiExpDuplicate($id): JsonResponse
    {
        $orig = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'EXPENDITURE'])
            ->findOrFail($id);
        $copy = $orig->replicate();
        $copy->is_credit     = 'No';
        $copy->credit_amount = null;
        $copy->created_by_id = Admin::user()->id;
        $copy->save();
        $copy->load(['account', 'par', 'term', 'supplier', 'creditor_record']);
        return response()->json(['success' => true, 'record' => $this->fmtExp($copy)]);
    }

    // ─── Budget API ────────────────────────────────────────────────────

    public function apiBudList(Request $request): JsonResponse
    {
        $q = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'BUDGET'])
            ->with(['account', 'par', 'term']);

        if ($request->term_id)    $q->where('term_id', $request->term_id);
        if ($request->vote_id)    $q->where('parent_account_id', $request->vote_id);
        if ($request->account_id) $q->where('account_id', $request->account_id);
        if ($request->q)          $q->where('description', 'like', '%'.$request->q.'%');

        $rows = $q->orderBy('id', 'desc')->get()->map(fn($r) => $this->fmtBud($r));
        return response()->json($rows);
    }

    public function apiBudShow($id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'BUDGET'])
            ->with(['account', 'par', 'term'])->findOrFail($id);
        return response()->json($this->fmtBud($r));
    }

    public function apiBudStore(Request $request): JsonResponse
    {
        $u    = Admin::user();
        $data = $request->validate([
            'term_id'      => 'required|integer',
            'payment_date' => 'required|date',
            'account_id'   => 'required|integer',
            'quantity'     => 'required|numeric|min:0.001',
            'unit_price'   => 'required|numeric|min:0',
            'description'  => 'required|string|min:4|max:500',
        ]);

        $data['enterprise_id'] = $u->enterprise_id;
        $data['created_by_id'] = $u->id;
        $data['type']          = 'BUDGET';

        $r = FinancialRecord::create($data);
        $r->load(['account', 'par', 'term']);
        return response()->json(['success' => true, 'record' => $this->fmtBud($r)]);
    }

    public function apiBudUpdate(Request $request, $id): JsonResponse
    {
        $r = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'BUDGET'])
            ->findOrFail($id);

        $data = $request->validate([
            'term_id'      => 'required|integer',
            'payment_date' => 'required|date',
            'account_id'   => 'required|integer',
            'quantity'     => 'required|numeric|min:0.001',
            'unit_price'   => 'required|numeric|min:0',
            'description'  => 'required|string|min:4|max:500',
        ]);

        $r->update($data);
        return response()->json(['success' => true, 'record' => $this->fmtBud($r->fresh(['account', 'par', 'term']))]);
    }

    public function apiBudDestroy($id): JsonResponse
    {
        FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'BUDGET'])
            ->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function apiBudDuplicate($id): JsonResponse
    {
        $orig = FinancialRecord::where(['enterprise_id' => $this->eid(), 'type' => 'BUDGET'])
            ->findOrFail($id);
        $copy = $orig->replicate();
        $copy->created_by_id = Admin::user()->id;
        $copy->save();
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

        $rows = $q->orderByRaw("FIELD(status,'Overdue','Pending','Partial','Paid')")
            ->orderBy('due_date')->orderBy('id', 'desc')
            ->get()->map(fn($r) => $this->fmtCred($r));

        return response()->json($rows);
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
        $u    = Admin::user();
        $data = $request->validate([
            'creditor_record_id' => 'required|integer',
            'amount_paid'        => 'required|numeric|min:1',
            'payment_date'       => 'required|date',
            'payment_method'     => 'nullable|string|max:50',
            'reference'          => 'nullable|string|max:200',
            'notes'              => 'nullable|string',
        ]);

        $cred = CreditorRecord::where('enterprise_id', $u->enterprise_id)
            ->findOrFail($data['creditor_record_id']);

        $data['enterprise_id'] = $u->enterprise_id;
        $data['created_by_id'] = $u->id;

        $p = CreditorPayment::create($data);
        $cred->updateStatus();
        $cred->load(['supplier', 'term']);

        return response()->json([
            'success'  => true,
            'payment'  => $this->fmtPay($p),
            'creditor' => $this->fmtCred($cred),
        ]);
    }

    public function apiPayDestroy($id): JsonResponse
    {
        $p    = CreditorPayment::where('enterprise_id', $this->eid())->findOrFail($id);
        $cred = CreditorRecord::find($p->creditor_record_id);
        $p->delete();
        if ($cred) {
            $cred->updateStatus();
            $cred->load(['supplier', 'term']);
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
                'u.email', 'u.current_address', 'u.description',
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
                'u.email', 'u.current_address', 'u.description',
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
            'description'     => 'nullable|string|max:1000',
        ]);

        $eid  = $this->eid();
        $base = 'sup_'.preg_replace('/[^a-z0-9]/', '', strtolower($data['name']));
        $base = substr($base, 0, 20);
        do {
            $username = $base.'_'.rand(1000, 9999);
        } while (Administrator::where('username', $username)->exists());

        $parts    = explode(' ', trim($data['name']), 2);
        $supplier = Administrator::create([
            'name'            => $data['name'],
            'first_name'      => $parts[0],
            'last_name'       => $parts[1] ?? $parts[0],
            'username'        => $username,
            'password'        => bcrypt(Str::random(12)),
            'email'           => $data['email'] ?? null,
            'phone_number_1'  => $data['phone_number_1'],
            'phone_number_2'  => $data['phone_number_2'] ?? null,
            'current_address' => $data['current_address'] ?? null,
            'description'     => $data['description'] ?? null,
            'user_type'       => 'supplier',
            'enterprise_id'   => $eid,
        ]);

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
            'description'     => 'nullable|string|max:1000',
        ]);

        $parts = explode(' ', trim($data['name']), 2);
        $supplier->update([
            'name'            => $data['name'],
            'first_name'      => $parts[0],
            'last_name'       => $parts[1] ?? $parts[0],
            'email'           => $data['email'] ?? null,
            'phone_number_1'  => $data['phone_number_1'],
            'phone_number_2'  => $data['phone_number_2'] ?? null,
            'current_address' => $data['current_address'] ?? null,
            'description'     => $data['description'] ?? null,
        ]);

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
