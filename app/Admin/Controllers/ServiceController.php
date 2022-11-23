<?php

namespace App\Admin\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ServiceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Services';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /* $x = Service::find(1);
        $x->fee = rand(1000, 100000);
        $x->save();
        die("Anjane"); */

        $grid = new Grid(new Service());
        $grid->disableBatchActions();
        $grid->disableExport();

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });



        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            $u = Admin::user();


            $cats = [];
            foreach (ServiceCategory::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->get() as $v) {
                $cats[$v->id] = $v->name;
            }

            $filter->equal('service_category_id', 'Filter by service category')
                ->select($cats);
        });


        $grid->quickSearch('name')->placeholder("Search...");

        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Service #ID'));
        $grid->column('name', __('Name'))->sortable();
        $grid->column('service_category_id', __('Category'))
            ->sortable()
            ->display(function () {
                return $this->service_category->name;
            });
        $grid->column('fee', __('Fee'))->display(function () {
            return "UGX " . number_format($this->fee);
        });


        $grid->column('subs', __('Subscribers'))->display(function () {
            return count($this->subs);
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
        $show = new Show(Service::findOrFail($id));

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

        $form = new Form(new Service());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->text('name', __('Name'))->rules('required');

        $form->select('service_category_id', 'Service category')
            ->options(ServiceCategory::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()->pluck('name', 'id'));
        $form->text('fee', __('Fee'))->attribute('type', 'number')->rules('required');

        $form->textarea('description', __('Description'));



        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();
        $form->disableReset();

        return $form;
    }
}
