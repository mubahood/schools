<?php

namespace App\Admin\Controllers;

use App\Models\StockItemCategory;
use App\Models\SupplierProduct;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class SupplierProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Products';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SupplierProduct());

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');
        $grid->disableBatchActions();

        if (Admin::user()->isRole('supplier')) {
            $grid->model()->where('administrator_id', Admin::user()->id);
            $grid->disableExport();
        } else {
            $grid->disableCreateButton();
        }

        $grid->column('created_at', __('Date'))
            ->display(function ($x) {
                return Utils::my_date($x);
            })
            ->sortable();

        $grid->column('image', __('Photo'))
            ->lightbox(['width' => 60, 'height' => 60]);

        $grid->column('name', __('Product'))->sortable();
        $grid->column('stock_item_category_id', __('Category'));
        $grid->column('price', __('Price (UGX)'))->display(function ($x) {
            return number_format($x);
        })
            ->sortable();

        $grid->column('administrator_id', __('Supplier'))
            ->display(function ($x) {
                if ($this->supplier == null) {
                    return ($x);
                }
                return $this->supplier->name;
            })
            ->sortable();
        $grid->column('images', __('Images'))->hide();
        $grid->column('details', __('Details'))->hide();
        $grid->column('price_details', __('Price details'))->hide();

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
        $show = new Show(SupplierProduct::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('stock_item_category_id', __('Stock item category id'));
        $show->field('name', __('Name'));
        $show->field('image', __('Image'));
        $show->field('images', __('Images'));
        $show->field('details', __('Details'));
        $show->field('price', __('Price'));
        $show->field('price_details', __('Price details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SupplierProduct());
        $u = Admin::user();

        if (!$u->isRole('supplier')) {
            return admin_warning('Only suppliers can post a product.');
        }

        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('administrator_id', __('administrator_id'))->default($u->id)->rules('required');

        $cats = [];
        foreach (StockItemCategory::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->get() as $val) {
            $p = Str::plural($val->measuring_unit);
            $cats[$val->id] = $val->name . " - (in $p)";
        }

        $form->select('stock_item_category_id', 'Item')
            ->options($cats)->rules('required');

        $form->text('name', __('Name'))->rules('required')->required();
        $form->decimal('price', __('Unit Price'))->rules('required')->required();
        $form->textarea('price_details', __('Pricing details'))->rules('required')->required();
        $form->textarea('details', __('Product\'s Details'));
        $form->image('image', __('Product\'s Main Photo'));
        $form->multipleImage('images', __('More photos'));

        $form->disableReset();
        $form->disableViewCheck();


        return $form;
    }
}
