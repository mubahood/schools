<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\Term;
use App\Models\Transaction;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class SchoolFeesPaymentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School fees - payment';

    /**
     * Make a grid builder.
     * 
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transaction());

        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            $u = Admin::user();

            $filter->equal('account_id', 'Student')
                ->select(function ($id) {
                    $a = Account::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax(url(
                    '/api/studentsFinancialAccounts?'
                        . 'user_id=' . $u->id
                ));




            $filter->group('amount', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
            $filter->between('payment_date', 'Date between')->datetime();
        });


        $grid->disableBatchActions();
        $grid->disableActions();
        $grid->quickSearch('school_pay_transporter_id', 'description')
            ->placeholder('Search by school pay ID or description');
        $grid->model()
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'type' => 'FEES_PAYMENT',
            ])
            ->orderBy('id', 'DESC');

        $grid->column('id', __('ID'))->sortable()->hide();



        $grid->column('payment_date', __('Created'))->display(function () {
            return Utils::my_date_time($this->payment_date);
        })
            ->sortable();


        $grid->column('account_id', __('Student Account'))->display(function ($x) {
            if ($this->account == null) {
                return $x;
            }
            return $this->account->name;
        })->sortable();

        $grid->column('amount', __('Amount (UGX)'))->display(function () {
            return "" . number_format($this->amount);
        })->sortable()->totalRow(function ($x) {
            return  number_format($x);
        });




        $grid->column('description', __('Description'))->display(function ($x) {
            return '<spap title="' . $x . '" >' . Str::limit($x, 40, '...') . '</span>';
        });

        $grid->column('source', __('Source'))
            ->label([
                "SCHOOL_PAY" => 'success',
                "GENERATED" => 'info',
                "MANUAL_ENTRY" => 'warning',
            ])
            ->filter([
                "SCHOOL_PAY" => 'SCHOOL_PAY',
                "GENERATED" => 'GENERATED',
                "MANUAL_ENTRY" => 'MANUAL_ENTRY',
            ])
            ->sortable();

        $grid->column('academic_year_id', __('Academic year id'))->hide();
        $grid->column('term_id', __('Term id'))->hide();
        //school_pay_transporter_id
        $grid->column('school_pay_transporter_id', __('School Pay ID'))
            ->display(function ($x) {
                if ($this->source != 'SCHOOL_PAY') {
                    return "N/A";
                }
                return $x;
            })->sortable();


        $terms = [];
        $active_term = 0;
        foreach (Term::where(
            'enterprise_id',
            Admin::user()->enterprise_id
        )->orderBy('id', 'desc')->get() as $key => $term) {
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
            ->sortable()
            ->filter($terms);



        $grid->column('documents', __('Print'))
            ->display(function () {
                $admission_letter = url('print-receipt?id=' . $this->id);
                return '<a title="Print admission letter" href="' . $admission_letter . '" target="_blank">Print receipt</a>';
            });


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
        $show->field('description', __('Description'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));

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

        //on submit
        $form->submitted(function (Form $form) {
            //check if is from school pay
            if ($form->source == 'SCHOOL_PAY') {
                //check if the transaction id is valid
                $trans = Transaction::where([
                    'school_pay_transporter_id' => $form->school_pay_transporter_id,
                ])->first();
                if ($trans != null) {
                    //if the transaction id is already used
                    admin_error("Transaction ID already used");
                    return back();
                }
            }
        });

        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('type', __('Transaction type'))->default('FEES_PAYMENT')->rules('required');
        $form->hidden('created_by_id', __('created_by_id'))->default(Admin::user()->id)->rules('required');
        $form->hidden('is_contra_entry', __('is_contra_entry'))->default(0)->rules('required');

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

        //->format('YYYY-MM-DD HH:mm:ss')
        $form->datetime('payment_date', __('Date'))
            ->default(date('Y-m-d H:i:s'))
            ->rules('required');


        $form->radio('source', "Money deposited to")
            ->options([
                "SCHOOL_PAY" => 'School Pay',
                "MANUAL_ENTRY" => 'Manual Entry (Cash)',
            ])
            ->when('SCHOOL_PAY', function (Form $form) {
                $form->text('school_pay_transporter_id', 'School Pay transaction ID')
                    ->rules('required|numeric');
            })->rules('required')
            ->required();


        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();


        /* $form->saving(function (Form $form) {
            $form->ignore(['source']);
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });
 */

        return $form;
    }
}
