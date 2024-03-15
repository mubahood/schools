<?php

namespace App\Admin\Controllers;

use App\Models\FixedAssetCategory;
use App\Models\FixedAssetPrint;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FixedAssetPrintController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Fixed Asset Prints';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FixedAssetPrint());
        $u = auth('admin')->user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ]);

        $grid->column('name', __('Title'))->sortable();
        $grid->column('fixed_asset_category_id', __('Category'))
            ->display(function ($fixed_asset_category_id) {
                $cat = FixedAssetCategory::find($fixed_asset_category_id);
                if ($cat) {
                    return $cat->name;
                }
                return '-';
            })->sortable();
        $grid->column('start_date', __('Start Date'));
        $grid->column('end_date', __('End Date'));
        $grid->column('status', __('Status'))->sortable();
        $grid->column('id', __('PRINT'))
            ->display(function ($id) {
                $print_link = url('fixed-asset-print?id=' . $id);
                return "<a href='$print_link' target='_blank'>Print</a>"; 
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
        $show = new Show(FixedAssetPrint::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('fixed_asset_category_id', __('Fixed asset category id'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
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
        $form = new Form(new FixedAssetPrint());
        $u = auth('admin')->user();
        $form->hidden('enterprise_id')->value($u->enterprise_id);
        $form->text('name', __('Export Title'))->rules('required');
        $cats = FixedAssetCategory::where('enterprise_id', $u->enterprise_id)
            ->get()
            ->pluck('name', 'id');
        $form->select('fixed_asset_category_id', __('Export by category'))
            ->options($cats)
            ->rules('required');
        $form->date('start_date', __('Start date'));
        $form->date('end_date', __('End date'));
        $form->radio('status', __('By Status'))->options([
            'Active' => 'Active',
            'Disposed' => 'Disposed',
            'Damaged' => 'Damaged',
            'Lost' => 'Lost',
        ]);

        return $form;
    }
}
