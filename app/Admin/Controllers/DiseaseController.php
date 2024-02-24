<?php

namespace App\Admin\Controllers;

use App\Models\Disease;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DiseaseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Diseases';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Disease());
        $grid->quickSearch('name')->placeholder('Search Disease');
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('name', 'asc');
        $grid->disableBatchActions();
        $grid->column('photo', __('Photo'))->image('', 50, 50)
            ->width(100);
        $grid->column('name', __('Name'))->sortable();
        $grid->column('description', __('Description'))->hide();

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
        $show = new Show(Disease::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
        $show->field('photo', __('Photo'));
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
        $form = new Form(new Disease());

        $u = Admin::user();

        $form->hidden('enterprise_id', __('Enterprise id'))
            ->default($u->enterprise_id);

        $form->text('name', __('Name'))->rules('required');
        $form->image('photo', __('Photo'));
        $form->quill('description', __('Disease Description'));
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
