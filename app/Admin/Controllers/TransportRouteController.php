<?php

namespace App\Admin\Controllers;

use App\Models\TransportRoute;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransportRouteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Transport Routes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TransportRoute());

        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id);
        $grid->disableBatchActions();
        $grid->quickSearch();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('single_trip_fare', __('Single trip fare'))->sortable();
        $grid->column('round_trip_fare', __('Round trip fare'))->sortable();
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
        $show = new Show(TransportRoute::findOrFail($id));

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
        $form = new Form(new TransportRoute());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);
        $form->text('name', __('Name'))->rules('required');
        $form->decimal('single_trip_fare', __('Single trip fare'))->rules('required');
        $form->decimal('round_trip_fare', __('Round trip fare'))->rules('required');

        $form->textarea('description', __('Description'));


        return $form;
    }
}
