<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Models\Room;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RoomController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Room';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Room());
        $ent = Enterprise::find(Admin::user()->enterprise_id);
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            // 'administrator_id' => $ent->administrator_id,
        ]);
         $grid->column('numberOfSlots', __('NumberOfSlots'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Room::findOrFail($id));
        $show->field('numberOfSlots', __('NumberOfSlots'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Room());
        $form->text('roomName', __('Room Name'));
        $form->text('numberOfSlots', __('Number Of Slots'));

        $form->hasMany('slot', 'Slot',function($form){
            $form->text('slotName', __('Slot Name'));
        $form->text('studentName', __('Student Name'));

        });


        return $form;
    }
}
