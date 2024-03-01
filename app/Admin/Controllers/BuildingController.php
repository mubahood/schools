<?php

namespace App\Admin\Controllers;

use App\Models\Building;
use App\Models\Enterprise;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BuildingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Hostel Buildings';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Building());

        $ent = Enterprise::find(Admin::user()->enterprise_id);
        $grid->model()->where([
            'enterprise_id' => $ent->id,
        ]);

        $grid->disableBatchActions();
        $grid->quickSearch('name')->placeholder('Search Building Name');

        $grid->column('photo', __('Photo'))->lightbox(['width' => 50, 'height' => 50])->width(100)->sortable();
        $grid->column('name', __('Building Name'))->sortable();
        $grid->column('created_at', __('Created'))->hide();
        $grid->column('total_rooms', __('Total Rooms'))->sortable();
        $grid->column('total_slots', __('Total Slots'))->sortable();
        $grid->column('total_slots_occupied', __('Occupied Slots'))->sortable();
        $grid->column('total_slots_vacant', __('Vacant Slots'))->sortable();
        $grid->column('total_slots_occupied_percent', __('Occupied %'))->sortable()
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
        $show = new Show(Building::findOrFail($id));

        $show->field('buildingName', __('BuildingName'));
        $show->field('enterprise_id', __('Enterprise id'));
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
        $form = new Form(new Building());
        //hidden enterprise_id 
        $form->hidden('enterprise_id')->value(Admin::user()->enterprise_id);
        $form->text('name', __('Building Name'))->rules('required|string|max:255');
        $form->image('photo', __('Photo'));
        $form->textarea('details', __('Details'));


        $form->disableReset();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableDelete();
        });
        return $form;
    }
}
