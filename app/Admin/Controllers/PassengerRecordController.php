<?php

namespace App\Admin\Controllers;

use App\Models\PassengerRecord;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PassengerRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student Transport Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PassengerRecord());
        $grid->disableCreateButton();
        $u = Admin::user();


        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $u = Admin::user();
            $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student"); 

            $filter->equal('user_id', 'Filter by Student')->select(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })->ajax($ajax_url);

            //date range
            $filter->between('created_at', 'Filter by date')->datetime();
        });




        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('created_at', 'desc');

        $grid->disableBatchActions();
        $grid->column('trip_id', __('Trip'))
            ->display(function ($trip_id) {
                if ($this->trip == null) {
                    return 'Trip not found';
                }
                return $this->trip->name;
            })->sortable();
        $grid->column('user_id', __('Student'))
            ->display(function ($user_id) {
                if ($this->user == null) {
                    return 'Student not found';
                }
                return $this->user->name_text;
            })->sortable();
        $grid->column('status', __('Status'))
            ->label([
                'Arrived' => 'success',
                'Onboard' => 'warning',
                'Absent' => 'danger',
            ])->filter([
                'Arrived' => 'Arrived',
                'Onboard' => 'Onboard',
                'Absent' => 'Absent',
            ])->sortable();
        $grid->column('start_time', __('Onboard Time'))
            ->display(function ($start_time) {
                if ($start_time == null) {
                    return 'Not Onboarded';
                }
                return Utils::my_date_time($start_time);
            })->sortable();

        $grid->column('end_time', __('Offboard Time'))
            ->display(function ($end_time) {
                if ($end_time == null) {
                    return 'Not Offboarded';
                }
                return Utils::my_date_time($end_time);
            })->sortable();
        $grid->column('created_at', __('Created'))->hide();
        /* 
    "id" => 9
    "created_at" => "2025-05-14 11:15:38"
    "updated_at" => "2025-05-14 11:15:38"
    "enterprise_id" => 21
    "driver_id" => 17512
    "term_id" => 65
    "transport_route_id" => 9
    "date" => "2025-05-14"
    "status" => "Closed"
    "start_time" => "2025-05-14 09:32:01.189830"
    "end_time" => null
    "start_gps" => "0.0,0.0"
    "end_gps" => "0.0,0.0"
    "trip_direction" => "From School"
    "start_mileage" => "21"
    "end_mileage" => null
    "expected_passengers" => 1
    "actual_passengers" => null
    "absent_passengers" => null
    "local_id" => "1747204321213870199"
*/
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
        $show = new Show(PassengerRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('trip_id', __('Trip id'));
        $show->field('user_id', __('User id'));
        $show->field('status', __('Status'));
        $show->field('start_time', __('Start time'));
        $show->field('end_time', __('End time'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PassengerRecord());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('trip_id', __('Trip id'));
        $form->number('user_id', __('User id'));
        $form->text('status', __('Status'));
        $form->text('start_time', __('Start time'));
        $form->text('end_time', __('End time'));

        return $form;
    }
}
