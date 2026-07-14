<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\AccountParent;
use App\Models\FinancialRecord;
use App\Models\Term;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FinancialExpenditureRecordController extends AdminController
{
    protected $title = 'Expenditure Records';

    protected function grid()
    {
        $grid = new Grid(new FinancialRecord());

        $grid->export(function ($export) {
            $export->filename('Expenditure-Records');
            $export->except(['actions']);
            $export->originalValue(['description', 'type']);
        });

        $grid->disableBatchActions();

        $terms = [];
        $active_term = 0;
        foreach (
            Term::with('academic_year')->where('enterprise_id', Admin::user()->enterprise_id)
                ->orderBy('id', 'desc')
                ->get() as $term
        ) {
            $terms[$term->id] = 'Term ' . $term->name . ' - ' . ($term->academic_year ? $term->academic_year->name : '');
            if ($term->is_active) {
                $active_term = $term->id;
            }
        }

        if (!isset($_GET['term_id'])) {
            $grid->model()->where('term_id', $active_term);
        }

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();

            $accs = [];
            foreach (
                Account::where(['enterprise_id' => $u->enterprise_id, 'type' => 'OTHER_ACCOUNT'])->get() as $val
            ) {
                if ($val->account_parent_id == null) continue;
                $accs[$val->id] = $val->getName();
            }

            $parents = [];
            foreach (
                AccountParent::where(['enterprise_id' => $u->enterprise_id])
                    ->orderBy('name', 'asc')->get() as $v
            ) {
                $parents[$v->id] = $v->name;
            }

            $suppliers = [];
            foreach (
                Administrator::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'supplier'])
                    ->orderBy('name', 'asc')->get() as $s
            ) {
                $suppliers[$s->id] = $s->name;
            }

            $filterTerms = [];
            foreach (
                Term::with('academic_year')->where('enterprise_id', $u->enterprise_id)
                    ->orderBy('id', 'desc')->get() as $t
            ) {
                $filterTerms[$t->id] = 'Term ' . $t->name . ' - ' . ($t->academic_year ? $t->academic_year->name : '');
            }

            $filter->equal('parent_account_id', 'Filter by Vote')->select($parents);
            $filter->equal('account_id', 'Filter by Account')->select($accs);
            $filter->equal('supplier_id', 'Filter by Supplier')->select($suppliers);
            $filter->equal('term_id', 'Filter by Term')->select($filterTerms);
            $filter->between('payment_date', 'Date range')->date();
            $filter->group('amount', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });

        $grid->quickSearch('description')->placeholder('Search by particulars...');

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'type' => 'EXPENDITURE',
        ])->with(['account', 'par', 'term', 'created_by', 'supplier'])->orderBy('id', 'DESC');

        $grid->column('payment_date', 'Date')
            ->display(fn($x) => Utils::my_date($x))
            ->sortable();

        $grid->column('description', 'Particulars')
            ->display(fn($x) => '<span title="' . e($x) . '">' . Str::limit($x, 40) . '</span>');

        $grid->column('quantity', 'Qty')
            ->display(fn($x) => number_format($x));

        $grid->column('unit_price', 'Unit Price (UGX)')
            ->display(fn($x) => number_format($x));

        $grid->column('amount', 'Total (UGX)')
            ->display(fn($x) => '<strong>' . number_format(abs($x)) . '</strong>')
            ->sortable()
            ->totalRow(fn($x) => '<strong>' . number_format(abs($x)) . '</strong>');

        $grid->column('account_id', 'Account')
            ->display(function ($x) {
                if (!$this->account) return $x;
                $url = admin_url('financial-records-expenditure?account_id=' . $x);
                return '<a href="' . $url . '">' . e($this->account->name) . '</a>';
            })->sortable();

        $grid->column('parent_account_id', 'Vote')
            ->display(function ($x) {
                if (!$this->par) return '-';
                $url = admin_url('financial-records-expenditure?parent_account_id=' . $x);
                return '<a href="' . $url . '">' . e($this->par->name) . '</a>';
            })->sortable();

        $grid->column('supplier_id', 'Supplier')
            ->display(function ($x) {
                if (!$x) return '<span class="text-muted">—</span>';
                $supplier = $this->supplier;
                if (!$supplier) return $x;
                $url = admin_url('financial-records-expenditure?supplier_id=' . $x);
                return '<a href="' . $url . '">' . e($supplier->name) . '</a>';
            })->sortable();

        $grid->column('term_id', 'Term')
            ->display(fn($x) => $this->term ? $this->term->name_text : $x)
            ->sortable();

        $grid->column('payment_method', 'Payment Method')
            ->display(fn($x) => $x ?: '<span class="text-muted">—</span>');

        $grid->column('created_by_id', 'Created By')
            ->display(fn($x) => $this->created_by ? $this->created_by->name : $x)
            ->hide();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(FinancialRecord::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('payment_date', 'Date');
        $show->field('description', 'Particulars');
        $show->field('quantity', 'Quantity');
        $show->field('unit_price', 'Unit Price');
        $show->field('amount', 'Total Amount');
        $show->field('account_id', 'Account ID');
        $show->field('parent_account_id', 'Vote/Department ID');
        $show->field('supplier_id', 'Supplier ID');
        $show->field('payment_method', 'Payment Method');
        $show->field('term_id', 'Term ID');
        $show->field('created_by_id', 'Created By');
        $show->field('created_at', 'Created At');
        return $show;
    }

    protected function form()
    {
        $form = new Form(new FinancialRecord());
        $u = Auth::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id)->rules('required');
        if ($form->isCreating()) {
            $form->hidden('created_by_id')->default($u->id)->rules('required');
        }

        $form->text('type', 'Record Type')->value('EXPENDITURE')->readonly()->rules('required');

        $term = $u->ent->active_term();
        $form->select('term_id', 'Term')
            ->options(
                Term::where(['enterprise_id' => $u->enterprise_id])
                    ->orderBy('id', 'desc')->get()->pluck('name_text', 'id')
            )
            ->default($term ? $term->id : null)
            ->rules('required');

        $form->datetime('payment_date', 'Expenditure Date')->default(date('Y-m-d'))->rules('required');
        $form->divider();

        $accs = [];
        foreach (
            Account::where(['enterprise_id' => $u->enterprise_id, 'type' => 'OTHER_ACCOUNT'])->get() as $val
        ) {
            if ($val->account_parent_id == null) continue;
            $accs[$val->id] = $val->getName();
        }

        $form->select('account_id', 'Account')->options($accs)->rules('required');

        $suppliers = Administrator::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'supplier',
        ])->orderBy('name', 'asc')->get()->pluck('name', 'id')->prepend('— No Supplier —', '');

        $form->select('supplier_id', 'Supplier (optional)')->options($suppliers);

        $form->select('payment_method', 'Payment Method')->options([
            'Cash' => 'Cash',
            'Bank Transfer' => 'Bank Transfer',
            'Mobile Money' => 'Mobile Money',
            'Cheque' => 'Cheque',
            'Other' => 'Other',
        ]);

        $form->decimal('quantity', 'Quantity')->default(1)->rules('required');
        $form->decimal('unit_price', 'Unit Price (UGX)')->rules('required');
        $form->text('description', 'Particulars / Description');

        $form->divider('Credit Settings');
        $form->radio('is_credit', 'Was this bought on credit?')
            ->options(['No' => 'No — fully paid', 'Yes' => 'Yes — part or all on credit'])
            ->default('No')
            ->when('Yes', function ($f) {
                $f->decimal('credit_amount', 'Credit Amount (UGX)')
                    ->help('Enter the portion of the total that was NOT paid yet (owed to the supplier).')
                    ->rules('required|min:1');
            });

        // Show link to creditor record when editing an existing record that has one
        if ($form->isEditing()) {
            $params = request()->route()->parameters();
            $recordId = $params['financial_records_expenditure']
                ?? $params['financial-records-expenditure']
                ?? null;
            if ($recordId) {
                $creditor = \App\Models\CreditorRecord::where('financial_record_id', $recordId)->first();
                if ($creditor) {
                    $url = admin_url('creditor-records/' . $creditor->id . '/edit');
                    $form->html(
                        "<div class='alert alert-info'>"
                        . "<i class='fa fa-credit-card'></i> "
                        . "<strong>Credit record exists:</strong> Balance UGX "
                        . number_format($creditor->balance)
                        . " &nbsp; <a href='$url' class='btn btn-xs btn-warning' target='_blank'>Manage Creditor Record</a>"
                        . "</div>",
                        'Credit Status'
                    );
                }
            }
        }

        $form->disableViewCheck();

        return $form;
    }
}
