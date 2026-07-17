<?php

namespace App\Admin\Controllers;

use App\Models\CreditorPayment;
use App\Models\CreditorRecord;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreditorPaymentController extends AdminController
{
    protected $title = 'Creditor Payments';

    protected function grid()
    {
        $grid = new Grid(new CreditorPayment());
        $u = Admin::user();

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc');

        // If coming from a creditor record page, pre-filter
        if (request('creditor_record_id')) {
            $grid->model()->where('creditor_record_id', request('creditor_record_id'));

            // Show a back-link to the creditor record
            $grid->header(function () {
                $id  = request('creditor_record_id');
                $url = admin_url('creditor-records/' . $id . '/edit');
                return "<a href='$url' class='btn btn-sm btn-default'>"
                    . "<i class='fa fa-arrow-left'></i> Back to Creditor Record</a>";
            });
        }

        $grid->disableBatchActions();

        $grid->export(function ($export) {
            $export->filename('Creditor-Payments');
            $export->except(['actions']);
        });

        $grid->quickSearch('notes')->placeholder('Search by notes or reference...');

        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();

            $creditors = [];
            foreach (
                CreditorRecord::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('description')->get() as $cr
            ) {
                $creditors[$cr->id] = Str::limit($cr->description ?? 'Record #' . $cr->id, 60)
                    . ' (' . ($cr->supplier ? $cr->supplier->name : '—') . ')';
            }

            $filter->equal('creditor_record_id', 'Creditor Record')->select($creditors);
            $filter->equal('payment_method', 'Payment Method')->select([
                'Cash' => 'Cash',
                'Bank Transfer' => 'Bank Transfer',
                'Mobile Money' => 'Mobile Money',
                'Cheque' => 'Cheque',
            ]);
            $filter->between('payment_date', 'Payment Date')->date();
            $filter->group('amount_paid', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });

        // Columns
        $grid->column('payment_date', 'Date')
            ->display(fn($x) => Utils::my_date($x))
            ->sortable();

        $grid->column('creditor_record_id', 'Creditor / Item')->display(function ($x) {
            $cr = $this->creditor_record;
            if (!$cr) return $x;
            $supplier = $cr->supplier ? e($cr->supplier->name) : '—';
            $desc     = Str::limit($cr->description ?? '', 40);
            $url      = admin_url('creditor-records/' . $x . '/edit');
            return "<a href='$url'>$supplier</a><br><small class='text-muted'>$desc</small>";
        })->sortable();

        $grid->column('amount_paid', 'Amount Paid (UGX)')
            ->display(fn($x) => '<strong class="text-success">UGX ' . number_format($x) . '</strong>')
            ->sortable()
            ->totalRow(fn($x) => '<strong>UGX ' . number_format($x) . '</strong>');

        $grid->column('payment_method', 'Method')
            ->display(fn($x) => $x ?: '<span class="text-muted">—</span>')
            ->sortable();

        $grid->column('reference', 'Reference')
            ->display(fn($x) => $x ?: '<span class="text-muted">—</span>');

        $grid->column('notes', 'Notes')
            ->display(fn($x) => $x ? Str::limit($x, 50) : '<span class="text-muted">—</span>');

        $grid->column('created_by_id', 'Recorded By')
            ->display(fn($x) => $this->created_by ? $this->created_by->name : '—')
            ->hide();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(CreditorPayment::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('payment_date', 'Payment Date');
        $show->field('amount_paid', 'Amount Paid')->as(fn($v) => 'UGX ' . number_format($v));
        $show->field('payment_method', 'Payment Method');
        $show->field('reference', 'Reference / Receipt No.');
        $show->field('notes', 'Notes');
        $show->field('created_at', 'Recorded At');
        return $show;
    }

    protected function form()
    {
        $form = new Form(new CreditorPayment());
        $u = Auth::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->hidden('created_by_id')->default($u->id);

        // Build creditor record list (outstanding only, unless editing)
        $query = CreditorRecord::where('enterprise_id', $u->enterprise_id);
        if ($form->isCreating()) {
            $query->whereIn('status', ['Pending', 'Partial', 'Overdue']);
        }

        $creditors = [];
        foreach ($query->orderBy('status')->orderBy('due_date')->get() as $cr) {
            $label = ($cr->supplier ? $cr->supplier->name . ' — ' : '')
                . Str::limit($cr->description ?? 'Record #' . $cr->id, 50)
                . ' [Balance: UGX ' . number_format($cr->balance) . ']';
            $creditors[$cr->id] = $label;
        }

        // Pre-select if coming from the creditor record page
        $preselect = request('creditor_record_id');
        $form->select('creditor_record_id', 'Creditor Record')
            ->options($creditors)
            ->default($preselect ?: null)
            ->rules('required');

        // Show current balance hint when a creditor record is pre-selected
        if ($preselect) {
            $cr = CreditorRecord::find($preselect);
            if ($cr) {
                $form->html(
                    "<div class='alert alert-warning'>"
                    . "<strong>Outstanding balance:</strong> UGX " . number_format($cr->balance)
                    . " &nbsp;|&nbsp; Status: <strong>" . $cr->status . "</strong>"
                    . ($cr->due_date ? " &nbsp;|&nbsp; Due: <strong>" . $cr->due_date . "</strong>" : '')
                    . "</div>",
                    'Balance Info'
                );
            }
        }

        $form->decimal('amount_paid', 'Amount Being Paid (UGX)')->rules('required|min:1');
        $form->date('payment_date', 'Payment Date')->default(date('Y-m-d'))->rules('required');

        $form->select('payment_method', 'Payment Method')->options([
            'Cash'          => 'Cash',
            'Bank Transfer' => 'Bank Transfer',
            'Mobile Money'  => 'Mobile Money',
            'Cheque'        => 'Cheque',
            'Other'         => 'Other',
        ]);

        $form->text('reference', 'Receipt / Reference Number');
        $form->textarea('notes', 'Notes');

        $form->disableViewCheck();

        return $form;
    }
}
