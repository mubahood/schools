<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\SplitTransaction;
use App\Models\SplitTransactionItem;
use App\Models\Transaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SplitTransactionController extends AdminController
{
    protected $title = 'Split Transactions';

    // ── AJAX: search transactions for the enterprise ─────────────────────────
    public function searchTransaction(Request $request)
    {
        $u   = Admin::user();
        $id  = (int) $request->get('id');
        $q   = trim($request->get('q', ''));

        $query = DB::table('transactions as t')
            ->join('accounts as a', 't.account_id', '=', 'a.id')
            ->join('admin_users as u', 'a.administrator_id', '=', 'u.id')
            ->where('t.enterprise_id', $u->enterprise_id)
            ->where('t.amount', '>', 0) // only positive (payments)
            ->orderByDesc('t.id')
            ->limit(50)
            ->select(
                't.id', 't.amount', 't.description', 't.payment_date', 't.source',
                'u.name as student_name', 'u.id as student_id', 'a.id as account_id'
            );

        if ($id > 0) {
            $query->where('t.id', $id);
        } elseif (strlen($q) >= 2) {
            $query->where(function ($w) use ($q) {
                $w->where('u.name', 'like', "%{$q}%")
                  ->orWhere('t.description', 'like', "%{$q}%")
                  ->orWhere('t.id', $q);
            });
        }

        $rows = $query->get()->map(function ($r) {
            return [
                'id'           => $r->id,
                'student_id'   => $r->student_id,
                'student_name' => $r->student_name,
                'amount'       => (int) $r->amount,
                'description'  => $r->description,
                'payment_date' => $r->payment_date,
                'source'       => $r->source,
                'label'        => 'Txn#' . $r->id . ' — ' . $r->student_name . ' — UGX ' . number_format($r->amount) . ($r->payment_date ? ' (' . date('d M Y', strtotime($r->payment_date)) . ')' : ''),
            ];
        });

        return response()->json($rows);
    }

    // ── AJAX: search students for split-item picker ──────────────────────────
    public function searchStudent(Request $request)
    {
        $u = Admin::user();
        $q = trim($request->get('q', ''));

        $query = DB::table('admin_users')
            ->where('enterprise_id', $u->enterprise_id)
            ->where('user_type', 'student')
            ->where('status', 1)
            ->orderBy('name')
            ->limit(30)
            ->select('id', 'name');

        if (strlen($q) >= 2) {
            $query->where('name', 'like', "%{$q}%");
        }

        $rows = $query->get()->map(fn($r) => [
            'id'   => $r->id,
            'text' => $r->name,
        ]);

        return response()->json(['results' => $rows]);
    }

    // ── Index: grid list ─────────────────────────────────────────────────────
    protected function grid()
    {
        $u    = Admin::user();
        $grid = new Grid(new SplitTransaction());

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderByDesc('id');

        $grid->disableExport();
        $grid->disableColumnSelector();

        $grid->column('id', '#')->sortable()->display(function ($id) {
            return '<a href="' . url('split-transactions/' . $id) . '" style="font-weight:700">' . $id . '</a>';
        });
        $grid->column('original_transaction_id', 'Original Txn')->display(function ($txnId) {
            $txn = Transaction::find($txnId);
            if (!$txn) return '#' . $txnId;
            $student = optional(optional($txn->account)->owner)->name ?? '—';
            return '<a href="' . url('transactions/' . $txnId) . '" target="_blank" style="font-weight:600">#' . $txnId . '</a>'
                 . '<br><small style="color:#888">' . e($student) . '</small>';
        });
        $grid->column('original_amount', 'Amount')->display(fn($v) => number_format($v));
        $grid->column('original_remaining_amount', 'Remaining')->display(fn($v) => number_format($v));
        $grid->column('items_count', 'Students')->display(function () {
            return '<span class="badge" style="background:#2d6a4f">' . $this->items()->count() . '</span>';
        });
        $grid->column('status', 'Status')->display(function ($s) {
            $color = $s === 'Applied' ? '#28a745' : '#fd7e14';
            return '<span style="background:' . $color . ';color:#fff;padding:3px 9px;border-radius:12px;font-size:.75rem;font-weight:700">' . $s . '</span>';
        });
        $grid->column('created_at', 'Date')->display(fn($v) => date('d M Y', strtotime($v)));
        $grid->column('view_btn', '')->display(function () {
            return '<a href="' . url('split-transactions/' . $this->id) . '" class="btn btn-xs btn-primary">'
                 . '<i class="fa fa-eye"></i> View</a>';
        });

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('status', 'Status')->select(['Draft' => 'Draft', 'Applied' => 'Applied']);
        });

        return $grid;
    }

    // ── Detail: show a split record ──────────────────────────────────────────
    protected function detail($id)
    {
        // Redirect to our custom show view
        return redirect()->route('.split-transactions.show', $id);
    }

    public function show($id, Content $content)
    {
        $u     = Admin::user();
        $split = SplitTransaction::where('enterprise_id', $u->enterprise_id)->findOrFail($id);
        $split->load('items.toAccount.owner', 'items.toTransaction', 'originalTransaction.account.owner', 'createdBy');

        return $content
            ->title('Split Transaction #' . $id)
            ->breadcrumb(
                ['text' => 'School fees', 'url' => '#'],
                ['text' => 'Split Transactions', 'url' => url('split-transactions')],
                ['text' => 'Detail']
            )
            ->body(view('admin.split-transaction.show', compact('split')));
    }

    // ── Create: custom form ──────────────────────────────────────────────────
    public function create(Content $content)
    {
        $u     = Admin::user();
        $txnId = (int) request('transaction_id', 0);

        // Pre-load recent positive transactions (last 300) for this enterprise
        $transactions = DB::table('transactions as t')
            ->join('accounts as a', 't.account_id', '=', 'a.id')
            ->join('admin_users as u', 'a.administrator_id', '=', 'u.id')
            ->where('t.enterprise_id', $u->enterprise_id)
            ->where('t.amount', '>', 0)
            ->orderByDesc('t.id')
            ->limit(300)
            ->select('t.id', 't.amount', 't.description', 't.payment_date', 't.source',
                     'u.name as student_name', 'u.id as student_id')
            ->get();

        $prefillTxn = $txnId > 0 ? $transactions->firstWhere('id', $txnId) : null;

        return $content
            ->title('New Split Transaction')
            ->breadcrumb(
                ['text' => 'School fees', 'url' => '#'],
                ['text' => 'Split Transactions', 'url' => url('split-transactions')],
                ['text' => 'New Split']
            )
            ->body(view('admin.split-transaction.create', compact('prefillTxn', 'u', 'transactions')));
    }

    // ── Store: validate + persist + apply ────────────────────────────────────
    public function store()
    {
        $request = request();
        $u = Admin::user();

        $request->validate([
            'original_transaction_id'   => 'required|integer|min:1',
            'original_remaining_amount' => 'required|integer|min:0',
            'items'                     => 'required|array|min:1',
            'items.*.to_account_id'     => 'required|integer|min:1',
            'items.*.amount'            => 'required|integer|min:1',
        ]);

        $txnId     = (int) $request->original_transaction_id;
        $remaining = (int) $request->original_remaining_amount;
        $items     = $request->items;

        // Load and validate original transaction belongs to this enterprise
        $original = Transaction::where('enterprise_id', $u->enterprise_id)->find($txnId);
        if (!$original) {
            return back()->withErrors(['original_transaction_id' => 'Transaction not found or does not belong to your school.'])->withInput();
        }

        if ($original->amount <= 0) {
            return back()->withErrors(['original_transaction_id' => 'Only positive (payment) transactions can be split.'])->withInput();
        }

        // Check not already split-applied
        $existingSplit = SplitTransaction::where('original_transaction_id', $txnId)
            ->where('status', 'Applied')->first();
        if ($existingSplit) {
            return back()->withErrors(['original_transaction_id' => 'This transaction has already been split (Split #' . $existingSplit->id . '). Cannot split again.'])->withInput();
        }

        $totalItems = array_sum(array_column($items, 'amount'));
        if ($remaining + $totalItems !== (int) $original->amount) {
            return back()->withErrors([
                'original_remaining_amount' => 'Remaining (' . number_format($remaining) . ') + items total (' .
                    number_format($totalItems) . ') = ' . number_format($remaining + $totalItems) .
                    ', but original amount is UGX ' . number_format($original->amount) . '.',
            ])->withInput();
        }

        // Validate each target account belongs to the enterprise
        foreach ($items as $i => $item) {
            $acc = Account::where('enterprise_id', $u->enterprise_id)
                ->find($item['to_account_id']);
            if (!$acc) {
                return back()->withErrors([
                    "items.{$i}.to_account_id" => 'Account ID ' . $item['to_account_id'] . ' not found in your school.',
                ])->withInput();
            }
        }

        $splitId = null;
        try {
            DB::transaction(function () use ($u, $original, $remaining, $items, $request, &$splitId) {
                $split = SplitTransaction::create([
                    'enterprise_id'              => $u->enterprise_id,
                    'original_transaction_id'    => $original->id,
                    'original_amount'            => $original->amount,
                    'original_remaining_amount'  => $remaining,
                    'notes'                      => $request->notes,
                    'created_by_id'              => $u->id,
                    'status'                     => 'Draft',
                ]);

                foreach ($items as $item) {
                    SplitTransactionItem::create([
                        'split_transaction_id' => $split->id,
                        'to_account_id'        => (int) $item['to_account_id'],
                        'amount'               => (int) $item['amount'],
                    ]);
                }

                $split->apply();

                $splitId = $split->id;
            });
        } catch (\Exception $e) {
            admin_error('Split Failed', $e->getMessage());
            return back()->withInput();
        }

        admin_success('Split Applied', 'Transaction split #' . $splitId . ' has been applied successfully.');
        return redirect(url('split-transactions/' . $splitId));
    }
}
