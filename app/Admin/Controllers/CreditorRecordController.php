<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\AccountParent;
use App\Models\CreditorRecord;
use App\Models\FinancialRecord;
use App\Models\Term;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreditorRecordController extends AdminController
{
    protected $title = 'Creditors';

    protected function grid()
    {
        $grid = new Grid(new CreditorRecord());

        $u = Admin::user();
        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('status', 'asc')    // Overdue first, then Pending, Partial, Paid
            ->orderBy('due_date', 'asc')  // Earliest due first within status
            ->orderBy('id', 'desc');

        $grid->disableBatchActions();

        $grid->export(function ($export) {
            $export->filename('Creditors');
            $export->except(['quick_links']);
        });

        $grid->quickSearch('description')->placeholder('Search by description...');

        // Filters
        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();

            $suppliers = [];
            foreach (
                Administrator::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'supplier'])
                    ->orderBy('name')->get() as $s
            ) {
                $suppliers[$s->id] = $s->name;
            }

            $filterTerms = [];
            foreach (
                Term::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('id', 'desc')->get() as $t
            ) {
                $filterTerms[$t->id] = 'Term ' . $t->name . ' - ' . $t->academic_year->name;
            }

            $filter->equal('supplier_id', 'Supplier')->select($suppliers);
            $filter->equal('status', 'Status')->select([
                'Pending'  => 'Pending',
                'Partial'  => 'Partially Paid',
                'Overdue'  => 'Overdue',
                'Paid'     => 'Fully Paid',
            ]);
            $filter->equal('term_id', 'Term')->select($filterTerms);
            $filter->between('due_date', 'Due Date')->date();
            $filter->group('balance', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });

        // Status refresh: mark as overdue if due_date has passed and not paid
        // (lightweight — only on grid load for unpaid records)
        CreditorRecord::where('enterprise_id', $u->enterprise_id)
            ->whereIn('status', ['Pending', 'Partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'Overdue']);

        // ── Columns ──

        $grid->column('supplier_id', 'Supplier')->display(function ($x) {
            if (!$x) return '<span class="text-muted">—</span>';
            $s = $this->supplier;
            if (!$s) return $x;
            $phone = $s->phone_number_1 ?? '';
            return e($s->name) . ($phone ? '<br><small class="text-muted">' . e($phone) . '</small>' : '');
        })->sortable();

        $grid->column('description', 'Item / Description')->display(function ($x) {
            $source = '';
            if ($this->financial_record_id) {
                $url = admin_url('financial-records-expenditure/' . $this->financial_record_id . '/edit');
                $source = " <a href='$url' class='label label-default' target='_blank'><i class='fa fa-link'></i> Expenditure</a>";
            }
            return '<span title="' . e($x) . '">' . Str::limit($x, 50) . '</span>' . $source;
        });

        $grid->column('original_amount', 'Total Credit (UGX)')
            ->display(fn($x) => 'UGX ' . number_format($x))
            ->sortable()
            ->totalRow(fn($x) => '<strong>UGX ' . number_format($x) . '</strong>');

        $grid->column('paid_amount', 'Paid (UGX)')
            ->display(fn($x) => $x > 0 ? '<span class="text-success">UGX ' . number_format($x) . '</span>' : '<span class="text-muted">—</span>')
            ->sortable()
            ->totalRow(fn($x) => '<strong class="text-success">UGX ' . number_format($x) . '</strong>');

        $grid->column('balance', 'Outstanding (UGX)')
            ->display(function ($x) {
                if ($x <= 0) return '<span class="label label-success">Cleared</span>';
                return '<strong class="text-danger">UGX ' . number_format($x) . '</strong>';
            })
            ->sortable()
            ->totalRow(fn($x) => '<strong class="text-danger">UGX ' . number_format($x) . '</strong>');

        $grid->column('status', 'Status')->display(function ($x) {
            $map = [
                'Pending'  => 'label-warning',
                'Partial'  => 'label-info',
                'Overdue'  => 'label-danger',
                'Paid'     => 'label-success',
            ];
            $cls = $map[$x] ?? 'label-default';
            return "<span class='label $cls'>$x</span>";
        })->sortable();

        $grid->column('due_date', 'Due Date')->display(function ($x) {
            if (!$x) return '<span class="text-muted">—</span>';
            $date = Carbon::parse($x);
            $label = $date->isPast() && $this->status !== 'Paid'
                ? 'text-danger'
                : 'text-muted';
            return '<span class="' . $label . '">' . $date->format('d M Y') . '</span>';
        })->sortable();

        $grid->column('term_id', 'Term')->display(fn($x) => $this->term ? $this->term->name_text : '—')->hide();

        $grid->column('quick_links', 'Actions')->display(function () {
            $payUrl  = admin_url('creditor-payments/create?creditor_record_id=' . $this->id);
            $listUrl = admin_url('creditor-payments?creditor_record_id=' . $this->id);
            $links   = "<a href='$payUrl' class='btn btn-xs btn-success' title='Record a payment'>"
                . "<i class='fa fa-money'></i> Pay</a> "
                . "<a href='$listUrl' class='btn btn-xs btn-info' title='View all payments'>"
                . "<i class='fa fa-list'></i> Payments</a>";
            return $links;
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(CreditorRecord::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('description', 'Description');
        $show->field('original_amount', 'Total Credit (UGX)')->as(fn($v) => 'UGX ' . number_format($v));
        $show->field('paid_amount', 'Paid (UGX)')->as(fn($v) => 'UGX ' . number_format($v));
        $show->field('balance', 'Outstanding (UGX)')->as(fn($v) => 'UGX ' . number_format($v));
        $show->field('status', 'Status');
        $show->field('due_date', 'Due Date');
        $show->field('payment_method', 'Expected Payment Method');
        $show->field('notes', 'Notes');
        $show->field('created_at', 'Created');

        $show->panel()->tools(function ($tools) {
            $tools->disableDelete();
        });

        return $show;
    }

    protected function form()
    {
        $form = new Form(new CreditorRecord());
        $u = Auth::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id);

        // Read-only summary section when editing
        if ($form->isEditing()) {
            $params = request()->route()->parameters();
            $id = $params['creditor_record'] ?? null;
            if ($id) {
                $record = CreditorRecord::find($id);
                if ($record) {
                    $expUrl = $record->financial_record_id
                        ? admin_url('financial-records-expenditure/' . $record->financial_record_id . '/edit')
                        : null;

                    $html = "<table class='table table-bordered' style='background:#f9f9f9'>"
                        . "<tr><th>Total Credit</th><td>UGX " . number_format($record->original_amount) . "</td>"
                        . "<th>Paid</th><td>UGX " . number_format($record->paid_amount) . "</td>"
                        . "<th>Balance</th><td><strong>UGX " . number_format($record->balance) . "</strong></td></tr>"
                        . "<tr><th>Status</th><td colspan='5'><span class='label label-warning'>"
                        . e($record->status) . "</span></td></tr>";

                    if ($expUrl) {
                        $html .= "<tr><td colspan='6'><a href='$expUrl' class='btn btn-xs btn-default'>"
                            . "<i class='fa fa-link'></i> View Source Expenditure</a></td></tr>";
                    }
                    $html .= "</table>";

                    $form->html($html, 'Credit Summary');
                }
            }
        }

        $form->divider('Creditor Details');

        // Supplier
        $suppliers = Administrator::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'supplier',
        ])->orderBy('name')->get()->pluck('name', 'id')->prepend('— No supplier —', '');
        $form->select('supplier_id', 'Supplier')->options($suppliers);

        $form->text('description', 'Description / What was purchased');

        $form->date('due_date', 'Payment Due Date');

        $form->select('payment_method', 'Expected Payment Method')->options([
            'Cash' => 'Cash',
            'Bank Transfer' => 'Bank Transfer',
            'Mobile Money' => 'Mobile Money',
            'Cheque' => 'Cheque',
            'Other' => 'Other',
        ]);

        $form->textarea('notes', 'Notes / Remarks');

        // Only allow editing original_amount on new manually-created records
        if ($form->isCreating()) {
            $form->decimal('original_amount', 'Credit Amount (UGX)')->rules('required|min:1');
            $form->hidden('paid_amount')->default(0);
            $form->hidden('balance')->default(0);
            $form->hidden('status')->default('Pending');
            $form->hidden('created_by_id')->default($u->id);

            // Term select
            $term = $u->ent->active_term();
            $form->select('term_id', 'Term')
                ->options(
                    Term::where('enterprise_id', $u->enterprise_id)
                        ->orderBy('id', 'desc')->get()->pluck('name_text', 'id')
                )
                ->default($term ? $term->id : null)
                ->rules('required');

            $form->saving(function ($f) {
                $f->balance = $f->original_amount;
            });
        }

        $form->disableViewCheck();

        return $form;
    }
}
