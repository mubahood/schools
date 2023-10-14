<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Models\Slot;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SlotController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Slot';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Slot());

        $ent = Enterprise::find(Admin::user()->enterprise_id);
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            // 'administrator_id' => $ent->administrator_id,
        ]);
        $grid->column('slotName', __('SlotName'));
        $grid->column('studentName', __('StudentName'));
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
        $show = new Show(Slot::findOrFail($id));
        $show->field('slotName', __('SlotName'));
        $show->field('studentName', __('StudentName'));
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
        $form = new Form(new Slot());
        $form->text('slotName', __('SlotName'));
        $form->text('studentName', __('StudentName'));

        return $form;
    }
}
