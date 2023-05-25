<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\Term;
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


            $filter->equal('term_id', 'Fliter by term')->select(Term::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()
                ->pluck('name_text', 'id'));
            $filter->between('payment_date', 'Created between')->date();
            
            $filter->group('amount', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });

        });

        //$grid->disableBatchActions();

        $grid->quickSearch('description');


        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');

/*         $grid->column('id', __('Id'))->sortable(); */

        $grid->column('term_id', __('Term'))->display(function () {
            if ($this->term == null) {
            }
            return 'Term ' . $this->term->name_text;
        })
            ->sortable();

        $grid->column('payment_date', __('Date'))->display(function () {
            return Utils::my_date_time($this->payment_date);
        })
            ->sortable();


        $grid->column('academic_year_id', __('Academic year id'))->hide();


        $grid->column('account_id', __('Account'))
            ->sortable()
            ->display(function ($x) {
                if($this->account == null){
                    return $x;
                }
                return
                    '<a class="text-dark" href="' . admin_url('students/' . $this->account->administrator_id) . '">' . $this->account->name . "</a>";;
            });




        $grid->column('amount', __('Amount (UGX)'))->display(function () {
            return  number_format($this->amount);
        })
            ->sortable()->totalRow(function ($x) {
                return  number_format($x);
            });

 

        $grid->column('description', __('Description'))->display(function ($x) {
            return '<spap title="' . $x . '" >' . Str::limit($x, 40, '...') . '</span>';
        });

        $grid->column('type', __('Transaction Type'))
            ->label([
                "FEES_PAYMENT" => 'success',
                "FEES_BILL" => 'info',
                "other" => 'warning',
            ])
            ->filter([
                "FEES_PAYMENT" => 'Fees Payment',
                "FEES_BILL" => 'FEES BILL',
                "other" => 'Other',
            ])
            ->sortable();


        $grid->column('created_by_id', __('Created By'))->display(function () {
            if ($this->by == null) {
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
