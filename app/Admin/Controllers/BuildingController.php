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
    protected $title = 'Building';

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
            'enterprise_id' => Admin::user()->enterprise_id,
            // 'administrator_id' => $ent->administrator_id,
        ]);

        $grid->column('buildingName', __('BuildingName'));
        $grid->column('enterprise_id', __('Enterprise id'));
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

        $form->text('buildingName', __('BuildingName'));
       

        return $form;
    }
}
