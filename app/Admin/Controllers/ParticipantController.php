<?php

namespace App\Admin\Controllers;

use App\Models\Participant;
use App\Models\Session;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ParticipantController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Participant';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Participant());

        $grid->disableExport();

        $activeSession = Session::where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'administrator_id' => Admin::user()->id,
            'is_open' => 1,
        ])->first();

        if ($activeSession  != null) {
            return redirect(admin_url("sessions/{$activeSession->id}/edit"));
        }

        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');




        $grid->column('created_at', __('Created'))
            ->display(function () {
                return Utils::my_date($this->created_at);
            })
            ->hide()
            ->sortable();

        $grid->column('administrator_id', __('Participant'))
            ->display(function () {
                return $this->participant->name;
            })
            ->sortable();
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('academic_class_id', __('Academic class id'));
        $grid->column('subject_id', __('Subject id'));
        $grid->column('service_id', __('Service id'));
        $grid->column('is_present', __('Is present'));
        $grid->column('session_id', __('Session id'));
        $grid->column('is_done', __('Is done'));

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
        $show = new Show(Participant::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('subject_id', __('Subject id'));
        $show->field('service_id', __('Service id'));
        $show->field('is_present', __('Is present'));
        $show->field('session_id', __('Session id'));
        $show->field('is_done', __('Is done'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Participant());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('administrator_id', __('Administrator id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('subject_id', __('Subject id'));
        $form->number('service_id', __('Service id'));
        $form->switch('is_present', __('Is present'));
        $form->number('session_id', __('Session id'));
        $form->switch('is_done', __('Is done'));

        return $form;
    }
}
