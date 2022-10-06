<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Attribute;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

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

            $filter->equal('account_id', 'Student')->select()->ajax($ajax_url);
        });

        $grid->disableBatchActions();
        $grid->disableActions();

        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Id'))->sortable();

        $grid->column('description', __('Description'));
        $grid->column('academic_year_id', __('Academic year id'))->hide();
        $grid->column('account_id', __('Account'))->display(function () {
            return $this->account->name;
        });



        $grid->column('amount', __('Amount'))->display(function () {
            return "UGX " . number_format($this->amount);
        })->sortable();
        $grid->column('created_at', __('Created'))->sortable();

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

        $form->select('account_id', "Account")
            ->options(function ($id) {
                $a = Account::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax($ajax_url)->rules('required');

        $form->radio('is_debit', "Transaction type")
            ->options([
                1 => 'Debit (+)',
                0 => 'Credit (-)',
            ])->default(-1)
            ->rules('required');;

        $form->text('amount', __('Amount'))
            ->attribute('type', 'number')
            ->rules('required|int');
        $form->textarea('description', __('Description'))->rules('required');

        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();






        return $form;
    }
}
