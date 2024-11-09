<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Participant;
use App\Models\Session;
use App\Models\Subject;
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
    protected $title = 'Roll-call records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Participant());
        $grid->export(function ($export) {
            $export->column('is_present', function ($value, $original) {
                return $value == 1 ? "Present" : "Absent";
            });
        });
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();
            $filter->equal('academic_class_id', 'Fliter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()
                ->pluck('name_text', 'id'));
            $filter->equal('subject_id', __('Subject'))
                ->select(
                    \App\Models\Subject::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                    ])->get()->pluck('name', 'id')
                );
            /*             $filter->equal('is_present', __('Is Present'))->select([
                1 => 'Present',
                0 => 'Absent',
            ]); */

            $sessions = Session::where([
                'enterprise_id' => $u->enterprise_id,
                'administrator_id' => $u->id,
            ])->get();

            $filter->equal('session_id', __('Roll-call'))
                ->select($sessions->pluck('title', 'id'));
            //created_at range
            $filter->between('created_at', __('Created at'))->datetime();
        });

        $grid->disableCreateButton();

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



        $grid->column('administrator_id', __('Student'))
            ->display(function () {
                if ($this->participant == null) {
                    return "N/A";
                }
                return $this->participant->name;
            })
            ->sortable();
        $grid->column('student_id', __('Student ID'))
            ->display(function () {
                return  $this->participant->user_number;
            })
            ->hide();

        $grid->column('created_at', __('DATE'))
            ->display(function () {
                return Utils::my_date($this->created_at);
            })
            ->sortable();
        $grid->column('academic_year_id', __('Academic year id'))->hide();
        $grid->column('term_id', __('Term id'))->hide();
        $grid->column('academic_class_id', __('Class'))
            ->display(function ($x) {
                $class = AcademicClass::find($x);
                if ($class == null) {
                    return "N/A";
                }
                return $class->name;
            })->sortable();
        $grid->column('subject_id', __('Subject'))
            ->display(function ($x) {
                $sub = Subject::find($x);
                if ($sub == null) {
                    return "N/A";
                }
                return $sub->name_text;
            })->sortable();
        $grid->column('service_id', __('Service'))
            ->display(function ($x) {
                $sub = Subject::find($x);
                if ($sub == null) {
                    return "N/A";
                }
                return $sub->name;
            })->sortable()->hide();
        $grid->column('is_present', __('Status'))
            ->using([
                1 => 'Present',
                0 => 'Absent',
            ])->filter([
                1 => 'Present',
                0 => 'Absent',
            ])->label([
                1 => 'success',
                0 => 'danger',
            ])
            ->sortable();
        $grid->column('session_id', __('Roll-call'))
            ->display(function ($x) {
                $ses = Session::find($x);
                if ($ses == null) {
                    return "N/A";
                }
                return $ses->type . " - " . Utils::my_date($ses->due_date);
            });
        $grid->column('is_done', __('Is done'))->hide();

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
