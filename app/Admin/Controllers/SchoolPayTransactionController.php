<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\SchoolPayTransactionImport;
use App\Admin\Actions\Post\TransactionChangeDueTerm;
use App\Models\Account;
use App\Models\SchoolPayTransaction;
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

class SchoolPayTransactionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School Pay Transactions';

    /** 
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SchoolPayTransaction());
        $grid->model()->orderBy('payment_date', 'desc');
        $grid->disableActions();
        $grid->disableCreateButton();
        $lastRec = SchoolPayTransaction::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('school_pay_transporter_id', 'Desc')->first();
        //$grid->disableActions();
        $grid->export(function ($export) {

            $export->filename('SchooPayTransactions');

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



            $filter->between('payment_date', 'Created between')->date();
            /* $filter->select([
                "SCHOOL_PAY" => 'SCHOOL_PAY',
                "GENERATED" => 'GENERATED',
                "MANUAL_ENTRY" => 'MANUAL_ENTRY',
                "MOBILE_APP" => 'MOBILE_APP',
            ]); */



            $filter->group('amount', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });

        $grid->quickSearch('description');

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new SchoolPayTransactionImport());
        });

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('school_pay_transporter_id', 'Desc');

        //add school_pay_transporter_id
        $grid->column('school_pay_transporter_id', __('ID'))->sortable()->width(120);



        $grid->column('payment_date', __('Date'))->display(function () {
            return Utils::my_date_time($this->payment_date);
        })
            ->sortable()
            ->width(200);


        $grid->column('account_id', __('Account'))
            ->sortable()
            ->display(function ($x) {
                if ($this->account == null) {
                    return $x;
                }
                $name = $this->account->name;
                if ($this->account->owner != null) {
                    $name = $this->account->owner->name_text;
                }


                return
                    '<b><a class="text-primary"  target="_blank"  href="' . admin_url('students/' . $this->account->administrator_id) . '">' . $name . "</a></b>";;
            })->width(320);


        $grid->column('amount', __('Amount (UGX)'))->display(function () {
            return  number_format($this->amount);
        })
            ->sortable()->totalRow(function ($x) {
                return  number_format($x);
            })->width(120);




        $grid->column('status', __('Status'))
            ->label([
                "Imported" => 'success',
                "Not Imported" => 'warning',
                "Error" => 'danger',
            ])
            ->filter([
                "Imported" => 'Imported',
                "Not Imported" => 'Not Imported',
                "Error" => 'Error',
            ])
            ->sortable()->width(100);

        //actions
        $grid->column('actions', __('Actions'))->display(function () {
            //if status is imported, do not show any action
            if ($this->status == 'Imported') {
                return 'Already Imported';
            }
            $text = 'Import Now';
            $link = url('import-transaction?trans_id=' . $this->id);
            //open in new tab
            return "<a href='$link' target='_blank' class='btn btn-xs btn-success'>$text</a>";
        });


        $grid->column('account.balance', __('Fee Balance'))
            ->display(function ($x) {
                return number_format($x);
            })->width(120);

        //description
        $grid->column('description', __('Description'))->limit(100)
            ->sortable()
            ->hide();


        $grid->column('schoolpayReceiptNumber', __('Schoolpay Receipt Number'))->sortable();
        $grid->column('studentRegistrationNumber', __('Student Registration Number'))->sortable();
        $grid->column('paymentDateAndTime', __('Payment Date and Time'))->hide();
        $grid->column('settlementBankCode', __('Settlement Bank Code'))->hide();
        $grid->column('sourceChannelTransDetail', __('Source Channel Transaction Detail'))->sortable();
        $grid->column('sourceChannelTransactionId', __('Source Channel Transaction ID'))->hide();
        $grid->column('sourcePaymentChannel', __('Source Payment Channel'))->hide();
        $grid->column('studentClass', __('Student Class'))->sortable();
        $grid->column('studentName', __('Student Name'))
            ->display(function ($studentName) {
                if ($studentName == null || $studentName == '') {
                    if ($this->data != null) {
                        if (strlen($this->data) > 100) {
                            $tran = $this;
                            try {
                                $v = json_decode($this->data);
                            } catch (\Exception $e) {
                                $v = null;
                            }
                            if ($v == null) {
                                return 'N/A';
                            }
                            if (isset($v->schoolpayReceiptNumber)) {
                                if ($v->schoolpayReceiptNumber != null) {
                                    $tran->schoolpayReceiptNumber = $v->schoolpayReceiptNumber;
                                }
                            }

                            if (isset($v->studentRegistrationNumber)) {
                                if ($v->studentRegistrationNumber != null) {
                                    $tran->studentRegistrationNumber = $v->studentRegistrationNumber;
                                }
                            }
                            //paymentDateAndTime
                            if (isset($v->paymentDateAndTime)) {
                                if ($v->paymentDateAndTime != null) {
                                    $tran->paymentDateAndTime = $v->paymentDateAndTime;
                                }
                            }

                            //settlementBankCode
                            if (isset($v->settlementBankCode)) {
                                if ($v->settlementBankCode != null) {
                                    $tran->settlementBankCode = $v->settlementBankCode;
                                }
                            }
                            //sourceChannelTransDetail
                            if (isset($v->sourceChannelTransDetail)) {
                                if ($v->sourceChannelTransDetail != null) {
                                    $tran->sourceChannelTransDetail = $v->sourceChannelTransDetail;
                                }
                            }

                            //sourceChannelTransactionId
                            if (isset($v->sourceChannelTransactionId)) {
                                if ($v->sourceChannelTransactionId != null) {
                                    $tran->sourceChannelTransactionId = $v->sourceChannelTransactionId;
                                }
                            }

                            //sourcePaymentChannel
                            if (isset($v->sourcePaymentChannel)) {
                                if ($v->sourcePaymentChannel != null) {
                                    $tran->sourcePaymentChannel = $v->sourcePaymentChannel;
                                }
                            }

                            //studentClass
                            if (isset($v->studentClass)) {
                                if ($v->studentClass != null) {
                                    $tran->studentClass = $v->studentClass;
                                }
                            }

                            //studentName
                            if (isset($v->studentName)) {
                                if ($v->studentName != null) {
                                    $tran->studentName = $v->studentName;
                                }
                            }

                            //studentPaymentCode
                            if (isset($v->studentPaymentCode)) {
                                if ($v->studentPaymentCode != null) {
                                    $tran->studentPaymentCode = $v->studentPaymentCode;
                                }
                            }

                            //transactionCompletionStatus
                            if (isset($v->transactionCompletionStatus)) {
                                if ($v->transactionCompletionStatus != null) {
                                    $tran->transactionCompletionStatus = $v->transactionCompletionStatus;
                                } 
                            }
                            $tran->save();
                            return $tran->studentName;
                        }
                    }
                    return 'N/A';
                }
                return $studentName;
            })->width(200)
            ->sortable();
        $grid->column('studentPaymentCode', __('Student Payment Code'))->sortable();
        $grid->column('data', __('DATA'))->sortable()->hide();
        $grid->column('transactionCompletionStatus', __('Transaction Completion Status'))->hide();
        //error_alert
        $grid->column('error_alert', __('Error Alert'))
            ->display(function ($error_alert) {
                if ($error_alert != null) {
                    return "<span class='text-danger'>Error: " . $this->description . "</span>";
                }
                return 'N/A';
            })->sortable();

        //add per page on $grid
        $grid->perPages([10, 20, 50, 100, 500, 1000]);

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
        return 'No form for this model';
        $form = new Form(new SchoolPayTransaction());
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

        $form->datetime('payment_date', __('Date'))
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
