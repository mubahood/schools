<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\MarkRecord;
use App\Models\ReportCard;
use App\Models\Subject;
use App\Models\TermlyReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MarkRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MarkRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MarkRecord());

        $grid->export(function ($export) {
            $export->filename('School dynamics.csv');
            $export->except(['is_submitted']);
            $export->originalValue(['score', 'remarks']);
        });

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        if (!Admin::user()->isRole('dos')) {

            $grid->model()->where([
                /*                 'teacher_id' => Admin::user()->id, */]);
        }

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableBatchActions();

        if (
            (!Admin::user()->isRole('dos')) &&
            ((!isset($_GET['academic_class_id'])) ||
                (!isset($_GET['exam_id'])) ||
                (!isset($_GET['subject_id'])) ||
                (((int)($_GET['subject_id'])) < 1) ||
                (((int)($_GET['exam_id'])) < 1) ||
                (((int)($_GET['academic_class_id'])) < 1))
        ) {
            admin_success(
                'Alert',
                'Select class, exam and subject and press "search button" to enter marks.'
            );
            $grid->model()->where([
                'enterprise_id' => 0,
            ])->orderBy('id', 'DESC');
        }

        $grid->filter(function ($filter) {


            if (
                (!Admin::user()->isRole('dos')) &&
                ((!isset($_GET['academic_class_id'])) ||
                    (!isset($_GET['exam_id'])) ||
                    (!isset($_GET['subject_id'])) ||
                    (((int)($_GET['subject_id'])) < 1) ||
                    (((int)($_GET['exam_id'])) < 1) ||
                    (((int)($_GET['academic_class_id'])) < 1))
            ) {
                $filter->expand();
            }


            // Remove the default id filter 
            $filter->disableIdFilter();
            $ent = Admin::user()->ent;
            $year = $ent->dpYear();
            $term = $ent->active_term();

            // Add a column filter 
            $u = Admin::user();
            $filter->equal('academic_class_id', 'Filter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => $year->id
            ])
                ->orderBy('id', 'Desc')
                ->get()->pluck('name_text', 'id'));


            $exams = [];
            foreach (TermlyReportCard::where([
                'enterprise_id' => $u->enterprise_id,
                'term_id' => $term->id,
            ])->get() as $ex) {
                $exams[$ex->id] = $ex->name;
            } 

            $filter->equal('temly_report_card_id', 'Filter by Report Card')->select($exams);

            $subs = [];
            foreach (Subject::where([
                'enterprise_id' => $u->enterprise_id,
            ])
                ->orderBy('id', 'desc')
                ->get() as $ex) {
                if ($ex->academic_class == null) {
                    continue;
                }
                if ($ex->academic_class->academic_year_id != $year->id) {
                    continue;
                }


                if (Admin::user()->isRole('dos')) {
                    $subs[$ex->id] = $ex->subject_name . " - " . $ex->academic_class->name_text;
                } else {
                    if (
                        $ex->subject_teacher == Admin::user()->id ||
                        $ex->teacher_1 == Admin::user()->id ||
                        $ex->teacher_2 == Admin::user()->id ||
                        $ex->teacher_3 == Admin::user()->id
                    ) {
                        $subs[$ex->id] = $ex->subject_name . " - " . $ex->academic_class->name_text;
                    }
                }
            }
            $filter->equal('subject_id', 'Filter by subject')->select($subs);


            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('student_id', 'Student')->select()->ajax($ajax_url);
        });

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('termly_report_card_id', __('Termly report card id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('academic_class_id', __('Academic class id'));
        $grid->column('academic_class_sctream_id', __('Academic class sctream id'));
        $grid->column('main_course_id', __('Main course id'));
        $grid->column('subject_id', __('Subject id'));
        $grid->column('bot_score', __('Bot score'));
        $grid->column('mot_score', __('Mot score'));
        $grid->column('eot_score', __('Eot score'));
        $grid->column('bot_is_submitted', __('Bot is submitted'));
        $grid->column('mot_is_submitted', __('Mot is submitted'));
        $grid->column('eot_is_submitted', __('Eot is submitted'));
        $grid->column('bot_missed', __('Bot missed'));
        $grid->column('mot_missed', __('Mot missed'));
        $grid->column('eot_missed', __('Eot missed'));
        $grid->column('initials', __('Initials'));
        $grid->column('remarks', __('Remarks'));

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
        $show = new Show(MarkRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('termly_report_card_id', __('Termly report card id'));
        $show->field('term_id', __('Term id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('academic_class_sctream_id', __('Academic class sctream id'));
        $show->field('main_course_id', __('Main course id'));
        $show->field('subject_id', __('Subject id'));
        $show->field('bot_score', __('Bot score'));
        $show->field('mot_score', __('Mot score'));
        $show->field('eot_score', __('Eot score'));
        $show->field('bot_is_submitted', __('Bot is submitted'));
        $show->field('mot_is_submitted', __('Mot is submitted'));
        $show->field('eot_is_submitted', __('Eot is submitted'));
        $show->field('bot_missed', __('Bot missed'));
        $show->field('mot_missed', __('Mot missed'));
        $show->field('eot_missed', __('Eot missed'));
        $show->field('initials', __('Initials'));
        $show->field('remarks', __('Remarks'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MarkRecord());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('termly_report_card_id', __('Termly report card id'));
        $form->number('term_id', __('Term id'));
        $form->number('administrator_id', __('Administrator id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('academic_class_sctream_id', __('Academic class sctream id'));
        $form->number('main_course_id', __('Main course id'));
        $form->number('subject_id', __('Subject id'));
        $form->number('bot_score', __('Bot score'));
        $form->number('mot_score', __('Mot score'));
        $form->number('eot_score', __('Eot score'));
        $form->text('bot_is_submitted', __('Bot is submitted'))->default('No');
        $form->text('mot_is_submitted', __('Mot is submitted'))->default('No');
        $form->text('eot_is_submitted', __('Eot is submitted'))->default('No');
        $form->text('bot_missed', __('Bot missed'))->default('Yes');
        $form->text('mot_missed', __('Mot missed'))->default('Yes');
        $form->text('eot_missed', __('Eot missed'))->default('Yes');
        $form->text('initials', __('Initials'));
        $form->textarea('remarks', __('Remarks'));

        return $form;
    }
}
