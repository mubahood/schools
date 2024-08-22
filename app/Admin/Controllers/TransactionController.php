<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\TransactionChangeDueTerm;
use App\Models\Account;
use App\Models\Term;
use App\Models\TermlySchoolFeesBalancing;
use App\Models\Transaction;
use App\Models\Utils;
use Attribute;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Transaction';

    /** 
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transaction());
        $u = Admin::user();
        Transaction::where('enterprise_id', $u->enterprise_id)
            ->delete();

        //$grid->disableActions();
        $grid->export(function ($export) {

            $export->filename('Transactions');

            $export->except(['actions']);
            $export->originalValue(['description', 'type']);

            $export->column('account_id', function ($value, $original) {
                $acc = Account::find($original);
                if ($acc == null) {
                    return '-';
                }
                return $acc->name;
            });
            $export->column('is_contra_entry', function ($value, $original) {
                if ($original == 1) {
                    return 'Contra Entry';
                }
                return 'Not Contra Entry';
            });
        });



        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();
            $u = Admin::user();


            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=Account"
            );
            $filter->equal('account_id', 'Filter by account')
                ->select(function ($id) {
                    $a = Account::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);


            $terms = [];
            $active_term = 0;
            foreach (
                Term::where(
                    'enterprise_id',
                    Admin::user()->enterprise_id
                )->orderBy('id', 'desc')->get() as $key => $term
            ) {
                $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
                if ($term->is_active) {
                    $active_term = $term->id;
                }
            }
            $filter->equal('term_id', 'Fliter by term')->select($terms);


            $balancings = [];
            foreach (
                TermlySchoolFeesBalancing::where([
                    'enterprise_id' => $u->enterprise_id
                ])
                    ->orderBy('id', 'desc')
                    ->get() as $v
            ) {
                $balancings[$v->id] = 'Term ' . $v->term->name_text;
            }

            $filter->equal('termly_school_fees_balancing_id', 'Fliter by balance')->select(
                $balancings
            );

            $filter->between('payment_date', 'Created between')->date();
            /* $filter->select([
                "SCHOOL_PAY" => 'SCHOOL_PAY',
                "GENERATED" => 'GENERATED',
                "MANUAL_ENTRY" => 'MANUAL_ENTRY',
                "MOBILE_APP" => 'MOBILE_APP',
            ]); */

            //filter by source
            $filter->equal('source', 'Filter by source')
                ->select([
                    "SCHOOL_PAY" => 'SCHOOL_PAY',
                    "GENERATED" => 'GENERATED',
                    "MANUAL_ENTRY" => 'MANUAL_ENTRY',
                    "MOBILE_APP" => 'MOBILE_APP',
                ]);

            $filter->group('amount', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });

        $grid->quickSearch('description');

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new TransactionChangeDueTerm());
        });

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('payment_date', 'Desc');

        /*         $grid->column('id', __('Id'))->sortable(); */



        $grid->column('payment_date', __('Date'))->display(function () {
            return Utils::my_date_time($this->payment_date);
        })
            ->sortable()
            ->width(120);


        $grid->column('academic_year_id', __('Academic year id'))->hide();


        $grid->column('account_id', __('Account'))
            ->sortable()
            ->display(function ($x) {
                if ($this->account == null) {
                    return $x;
                }
                return
                    '<b><a class="text-primary" href="' . admin_url('students/' . $this->account->administrator_id) . '">' . $this->account->name . "</a></b>";;
            });


        $grid->column('amount', __('Amount (UGX)'))->display(function () {
            return  number_format($this->amount);
        })
            ->sortable()->totalRow(function ($x) {
                return  number_format($x);
            });



        $grid->column('description', __('Description'))->limit(80)->sortable();

        $grid->column('type', __('Type'))
            ->label([
                "FEES_PAYMENT" => 'success',
                "FEES_BILL" => 'info',
                "other" => 'warning',
            ])
            ->filter([
                "FEES_PAYMENT" => 'Fees Payment',
                "FEES_BILL" => 'FEES BILL',
                "BALANCE_BROUGHT_FORWARD" => 'BALANCE BROUGHT FORWARD',
                "BALANCE_CARRIED_DOWN" => 'BALANCE CARRIED DOWN',
                "other" => 'Other',
            ])
            ->hide()
            ->sortable();


        $grid->column('source', __('Source'))
            ->label([
                "SCHOOL_PAY" => 'success',
                "GENERATED" => 'info',
                "MANUAL_ENTRY" => 'warning',
                "MOBILE_APP" => 'warning',
            ])
            ->sortable();

        $terms = [];
        $active_term = 0;
        foreach (
            Term::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->orderBy('id', 'desc')->get() as $key => $term
        ) {
            $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            if ($term->is_active) {
                $active_term = $term->id;
            }
        }
        if (!isset($_GET['term_id'])) {
            $grid->model()->where('term_id', $active_term);
        }

        $grid->column('term_id', __('Due term'))->display(function ($x) {
            $t = Term::find($x);
            if ($t == null) {
                return $x;
            }
            return '<span style="float: right;">Term ' . $t->name_text . '</span>';
        })
            ->sortable();


        $grid->column('created_by_id', __('Created By'))->display(function () {
            if ($this->by == null) {
                return 'Deleted';
            }
            return  $this->by->name;
        })
            ->sortable();
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Transaction::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('payment_date', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('account_id', __('Account id'));
        $show->field('amount', __('Amount'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Transaction());
        $u = Admin::user();
        $accs = Account::where([
            'enterprise_id' => $u->enterprise_id,
            'type' => 'CASH_ACCOUNT'
        ])->get();

        $terms = [];
        $active_term = 0;
        foreach (
            Term::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->orderBy('id', 'desc')->get() as $key => $term
        ) {
            $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            if ($term->is_active) {
                $active_term = $term->id;
            }
        }

        $form->select('term_id', 'Due term')->options($terms)
            ->default($active_term)
            ->rules('required');

        $accs_2 = Account::where([
            'enterprise_id' => $u->enterprise_id,
            'type' => 'BANK_ACCOUNT'
        ])
            ->get();

        foreach ($accs_2 as $acc) {
            $accs[] = $acc;
        }
        $_accs = [];
        foreach ($accs as $acc) {
            $_accs[$acc->id] = $acc->name;
        }

        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('created_by_id', __('By id'))->default($u->id)->rules('required');
        $form->hidden('source', "Money deposited to")->default('MANUAL_ENTRY')
            ->readonly();


        if ($form->isCreating()) {
            $form->radio('is_debit', "Transaction type")
                ->options([
                    1 => 'Debit (+)',
                    0 => 'Credit (-)',
                ])->default(-1)
                ->rules('required');
        }

        $ajax_url = url(
            '/api/ajax?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&model=Account"
        );
        $ajax_url = trim($ajax_url);

        $form->select('account_id', "Account")
            ->options(function ($id) {
                $a = Account::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax($ajax_url)->rules('required');


        $form->decimal('amount', __('Amount'))
            ->attribute('type', 'number')
            ->rules('required|int');

        $form->date('payment_date', __('Date'))
            ->rules('required');

        $form->textarea('description', __('Description'))->rules('required');

        if ($form->isCreating()) {
            $form->divider();

            $form->select('contra_entry_account_id', "Contra-entry Account")
                ->options($_accs)
                ->help("Source/Destination of the funds.")
                ->rules('required');
        }

        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();






        return $form;
    }
}
