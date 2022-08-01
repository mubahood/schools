<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\Transaction;
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

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('account_id', __('Account id'));
        $grid->column('amount', __('Amount'));
        $grid->column('description', __('Description'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));

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
        $show->field('created_at', __('Created at'));
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
            ->ajax($ajax_url)->rules('required');

        $form->text('amount', __('Amount'))
            ->attribute('type', 'number')
            ->rules('required|int');

        $form->radio('source', "Money deposited to")
            ->options([
                'to_bank' => 'To bank',
                'to_cash' => 'To cash',
                'to_other' => 'To other',
            ])
            ->rules('required')
            ->when('to_bank', function ($f) {
                return $f->select('source_account', "Select bank account")
                    ->options(
                        Account::where([
                            'enterprise_id' => Admin::user()->enterprise_id,
                            'type' => 'BANK_ACCOUNT'
                        ])->get()->pluck('name', 'id')
                    )
                    ->rules('required');
            })
            ->when('to_cash', function ($f) {
                return $f->select('source_account', "Select cash account")
                    ->options(
                        Account::where([
                            'enterprise_id' => Admin::user()->enterprise_id,
                            'type' => 'CASH_ACCOUNT'
                        ])->get()->pluck('name', 'id')
                    )
                    ->rules('required');
            });
        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();



        return $form;
    }
}
