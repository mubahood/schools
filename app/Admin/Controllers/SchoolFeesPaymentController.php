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

            $filter->equal('term_id', 'Fliter by term')->select(Term::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('id', 'desc')
                ->get()
                ->pluck('name_text', 'id'));
            $filter->between('payment_date', 'Date between')->datetime();
        });


        $grid->disableBatchActions();
        $grid->disableActions();
        $grid->model()
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'type' => 'FEES_PAYMENT',
            ])
            ->orderBy('id', 'DESC');

        $grid->column('id', __('ID'))->sortable()->hide();
        $grid->column('source', __('source'))->sortable();
        Source
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



        $grid->column('description', __('Description'));

        $grid->column('academic_year_id', __('Academic year id'))->hide();
        $grid->column('term_id', __('Term'))->display(function () {
            if ($this->term == null) {
            }
            return 'Term ' . $this->term->name_text;
        })
            ->sortable();

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
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('type', __('Transaction type'))->default('FEES_PAYMENT')->rules('required');
        $form->hidden('created_by_id', __('created_by_id'))->default(Admin::user()->id)->rules('required');
        $form->hidden('is_contra_entry', __('is_contra_entry'))->default(0)->rules('required');
        $form->hidden('school_pay_transporter_id', __('is_contra_entry'))->default('-')->rules('required');
 
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

        $form->text('amount', __('Amount'))
            ->attribute('type', 'number')
            ->rules('required|int');

        $form->date('payment_date', __('Date'))
            ->rules('required');


        $form->hidden('source', "Money deposited to")->default('MANUAL_ENTRY')
            ->required()
            ->readonly();

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
