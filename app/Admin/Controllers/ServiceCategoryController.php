<?php

namespace App\Admin\Controllers;

use App\Models\ServiceCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ServiceCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Service Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ServiceCategory());

        $grid->disableBatchActions();
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('name', 'Asc');
        $grid->quickSearch('name')->placeholder("Search...");
        $grid->column('name', __('Name'))->sortable();
        $grid->column('services', __('Services'))
            ->display(function () {
                return count($this->services);
            })
            ->sortable();

        $grid->column('income', __('Total income'))
            ->display(function () {
                return 'UGX '.number_format($this->income());
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
        $show = new Show(ServiceCategory::findOrFail($id));

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
        $form = new Form(new ServiceCategory());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->text('name', __('Name'))->rules('required');

        $form->radio('want_to_transfer', "Do you want to transfer serfices tom this category?")
            ->options([
                1 => 'Yes',
                0 => 'No',
            ])->when(1, function ($f) {
                $f->text('transfer_keyword', "Transfer keyword")
                    ->rules('required')
                    ->help("Any service containing mentioned keyword in its description should be transfered to this category.");
            })->rules('required');

        $form->textarea('description', __('Description'));

        return $form;
    }
}
