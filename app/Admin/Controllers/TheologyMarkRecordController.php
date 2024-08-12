<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Enterprise;
use App\Models\TheologyMarkRecord;
use App\Models\ReportCard;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TermlyReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyStream;
use App\Models\TheologySubject;
use App\Models\TheologyTermlyReportCard;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TheologyMarkRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Theology Marks';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TheologyMarkRecord());

        $grid->export(function ($export) {
            $export->filename('School dynamics.csv');
            $export->except(['is_submitted']);
            $export->originalValue(['score', 'remarks']);
        });

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->disableActions();
        $grid->disableCreateButton();

        $subs_ids = [];
        $u = Admin::user();
        $ent = Enterprise::find($u->enterprise_id);
        $dpYear =  $ent->dpYear();
        if ($dpYear == null) {
            die("Display year not found.");
        }
        if (Admin::user()->isRole('dos')) {
            foreach (
                TheologySubject::where([
                    'enterprise_id' => $u->enterprise_id
                ])
                    ->orderBy('theology_course_id', 'asc')
                    ->get() as $ex
            ) {
                if ($ex->theology_class->academic_year_id != $dpYear->id) {
                    continue;
                }
                $subs[$ex->id] = $ex->course->name . " - " . $ex->theology_class->name . " - " . $dpYear->name;
            }
        } else {
            foreach (
                TheologySubject::where([
                    'enterprise_id' => $u->enterprise_id
                ])
                    ->orderBy('theology_course_id', 'asc')
                    ->get() as $ex
            ) {
                if (
                    $ex->subject_teacher == Admin::user()->id ||
                    $ex->teacher_1 == Admin::user()->id ||
                    $ex->teacher_2 == Admin::user()->id ||
                    $ex->teacher_3 == Admin::user()->id
                ) {
                    if ($ex->theology_class->academic_year_id != $dpYear->id) {
                        continue;
                    }
                    $subs_ids[] = $ex->id;
                }
            }
        }

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        if (!Admin::user()->isRole('dos')) {
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->disableActions();
        }



        $grid->export(function ($export) {
            $export->filename('School dynamics.csv');
            $export->except(['is_submitted']);
            $export->originalValue(['score', 'remarks']);
        });

        if (
            (!Admin::user()->isRole('dos')) &&
            (

                (!isset($_GET['theology_subject_id'])) ||
                (((int)($_GET['theology_subject_id'])) < 1))
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
                ((!isset($_GET['theology_class_id'])) ||
                    (!isset($_GET['theology_exam_id'])) ||
                    (!isset($_GET['theology_subject_id'])) ||
                    (((int)($_GET['theology_subject_id'])) < 1) ||
                    (((int)($_GET['theology_exam_id'])) < 1) ||
                    (((int)($_GET['theology_class_id'])) < 1))
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
            $filter->equal('theology_class_id', 'Filter by class')->select(TheologyClass::where([
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => $year->id
            ])
                ->orderBy('id', 'Desc')
                ->get()->pluck('name_text', 'id'));




            /*             $exams = [];
            foreach (TheologyTermlyReportCard::where([
                'enterprise_id' => $u->enterprise_id,
                'term_id' => $term->id,
            ])->get() as $ex) {
                $exams[$ex->id] = $ex->report_title;
            }

            $filter->equal('termly_report_card_id', 'Filter by Report Card')->select($exams); */

            $subs = [];
            $u = Admin::user();
            $ent = Enterprise::find($u->enterprise_id);
            $dpYear =  $ent->dpYear();
            if ($dpYear == null) {
                die("Display year not found.");
            }
            if (Admin::user()->isRole('dos')) {
                foreach (
                    TheologySubject::where([
                        'enterprise_id' => $u->enterprise_id
                    ])
                        ->orderBy('theology_course_id', 'asc')
                        ->get() as $ex
                ) {
                    if ($ex->theology_class->academic_year_id != $dpYear->id) {
                        continue;
                    }
                    $subs[$ex->id] = $ex->course->name . " - " . $ex->theology_class->name . " - " . $dpYear->name;
                }
            } else {
                foreach (
                    TheologySubject::where([
                        'enterprise_id' => $u->enterprise_id
                    ])
                        ->orderBy('theology_course_id', 'asc')
                        ->get() as $ex
                ) {
                    if (
                        $ex->subject_teacher == Admin::user()->id ||
                        $ex->teacher_1 == Admin::user()->id ||
                        $ex->teacher_2 == Admin::user()->id ||
                        $ex->teacher_3 == Admin::user()->id
                    ) {
                        if ($ex->theology_class->academic_year_id != $dpYear->id) {
                            continue;
                        }
                        $subs[$ex->id] = $ex->course->name . " - " . $ex->theology_class->name . " - " . $dpYear->name;
                    }
                }
            }



            $filter->equal('theology_subject_id', 'Filter by subject')->select($subs);


            $u = Admin::user();
            $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student");

            $streams = [];
            foreach (
                TheologyStream::where(
                    [
                        'enterprise_id' => $u->enterprise_id,
                    ]
                )
                    ->orderBy('id', 'desc')
                    ->get() as $ex
            ) {
                $streams[$ex->id] = $ex->theology_class->short_name . " - " . $ex->name;
            }
            $filter->equal('theology_stream_id', 'Filter by Stream')->select($streams);

            $filter->equal('administrator_id', 'Filter by Student')->select(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })->ajax($ajax_url);


            $exams = [];
            foreach (
                Term::where([
                    'enterprise_id' => $u->enterprise_id,
                ])
                    ->orderBy('id', 'desc')
                    ->get() as $ex
            ) {
                $exams[$ex->id] = "Term " . $ex->name_text;
            }
            $filter->equal('term_id', 'Filter by Term')->select($exams);
        });



        $ent = Admin::user()->ent;
        $year = $ent->dpYear();
        $term = $ent->active_term();
        $reportCard = TheologyTermlyReportCard::where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'term_id' => $term->id,
        ])->first();
        if ($reportCard == null) {
            admin_error('Alert', 'No report card has been created for this term.');
            return redirect()->back();
        }

        $grid->column('id', __('Id'))->hide()->sortable();
        $grid->column('created_at', __('Created'))->sortable()->hide();
        $grid->column('updated_at', __('Updated'))->sortable()->hide();
        $grid->column('termly_report_card_id', __('Termly Report'))->display(function ($termly_report_card_id) {
            if ($this->termlyReportCard ==  null) {
                $this->delete();
                return 'Deleted';
            }
            return $this->termlyReportCard->report_title;
        })
            ->hide()
            ->sortable();
        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                return 'Term ' . $this->term->name_text;
            })
            ->sortable();

        $grid->column('academic_class_id', __('Class'))
            ->display(function ($academic_class_id) {

                return $this->academicClass->short_name;
            })
            ->sortable();




        $grid->column('theology_stream_id', __('Stream'))
            ->display(function ($academic_class_sctream_id) {
                $stream_name = '-';
                if ($this->stream != null) {
                    $stream_name = $this->stream->name;
                }
                return $stream_name;
            })
            ->sortable();

        $grid->column('subject_id', __('Subject'))->display(function () {
            if ($this->subject == null) {
                return '-';
            }
            return $this->subject->name;
        })->sortable();

        $grid->column('administrator_id', __('Student'))
            ->display(function ($administrator_id) {
                if ($this->student == null) {
                    $this->delete();
                    return '-';
                }
                return $this->student->name;
            })
            ->sortable();

        if ($reportCard->display_bot_to_teachers == 'Yes') {
            $grid->column('bot_score', __($reportCard->bot_name))
                ->editable()
                ->sortable();
        }
        if ($reportCard->display_mot_to_teachers == 'Yes') {
            $grid->column('mot_score', __($reportCard->mot_name))
                ->editable()
                ->sortable();
        }

        if ($reportCard->display_eot_to_teachers == 'Yes') {
            $grid->column('eot_score', __($reportCard->eot_name))
                ->editable()
                ->sortable();
        }
        $grid->column('remarks', __('Remarks'))->editable()->sortable();





        if ($reportCard->display_bot_to_teachers == 'Yes') {
            $grid->column('bot_is_submitted', __($reportCard->bot_name))
                ->label([
                    'No' => 'danger',
                    'Yes' => 'success',
                ])
                ->filter([
                    'Yes' => 'Submitted',
                    'No' => 'Not Submitted',
                ])
                ->sortable();
        }
        if ($reportCard->display_mot_to_teachers == 'Yes') {

            $grid->column('mot_is_submitted', __($reportCard->mot_name))
                ->label([
                    'No' => 'danger',
                    'Yes' => 'success',
                ])
                ->filter([
                    'Yes' => 'Submitted',
                    'No' => 'Not Submitted',
                ])
                ->sortable();
        }

        if ($reportCard->display_eot_to_teachers == 'Yes') {
            $grid->column('eot_is_submitted', __($reportCard->eot_name))
                ->filter([
                    'Yes' => 'Submitted',
                    'No' => 'Not Submitted',
                ])
                ->label([
                    'No' => 'danger',
                    'Yes' => 'success',
                ])->sortable();
        }




        $grid->column('bot_missed', __('B.O.T Missed'))

            ->editable('select', [
                'Yes' => 'Missed',
                'No' => 'Not Missed',
            ])
            ->hide()
            ->dot([
                'No' => 'success',
                'Yes' => 'danger',
            ])->sortable();
        $grid->column('mot_missed', __('M.O.T Missed'))->editable('select', [
            'Yes' => 'Missed',
            'No' => 'Not Missed',
        ])
            ->hide()
            ->dot([
                'No' => 'success',
                'Yes' => 'danger',
            ])->sortable();
        $grid->column('eot_missed', __('E.O.T Missed'))->editable('select', [
            'Yes' => 'Missed',
            'No' => 'Not Missed',
        ])
            ->hide()
            ->dot([
                'No' => 'success',
                'Yes' => 'danger',
            ])->sortable();
        $grid->column('initials', __('Initials'))->hide();



        $grid->column('total_score_display', __('Average Mark'))->sortable(); 
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
        $show = new Show(TheologyMarkRecord::findOrFail($id));

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
        $form = new Form(new TheologyMarkRecord());

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
