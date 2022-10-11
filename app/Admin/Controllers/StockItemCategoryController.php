<?php

namespace App\Admin\Controllers;

use App\Models\StockItemCategory;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;


class StockItemCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock item categories';

    /**
     * Make a grid builder.
     *
     * public_html/storage/files/566a4a65425d5b27667e8d454cd7c845.xlsx
     * public/storage/files/566a4a65425d5b27667e8d454cd7c845.xlsx File does not exist.

     * @return Grid
     */
    protected function grid()
    {


        StockItemCategory::update_quantity(Admin::user()->enterprise_id);
        $grid = new Grid(new StockItemCategory());

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->disableBatchActions();
        if (!Admin::user()->isRole('admin')) {
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->disableActions();
        }

        $grid->disableFilter();
        $grid->quickSearch('name')->placeholder("Search...");


        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();

        $grid->column('reorder_level', __('Reorder level'))->display(function ($x) {
            return  Utils::number_format($x, $this->measuring_unit);
        })->sortable();

        $grid->column('quantity', __('Available Quantity'))->display(function ($quantity) {
            return  Utils::number_format($quantity, $this->measuring_unit);
        })->sortable();

        $grid->column('status', __('Stock status'))
            ->using([
                1 => 'In stock',
                0 => 'Out of stock',
            ])
            ->filter([
                1 => 'In stock',
                0 => 'Out of stock',
            ])
            ->label([
                1 => 'success',
                0 => 'danger',
            ])
            ->sortable();
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
        $show = new Show(StockItemCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('measuring_unit', __('Measuring unit'));
        $show->field('description', __('Description'));
        $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StockItemCategory());
        $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
            ->value(Admin::user()->enterprise_id);
        $form->text('name', __('Name'))->rules('required');
        $form->text('measuring_unit', __('Measuring unit'))->rules('required');
        $form->text('reorder_level', __('Reorder level'))
            ->attribute('type', 'number')
            ->help('in Specified measuring unit above')
            ->rules('required|int');
        $form->textarea('description', __('Description'));

        return $form;
    }
}
