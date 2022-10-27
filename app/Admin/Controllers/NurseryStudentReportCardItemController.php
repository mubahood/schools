<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Competence;
use App\Models\NurseryStudentReportCardItem;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NurseryStudentReportCardItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student competences';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new NurseryStudentReportCardItem());
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableCreateButton();


        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $u = Admin::user();
            $filter->equal('academic_class_id', 'Filter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name_text', 'id'));

            $filter->equal('competence_id', 'Filter by competence')->select(Competence::where([
                'enterprise_id' => $u->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name', 'id'));


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


        $grid->column('id', __('#ID'))->sortable();

        $grid->column('student.avatar', __('Photo'))
            ->lightbox(['width' => 60, 'height' => 60]);

        $grid->column('student_id', __('Student'))->display(function () {
            if ($this->student == null) {
                return "-";
            }
            return $this->student->name;
        })->sortable();

        $grid->column('competence_id', __('Competence'))->display(function () {
            return $this->competence->name;
        })->sortable();

        $grid->column('score', __('Score'))->radio([
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
        ])->sortable();
        $grid->column('remarks', __('Remarks'))->editable();



        $grid->column('nursery_termly_report_card_id', __('Nursery termly report card id'))->hide();

        $grid->column('class_id', __('Class'))->display(function () {
            return $this->class->name;
        })->sortable();



        if (Admin::user()->isRole('dos')) {
            $grid->column('teacher.name', __('Teacher'))->sortable();
        } else {
            $grid->column('teacher.name', __('Teacher'))->sortable()->hide();
        }



        $grid->column('updated_at', __('Last Updat'))->display(function ($v) {
            return Utils::my_date_time($v);
        })->sortable();

        Admin::style(".fa-edit, .ie-display{color: black; }");

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
        $show = new Show(NurseryStudentReportCardItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('competence_id', __('Competence id'));
        $show->field('nursery_termly_report_card_id', __('Nursery termly report card id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('student_id', __('Student id'));
        $show->field('teacher_id', __('Teacher id'));
        $show->field('score', __('Score'));
        $show->field('remarks', __('Remarks'));
        $show->field('is_submitted', __('Is submitted'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new NurseryStudentReportCardItem());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('competence_id', __('Competence id'));
        $form->number('nursery_termly_report_card_id', __('Nursery termly report card id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('student_id', __('Student id'));
        $form->number('teacher_id', __('Teacher id'));
        $form->radio('score', __('Score'))->options([
            'A' => 'Sed ut perspiciatis unde omni',
            'B' => 'voluptatem accusantium doloremque',
            'C' => 'dicta sunt explicabo',
            'D' => 'laudantium, totam rem aperiam',
        ]);
        $form->textarea('remarks', __('Remarks'));
        $form->switch('is_submitted', __('Is submitted'));

        return $form;
    }
}
