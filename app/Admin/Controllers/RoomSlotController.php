<?php

namespace App\Admin\Controllers;

use App\Models\Building;
use App\Models\Enterprise;
use App\Models\Room;
use App\Models\RoomSlot;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class RoomSlotController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Room Slots';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RoomSlot());
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            //buildings 
            $buildings = Building::getBuildingDropdown(Admin::user()->enterprise_id);
            $filter->equal('room.building_id', __('Building'))->select($buildings);
            $filter->equal('room_id', __('Room'))->select(Room::getRoomDropdown(Admin::user()->enterprise_id));
            $u = Admin::user();
            //by current student
            $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student");
            $filter->equal('current_student_id', 'Filter by Current Student')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);
        });


        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ]);
        $grid->disableActions();
        $grid->disableBatchActions();

        $grid->column('name', __('Slot Name'))->sortable();
        $grid->column('room_id', __('Room'))
            ->display(function ($room_id) {
                return Room::find($room_id)->name_text;
            })
            ->sortable();
        $grid->column('current_student_id', __('Current Student'))
            ->display(function ($current_student_id) {
                if ($current_student_id == null) {
                    return '-';
                }
                if ($this->current_student == null) {
                    return '-';
                }
                return $this->current_student->name;
            })
            ->sortable();

        $grid->column('status', __('Status'))
            ->using([
                'vacant' => 'Vacant',
                'occupied' => 'Occupied',
                'reserved' => 'Reserved',
            ])
            ->label([
                'vacant' => 'success',
                'occupied' => 'danger',
                'reserved' => 'warning',
            ])
            ->filter([
                'vacant' => 'Vacant',
                'occupied' => 'Occupied',
                'reserved' => 'Reserved',
            ]);

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
        $show = new Show(RoomSlot::findOrFail($id));

        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('id', __('Id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('building_id', __('Building id'));
        $show->field('room_id', __('Room id'));
        $show->field('current_student_id', __('Current student id'));
        $show->field('name', __('Name'));
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
        $form = new Form(new RoomSlot());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);

        //rooms
        $rooms = Room::getRoomDropdown($u->enterprise_id);

        if ($form->isCreating()) {
            $form->select('room_id', __('Room'))->options($rooms)->rules('required');
        } else {
            $form->display('room.name_text', __('Room'));
        }


        $form->text('name', __('Room Slot Name'))->rules('required');


        $form->disableReset();
        $form->disableEditingCheck();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });


        return $form;
    }
}
