<?php

namespace App\Admin\Controllers;

use App\Models\Building;
use App\Models\Room;
use App\Models\RoomSlot;
use App\Models\RoomSlotAllocation;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RoomSlotAllocationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Room Slot Allocations';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RoomSlotAllocation());
        $grid->disableBatchActions();
        $grid->column('user_id', __('Student'))
            ->display(function ($user_id) {
                return $this->user->name_text;
            })->sortable();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('room_slot.room.building_id', __('Building'))->select(Building::getBuildingDropdown(Admin::user()->enterprise_id));
            $filter->equal('room_slot.room_id', __('Room'))->select(Room::getRoomDropdown(Admin::user()->enterprise_id));
            $u = Admin::user();
            //by current student
            $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student");
            $filter->equal('user_id', 'Filter by Student')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);
            //due_term_id
            $terms = Term::getItemsToArray([
                'enterprise_id' => $u->enterprise_id
            ]);
            $filter->equal('due_term_id', __('Due Term'))->select($terms);
        });

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'desc');

        if (!isset($_GET['due_term_id'])) {
            $active_term = Admin::user()->ent->active_term();
            $grid->model()->where('due_term_id', $active_term->id);
        }

        $grid->column('room_slot_id', __('Room Slot'))
            ->display(function ($room_slot_id) {
                return $this->room_slot->name . ' - ' . $this->room_slot->room->name_text;
            })->sortable();

        $grid->column('status', __('Status'))
            ->label([
                'Occupied' => 'success',
                'Reserved' => 'warning',
                'Vacant' => 'info',
            ])
            ->filter([
                'Occupied' => 'Occupied',
                'Reserved' => 'Reserved',
                'Vacant' => 'Vacant',
            ])->sortable();

        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));
        $grid->column('due_term_id', __('Due Term'))
            ->display(function ($term_id) {
                if ($this->due_term == null) {
                    return "N/A";
                }
                return $this->due_term->name_text;
            })->sortable()->hide();
        $grid->column('remarks', __('Remarks'))->sortable()->hide();
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
        $show = new Show(RoomSlotAllocation::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('room_slot_id', __('Room slot id'));
        $show->field('user_id', __('User id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('status', __('Status'));
        $show->field('remarks', __('Remarks'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RoomSlotAllocation());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);
        $active_term = Admin::user()->ent->active_term();

        //due_term_id is the term_id of the term the allocation is due
        $form->select('due_term_id', __('Due Term'))->options([
            $active_term->id => "Term " . $active_term->name_text
        ])->rules('required')
            ->default($active_term->id)
            ->readonly();


        if ($form->isCreating()) {
            $slots = RoomSlot::getDropDownList([
                'enterprise_id' => $u->enterprise_id,
                'status' => 'Vacant'
            ]);

            $form->select('room_slot_id', __('Select Room Slot'))->options($slots)->rules('required');

            $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student");
            $form->select('user_id', __('Select Student'))->ajax($ajax_url)->rules('required');
        } else {
            $form->display('room_slot.name', __('Room Slot'));
            $form->display('user.name', __('Student'));
        }

        $form->select('status', __('Status'))
            ->options([
                'Occupied' => 'Occupied',
                'Reserved' => 'Reserved',
                'Vacant' => 'Vacant',
            ])->rules('required');
        $form->text('remarks', __('Remarks'));
        $form->date('start_date', __('Start date'))->default(date('Y-m-d'));
        $form->date('end_date', __('End date'))->default(date('Y-m-d'));
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
