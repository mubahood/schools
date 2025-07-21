<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\TransactionChangeDueTerm;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\StudentHasFee;
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
    protected $title = 'Transactions';

    /** 
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transaction());

        $grid->tools(function ($tools) {
            $u = Admin::user();
            $url = url(
                'api/school-pay-reconcile?ent_id=' . $u->enterprise_id
            );
            $tools->append('<a 
            target="_blank"
            href="' . $url . '" class="btn btn-sm btn-primary" style="margin-right:10px;"><i class="fa fa-refresh"></i> Sync School Pay Transactions Now</a>');
        });

        /*  $u = Admin::user();
        StudentHasFee::where('enterprise_id', $u->enterprise_id)
            ->delete(); */

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
                    "MANUAL_ENTRY" => 'MANUAL_ENTRY (cash)',
                    "PEG_PAY" => 'PEG_PAY',
                    "BANK" => 'BANK',
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
            ->orderBy('id', 'Desc');

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
                "BANK" => 'info',
                "MANUAL_ENTRY" => 'warning',
                "MOBILE_APP" => 'warning',
                "MANUAL_ENTRY" => 'primary',
                "PEG_PAY" => 'warning',

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

        $grid->column('print_receipt', 'Print Receipt')->display(function () {
            $url = url('/print-receipt?id=' . $this->id);
            return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-info">
            <i class="fa fa-print"></i> Print Receipt
            </a>';
        })->width(120);
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
        $show->field('payment_date', __('Payment Date'))->as(function ($date) {
            return \App\Models\Utils::my_date_time($date);
        });
        $show->field('updated_at', __('Updated At'))->as(function ($date) {
            return \App\Models\Utils::my_date_time($date);
        });
        $show->field('account_id', __('Account'))->as(function ($id) {
            $acc = \App\Models\Account::find($id);
            return $acc ? $acc->name : $id;
        });
        $show->field('amount', __('Amount'))->as(function ($amount) {
            return number_format($amount);
        });
        $show->field('description', __('Description'));
        $show->field('type', __('Type'));
        $show->field('source', __('Source'));
        $show->field('term_id', __('Due Term'))->as(function ($id) {
            $term = \App\Models\Term::find($id);
            return $term ? $term->name_text : $id;
        });

        $show->field('created_by_id', __('Created By'))->as(function ($id) {
            $user = \Encore\Admin\Auth\Database\Administrator::find($id);
            return $user ? $user->name : $id;
        });
        $show->field('is_debit', __('Transaction Type'))->as(function ($val) {
            return $val == 1 ? 'Debit (+)' : 'Credit (-)';
        });

        $show->field('cash_receipt_number', __('Cash Receipt Number'));
        $show->field('school_pay_transporter_id', __('School Pay Receipt Number'));
        $show->field('bank_account_id', __('Bank Account'))->as(function ($id) {
            $bank = \App\Models\BankAccount::find($id);
            return $bank ? $bank->name : $id;
        });
        $show->field('bank_transaction_number', __('Bank Transaction Number'));
        $show->field('peg_pay_transaction_number', __('Peg Pay Transaction Number'));
        $show->field('receipt_photo', __('Receipt Photo'))->image();
        $show->field('platform', __('Platform'));

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

        $form->select('account_id', "Student Account")
            ->options(function ($id) {
                $a = Account::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax(url(
                '/api/studentsFinancialAccounts?'
                    . 'user_id=' . $u->id
            ))->rules('required');


        $form->decimal('amount', __('Amount'))
            ->attribute('type', 'number')
            ->rules('required|int');

        $form->datetime('payment_date', __('Date'))
            ->rules('required');



        $form->radio('source', "Money deposited to")
            ->options([
                "SCHOOL_PAY" => 'School Pay',
                "PEG_PAY" => 'Peg Pay',
                "BANK" => 'Bank',
                "MANUAL_ENTRY" => 'Manual Entry (Cash)',
            ])
            ->when('MANUAL_ENTRY', function (Form $form) {
                $form->text('cash_receipt_number', 'Cash Receipt - number')
                    ->rules('required');
            })
            ->when('SCHOOL_PAY', function (Form $form) {
                $form->text('school_pay_transporter_id', 'School Pay Receipt Number')
                    ->rules('required|numeric');
            })
            ->when('BANK', function (Form $form) {
                $u = Admin::user();
                $bank_drop = [];
                $banks = BankAccount::where([
                    'enterprise_id' => $u->enterprise_id
                ])->get();
                foreach ($banks as $bank) {
                    $bank_drop[$bank->id] = $bank->name . " - " . $bank->account_number;
                }
                $form->select('bank_account_id', 'Bank Account')
                    ->options($bank_drop)
                    ->rules('required')->rules('required');
                $form->text('bank_transaction_number', __('Bank Transaction number'))->rules('required');
            })
            ->when('PEG_PAY', function (Form $form) {
                $form->text('peg_pay_transaction_number', __('Peg pay transaction-number'))->rules('required');
            })->rules('required')
            ->required();
        $form->divider();
        $form->file('receipt_photo', __('Receipt Photo'))->uniqueName()
            ->help('Upload a receipt photo if available.');

        $form->hidden('platform', __('platform'))->default('WEB')
            ->rules('required');

        // $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        $form->hidden('is_contra_entry', __('is_contra_entry'))->default(0)->rules('required');
        // $form->disableEditingCheck();
        $form->text('description', __('Description'));

        //particulars
        $form->text('particulars', __('Particulars'))
            ->help('Enter any additional details or particulars for this transaction.');






        return $form;
    }
}
