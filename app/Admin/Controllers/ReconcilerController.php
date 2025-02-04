<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Models\Reconciler;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReconcilerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Reconciler';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Reconciler());
        $grid->model()->orderBy('id', 'DESC');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('details', __('Details'));
            $filter->equal('enterprise_id', __('Enterprise'))->select(Enterprise::all()->pluck('name', 'id'));
            //SEARCH IN DETAILS
            $filter->like('details', __('Details'));
        });

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created at'))->sortable();
        // $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise'))
            ->display(function () {
                if ($this->enterprise == null) {
                    return '-';
                }
                return $this->enterprise->name;
            });
        $grid->column('last_update', __('Last Update'))
            ->display(function () {
                $time = strtotime($this->last_update);
                if ($time == false || $time == -1 || $time == -62169984000) {
                    return '-';
                }
                return date('d/m/Y H:i:s', $time);
            });
        $grid->column('details', __('Details'))->sortable();
        $grid->column('back_day', __('Back day'))->sortable();

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
        $show = new Show(Reconciler::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('last_update', __('Last update'));
        $show->field('details', __('Details'));
        $show->field('back_day', __('Back day'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Reconciler());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->textarea('last_update', __('Last update'));
        $form->textarea('details', __('Details'));
        $form->number('back_day', __('Back day'));

        return $form;
    }
}
