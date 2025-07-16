<?php

namespace App\Admin\Controllers;

use App\Models\BankAccount;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BankAccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'BankAccount';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BankAccount());

        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc');
        $grid->disableBatchActions();

        $grid->column('name', __('Name'))->sortable();
        $grid->column('account_number', __('Account Number'))->sortable();
        $grid->column('details', __('Details'))->hide();
        $grid->column('status', __('Status'))
            ->label([
                'Active' => 'success',
                'Not Active' => 'danger',
            ]);

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
        $show = new Show(BankAccount::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('account_number', __('Account number'));
        $show->field('details', __('Details'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BankAccount());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);

        $form->text('name', __('Name'))->rules('required');
        $form->textarea('account_number', __('Account number'))->rules('required');
        $form->textarea('details', __('Details'));
        $form->text('status', __('Status'))->default('Active');

        return $form;
    }
}
