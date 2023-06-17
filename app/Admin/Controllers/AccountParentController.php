<?php

namespace App\Admin\Controllers;

use App\Models\AccountParent;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class AccountParentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Departments';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AccountParent());

        $grid->disableBatchActions();
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('name', 'Asc');

        $grid->column('name', __('Name'))->sortable();

        $grid->column('budget', __('Budget'))->display(function () {
            $term = Auth::user()->ent->dpTerm();
            return 'UGX ' . number_format($this->getBudget($term));
        });

        $grid->column('expense', __('Expense'))->display(function () {
            $term = Auth::user()->ent->dpTerm();
            return 'UGX ' . number_format($this->getExpenditure($term));
        });

        $grid->column('balance', __('Balance'))->display(function () {
            $term = Auth::user()->ent->dpTerm();
            $bud = $this->getBudget($term);
            $exp = $this->getExpenditure($term);
            $bal = $bud + $exp;
            $color = "green";
            if ($bal < 0) {
                $color = "red";
            }
            return '<span class="p-1 text-white" style="font-wight: 800!important; background-color: ' . $color . ';">UGX ' . number_format($bal) . '</span>';
        });

        $grid->column('Accounts', __('Accounts'))->display(function () {
            return count($this->accounts);
        });
        $grid->column('description', __('Description'))->hide();

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
        $show = new Show(AccountParent::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AccountParent());

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->text('name', __('Name'))->rules('required');
        $form->textarea('description', __('Description'));

        return $form;
    }
}
