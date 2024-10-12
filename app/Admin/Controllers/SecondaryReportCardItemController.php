<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\SecondaryReportCardItem;
use App\Models\SecondarySubject;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SecondaryReportCardItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SecondaryReportCardItem';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*  $m = SecondaryReportCardItem::find(40);
        $m->score_1 = 0.89;
        SecondaryReportCardItem::do_prepare($m);
        die("done"); */
        $grid = new Grid(new SecondaryReportCardItem());
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();
            $year = $u->ent->active_academic_year();
            $subs = SecondarySubject::get_active_subjects($year->id, true);


            $ajax_url = url(
                '/api/ajax-users?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&user_type=student"
                    . "&model=User"
            );
            $ajax_url = trim($ajax_url);
            $filter->equal('administrator_id', 'Filter by student')
                ->select(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);



            $filter->equal('academic_class_id', 'Filter by Class')
                ->select(AcademicClass::getAcademicClasses(['enterprise_id' => $u->enterprise_id]));

            $filter->equal('academic_class_sctream_id', 'Filter by Stream')
                ->select(AcademicClassSctream::getItemsToArray(['enterprise_id' => $u->enterprise_id]));

            $filter->equal('term_id', 'Filter by Term')
                ->select(Term::getItemsToArray(['enterprise_id' => $u->enterprise_id]));

            $filter->equal('secondary_subject_id', 'Filter by Subject')
                ->select($subs);
            $filter->group('average_score', 'Filter by Score', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });

        $grid->actions(function ($act) {
            $act->disableView();
            $act->disableDelete();
        });

        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ]);

        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'))->sortable();

        $grid->column('academic_class_id', __('Class'))
            ->display(function ($x) {
                if ($this->academic_class == null) {
                    return '-';
                }
                return $this->academic_class->short_name;
            })
            ->sortable();
        $grid->column('academic_class_sctream_id', __('Strem'))
            ->display(function ($x) {
                if ($this->academic_class_stream == null) {
                    return '-';
                }
                return $this->academic_class_stream->name;
            })
            ->sortable();

        $grid->column('administrator_id', __('Student'))
            ->display(function ($x) {
                if ($this->student == null) {
                    return '-';
                }
                return $this->student->name;
            })
            ->sortable();


        $grid->column('secondary_subject_id', __('Subject'))
            ->display(function ($x) {
                if ($this->secondary_subject == null) {
                    return $x;
                }
                return $this->secondary_subject->subject_name . " - " . $this->secondary_subject->code;
            })
            ->sortable();
        $grid->column('average_score', __('Score'))->sortable();
        $grid->column('generic_skills', __('Generic Skills'))->editable();
        $grid->column('remarks', __('Genral Remarks'))->editable();
        $grid->column('teacher', __('Teacher'))->editable();

        $grid->column('score_1', 'U1')->editable()->sortable();
        $grid->column('score_2', 'U2')->editable()->sortable();
        $grid->column('score_3', 'U3')->editable()->sortable();
        $grid->column('score_4', 'U4')->editable()->sortable();
        $grid->column('score_5', 'U5')->editable()->sortable();
        $grid->column('tot_units_score', 'U Score')->sortable();
        $grid->column('out_of_10', 'Out of 10')->sortable();
        $grid->column('descriptor', 'Descriptor')->sortable();
        $grid->column('project_score', 'Project Score')->editable()->sortable();
        $grid->column('out_of_20', 'Out of 20')->sortable();
        $grid->column('exam_score', 'Exam Score')->editable()->sortable();
        $grid->column('overall_score', 'Overall Score')->editable()->sortable();
        $grid->column('grade_value', 'Grade Value')->editable()->sortable();
        $grid->column('grade_name', 'Grade Value')->sortable();
        $grid->column('score_1_submitted', 'U1 Submitted')
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('score_2_submitted', 'U2 Submitted')
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('score_3_submitted', 'U3 Submitted')
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('score_4_submitted', 'U4 Submitted')
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('score_5_submitted', 'U5 Submitted')
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        $grid->column('exam_score_submitted', 'Exam Submitted')
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->sortable();
        //termly_examination_id
        $grid->column('termly_examination_id', 'Termly Exam')->sortable();

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
        $show = new Show(SecondaryReportCardItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('secondary_subject_id', __('Secondary subject id'));
        $show->field('secondary_report_card_id', __('Secondary report card id'));
        $show->field('average_score', __('Average score'));
        $show->field('generic_skills', __('Generic skills'));
        $show->field('remarks', __('Remarks'));
        $show->field('teacher', __('Teacher'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('term_id', __('Term id'));
        $show->field('academic_class_sctream_id', __('Academic class sctream id'));
        $show->field('score_1', __('Score 1'));
        $show->field('score_2', __('Score 2'));
        $show->field('score_3', __('Score 3'));
        $show->field('score_4', __('Score 4'));
        $show->field('score_5', __('Score 5'));
        $show->field('tot_units_score', __('Tot units score'));
        $show->field('out_of_10', __('Out of 10'));
        $show->field('descriptor', __('Descriptor'));
        $show->field('project_score', __('Project score'));
        $show->field('out_of_20', __('Out of 20'));
        $show->field('exam_score', __('Exam score'));
        $show->field('overall_score', __('Overall score'));
        $show->field('grade_value', __('Grade value'));
        $show->field('grade_name', __('Grade name'));
        $show->field('score_1_submitted', __('Score 1 submitted'));
        $show->field('score_2_submitted', __('Score 2 submitted'));
        $show->field('score_3_submitted', __('Score 3 submitted'));
        $show->field('score_4_submitted', __('Score 4 submitted'));
        $show->field('score_5_submitted', __('Score 5 submitted'));
        $show->field('project_score_submitted', __('Project score submitted'));
        $show->field('exam_score_submitted', __('Exam score submitted'));
        $show->field('termly_examination_id', __('Termly examination id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondaryReportCardItem());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('secondary_subject_id', __('Secondary subject id'));
        $form->number('secondary_report_card_id', __('Secondary report card id'));
        $form->decimal('average_score', __('Average score'))->default(0.00);
        $form->textarea('generic_skills', __('Generic skills'));
        $form->textarea('remarks', __('Remarks'));
        $form->text('teacher', __('Teacher'));
        $form->number('administrator_id', __('Administrator id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('term_id', __('Term id'));
        $form->number('academic_class_sctream_id', __('Academic class sctream id'));
        $form->decimal('score_1', __('Score 1'));
        $form->decimal('score_2', __('Score 2'));
        $form->decimal('score_3', __('Score 3'));
        $form->decimal('score_4', __('Score 4'));
        $form->decimal('score_5', __('Score 5'));
        $form->decimal('tot_units_score', __('Tot units score'));
        $form->decimal('out_of_10', __('Out of 10'));
        $form->text('descriptor', __('Descriptor'));
        $form->decimal('project_score', __('Project score'));
        $form->decimal('out_of_20', __('Out of 20'));
        $form->decimal('exam_score', __('Exam score'));
        $form->decimal('overall_score', __('Overall score'));
        $form->decimal('grade_value', __('Grade value'));
        $form->text('grade_name', __('Grade name'));
        $form->text('score_1_submitted', __('Score 1 submitted'))->default('No');
        $form->text('score_2_submitted', __('Score 2 submitted'))->default('No');
        $form->text('score_3_submitted', __('Score 3 submitted'))->default('No');
        $form->text('score_4_submitted', __('Score 4 submitted'))->default('No');
        $form->text('score_5_submitted', __('Score 5 submitted'))->default('No');
        $form->text('project_score_submitted', __('Project score submitted'))->default('No');
        $form->text('exam_score_submitted', __('Exam score submitted'))->default('No');
        $form->number('termly_examination_id', __('Termly examination id'));

        return $form;
    }
}
