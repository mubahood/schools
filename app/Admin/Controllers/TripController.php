<?php

namespace App\Admin\Controllers;

use App\Models\Term;
use App\Models\Trip;
use App\Models\User;
use App\Models\VisitorRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TripController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Trips';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Trip());
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->quickSearch('name', 'phone_number')->placeholder('Search by name or phone number');
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('created_at', 'desc');
        $grid->column('created_at', __('Date'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })->sortable();

        $grid->column('created_at', __('Date'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })->sortable();
        $grid->column('driver_id', __('Driver'))
            ->display(function ($driver_id) {
                $driver = User::find($driver_id);
                if ($driver != null) {
                    return $driver->name;
                }
                return 'N/A';
            })->sortable();
        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                $term = Term::find($term_id);
                if ($term != null) {
                    return 'Term ' . $term->name_text;
                }
                return 'N/A';
            })->sortable();
        $grid->column('transport_route_id', __('Transport Route'))
            ->display(function ($transport_route_id) {
                $route = \App\Models\TransportRoute::find($transport_route_id);
                if ($route != null) {
                    return $route->name;
                }
                return 'N/A';
            })->sortable();
        $grid->column('date', __('Date'))->sortable();
        $grid->column('status', __('Status'))
            ->label([
                'Ongoing' => 'success',
                'Completed' => 'info',
                'Cancelled' => 'danger',
            ])
            ->filter([
                'Ongoing' => 'Ongoing',
                'Completed' => 'Completed',
                'Cancelled' => 'Cancelled',
            ])
            ->sortable();
        $grid->column('start_time', __('Start Time'))
            ->display(function ($start_time) {
                return date('H:i', strtotime($start_time));
            })->sortable();
        $grid->column('end_time', __('End Time'))
            ->display(function ($end_time) {
                return date('H:i', strtotime($end_time));
            })->sortable();
        $grid->column('start_gps', __('Start GPS'));
        $grid->column('end_gps', __('End GPS'));
        $grid->column('trip_direction', __('Trip Direction'))
            ->dot([
                'To School' => 'success',
                'From School' => 'info',
            ])
            ->filter([
                'To School' => 'To School',
                'From School' => 'From School',
            ])
            ->sortable();
        $grid->column('start_mileage', __('Start Mileage'))->sortable();
        $grid->column('end_mileage', __('End Mileage'))->sortable();
        $grid->column('expected_passengers', __('Expected Passengers'))->sortable();
        $grid->column('actual_passengers', __('Actual Passengers'))->sortable();
        $grid->column('absent_passengers', __('Absent Passengers'))->sortable();
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
        $show = new Show(Trip::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('driver_id', __('Driver id'));
        $show->field('term_id', __('Term id'));
        $show->field('transport_route_id', __('Transport route id'));
        $show->field('date', __('Date'));
        $show->field('status', __('Status'));
        $show->field('start_time', __('Start time'));
        $show->field('end_time', __('End time'));
        $show->field('start_gps', __('Start gps'));
        $show->field('end_gps', __('End gps'));
        $show->field('trip_direction', __('Trip direction'));
        $show->field('start_mileage', __('Start mileage'));
        $show->field('end_mileage', __('End mileage'));
        $show->field('expected_passengers', __('Expected passengers'));
        $show->field('actual_passengers', __('Actual passengers'));
        $show->field('absent_passengers', __('Absent passengers'));
        $show->field('local_id', __('Local id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Trip());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('driver_id', __('Driver id'));
        $form->number('term_id', __('Term id'));
        $form->number('transport_route_id', __('Transport route id'));
        $form->date('date', __('Date'))->default(date('Y-m-d'));
        $form->text('status', __('Status'))->default('Ongoing');
        $form->text('start_time', __('Start time'));
        $form->text('end_time', __('End time'));
        $form->text('start_gps', __('Start gps'));
        $form->text('end_gps', __('End gps'));
        $form->text('trip_direction', __('Trip direction'));
        $form->text('start_mileage', __('Start mileage'));
        $form->text('end_mileage', __('End mileage'));
        $form->number('expected_passengers', __('Expected passengers'));
        $form->number('actual_passengers', __('Actual passengers'));
        $form->number('absent_passengers', __('Absent passengers'));
        $form->text('local_id', __('Local id'));

        return $form;
    }
}
