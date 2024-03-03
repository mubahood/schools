<?php

namespace App\Admin\Controllers;

use App\Models\Building;
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
    protected $title = 'Rooms';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Room());
        $grid->disableBatchActions();
        $ent = Enterprise::find(Admin::user()->enterprise_id);
        $grid->model()->where([
            'enterprise_id' => $ent->id,
        ]);

        $grid->quickSearch('name')->placeholder('Search Room Name');

        $grid->column('photo', __('Photo'))->lightbox(['width' => 50, 'height' => 50])->width(100)->sortable();
        $grid->column('name', __('Room Name'))->sortable();
        $grid->column('details', __('details'))->hide();
        $grid->column('total_slots', __('Capacity'))->sortable()
            ->filter('range');
        $grid->column('total_slots_occupied', __('Occupied Slots'))->sortable()
            ->filter('range');
        $grid->column('total_slots_vacant', __('Vacant Slots'))->sortable()
            ->filter('range');
        $grid->column('total_slots_occupied_percent', __('Occupied %'))->sortable()
            ->filter('range')
            ->progressBar('success');


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
        $u = Admin::user();
        $form->hidden('enterprise_id')->value(
            $u->enterprise_id
        );

        $buildings = Building::where('enterprise_id', Admin::user()->enterprise_id)->get();

        $form->text('name', __('Room Name'))->rules('required');
        $form->select('building_id', __('Building'))->options($buildings->pluck('name', 'id'))->rules('required');
        $form->text('details', __('Details'));
        $form->image('photo', __('Photo'));



        $form->hasMany('slots', 'Slot', function ($form) {
            $form->text('name', __('Slot Name'));
            $form->text('status', __('status'))->default('Vacant');
            $u = Admin::user();
            $form->hidden('enterprise_id')->value(
                $u->enterprise_id
            );

            /*
            $table->foreignIdFor(Building::class);
            $table->string('name')->nullable();
            $table->string('')->nullable()->default('Vacant');
            */
        });

        $form->disableReset();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        return $form;
    }
}
