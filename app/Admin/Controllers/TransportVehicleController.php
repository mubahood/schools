<?php

namespace App\Admin\Controllers;

use App\Models\TransportVehicle;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransportVehicleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Transport Vehicles';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TransportVehicle());
        $grid->quickSearch('name', 'registration_number', 'type', 'description');
        $u = auth('admin')->user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ])
            ->orderBy('name', 'ASC');
        $grid->column('name', __('Name'))->sortable();
        $grid->column('registration_number', __('Registration number'));
        $grid->column('type', __('Type'))->sortable();
        $grid->column('description', __('Description'));
        $grid->disableBatchActions();
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
        $show = new Show(TransportVehicle::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('registration_number', __('Registration number'));
        $show->field('type', __('Type'));
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
        $form = new Form(new TransportVehicle());
        $u = auth('admin')->user();
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);
        $form->text('name', __('Vehicle Name'))->rules('required');
        $form->text('registration_number', __('Registration number'))->rules('required');
        $form->radio('type', __('Type'))
            ->options([
                'bus' => 'Bus',
                'car' => 'Car',
                'van' => 'Van',
                'truck' => 'Truck',
                'motorcycle' => 'Motorcycle',
                'bicycle' => 'Bicycle',
                'other' => 'Other',
            ])->default('other');
        $form->textarea('description', __('Description'));

        return $form;
    }
}
