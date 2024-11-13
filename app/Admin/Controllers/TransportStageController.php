<?php

namespace App\Admin\Controllers;

use App\Models\TransportStage;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransportStageController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Routes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TransportStage());
        // $grid->disableCreateButton();
        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('name', 'asc');
        $grid->quickSearch('name')->placeholder('Search by name');

        // $grid->column('id', __('Id'));
        /* $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at')); */
        // $grid->column('enterprise_id', __('Enterprise id'));
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
        $show = new Show(TransportStage::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));
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
        $form = new Form(new TransportStage());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))
            ->default($u->enterprise_id);
        $form->text('name', __('Name'))->required();
        $form->textarea('description', __('Description'));

        return $form;
    }
}
