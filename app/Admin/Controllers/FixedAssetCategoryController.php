<?php

namespace App\Admin\Controllers;

use App\Models\FixedAssetCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FixedAssetCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Asset Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new FixedAssetCategory());
        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ]);
        $grid->quickSearch();
        $grid->column('photo', __('Photo'))->image(
            null,
            50,
            50
        )->sortable()
            ->width(100);
        $grid->column('name', __('Name'))->sortable();
        $grid->column('code', __('Code'))->sortable()
            ->filter('like');

        $grid->column('purchase_price', __('Total Investment'))
            ->display(function ($val) {
                return "UGX " . number_format($val);
            })->sortable();
        $grid->column('current_value', __('Current Value'))
            ->display(function ($val) {
                return "UGX " . number_format($val);
            })->sortable();
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
        $show = new Show(FixedAssetCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('photo', __('Photo'));
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
        $form = new Form(new \App\Models\FixedAssetCategory());

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);
        $form->text('name', __('Name'))->rules('required');
        $form->text('code', __('Code'))
            ->creationRules('required|max:3|min:3|unique:fixed_asset_categories,code')
            ->updateRules('required|max:3|min:3|unique:fixed_asset_categories,code,{{id}}')
            ->help('3 characters');
        $form->image('photo', __('Photo'));
        $form->textarea('description', __('Description'));
        $form->disableEditingCheck(); 
        return $form;
    }
}
