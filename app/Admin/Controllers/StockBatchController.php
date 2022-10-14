<?php

namespace App\Admin\Controllers;

use App\Models\FundRequisition;
use App\Models\StockBatch;
use App\Models\StockItemCategory;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class StockBatchController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock batches';

    /** 
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockBatch());


        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            $filter->equal('stock_item_category_id', 'Filter by Item')->select(
                StockItemCategory::all()
                    ->pluck('name', 'id')
            );
        });


        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        if (!Admin::user()->isRole('admin')) {
            $grid->disableActions();
        };


        //$grid->disableActions();

        if (!Admin::user()->isRole('admin')) {
            $grid->model()->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'manager' => Admin::user()->id,
            ])
                ->orderBy('id', 'Desc');
        } else {
            $grid->model()->where([
                'enterprise_id' => Admin::user()->enterprise_id,
            ])
                ->orderBy('id', 'Desc');
        }

        $grid->column('id', __('Batch Number'))->sortable();
        $grid->column('stock_item_category_id', __('Item'))->display(function () {
            return $this->cat->name;
        })->sortable();

        $grid->column('original_quantity', __('Original quantity'))
            ->display(function ($x) {
                return number_format($x) . " " . Str::plural($this->cat->measuring_unit);
            })->sortable()->totalRow(function ($x) {
                return number_format($x);
            });

        $grid->column('current_quantity', __('Current quantity'))
            ->display(function ($x) {
                return number_format($x) . " " . Str::plural($this->cat->measuring_unit);
            })->sortable()->totalRow(function ($x) {
                return number_format($x);
            });
        $grid->column('description', __('Description'))->hide();

        $grid->column('supplier_id', __('Supplier'))->display(function () {
            return $this->supplier->name . " " . $this->supplier->phone_number_1;
        })->sortable();

        $grid->column('manager', __('Stock manager'))->display(function () {
            return $this->stock_manager->name;
        })->sortable();

        $grid->column('purchase_date', __('Date'));

        $grid->column('photo', __('Photo'))->hide();
        $grid->column('fund_requisition_id', __('Requisition form ID'))->hide();

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
        $show = new Show(StockBatch::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('purchase_date', __('Created'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('stock_item_category_id', __('Stock item category id'));
        $show->field('original_quantity', __('Original quantity'));
        $show->field('current_quantity', __('Current quantity'));
        $show->field('photo', __('Photo'));
        $show->field('description', __('Description'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('fund_requisition_id', __('Fund requisition id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StockBatch());


        $form->date('purchase_date', __('Date'))->rules('required');


        $form->hidden('enterprise_id')->rules('required')->default(Admin::user()->enterprise_id)
            ->value(Admin::user()->enterprise_id);

        $cats = [];
        foreach (StockItemCategory::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->get() as $val) {
            $p = Str::plural($val->measuring_unit);
            $cats[$val->id] = $val->name . " - (in $p)";
        }

        $form->select('stock_item_category_id', 'Item')
            ->options($cats)->rules('required');




        $ads = [];
        foreach (Administrator::where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'user_type' => 'supplier'
        ])->get() as $ad) {
            if ($ad->isRole('supplier')) {
                $ads[$ad->id] = $ad->name . " - ID #{$ad->id}";
            };
        }


        $employees = [];
        foreach (Administrator::where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'user_type' => 'employee'
        ])->get() as $ad) {
            $employees[$ad->id] = $ad->name . " - ID #{$ad->id}";
        }


        $form->select('supplier_id', __('Supplier'))
            ->options(
                $ads
            )
            ->rules('required');



        $forms = [];
        foreach (FundRequisition::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc')
            ->get() as $val) {
            $forms[$val->id] = $val->cat->name . " UGX " . number_format($val->total_amount)
                . " - " . $val->created_at;
        }



        $form->decimal('original_quantity', __('Quantity (in Units)'))
            ->attribute('type', 'number')
            ->rules('required');

        if (Admin::user()->isRole('admin')) {
            $form->select('manager', __('Stock Manager'))
                ->options(
                    $employees
                )
                ->rules('required');
        } else {
            $form->select('manager', __('Stock Manager'))
                ->options(
                    $employees
                )
                ->default(Admin::user()->id)
                ->readOnly()
                ->rules('required');
        }



        $form->textarea('description', __('Stock Description'));

        $form->image('photo', __('Stock Photo'));

        $form->select('fund_requisition_id', 'Funds requisition form')
            ->options($forms);





        return $form;
    }
}
