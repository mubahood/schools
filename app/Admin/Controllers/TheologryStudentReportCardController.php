<?php

namespace App\Admin\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\TheologryStudentReportCard;
use App\Models\TheologyClass;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use NumberFormatter;

class TheologryStudentReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Theology Report Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new TheologryStudentReportCard());

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();


            $filter->equal('academic_year_id', 'Filter by academic year')->select(AcademicYear::where([
                'enterprise_id' => Admin::user()->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name', 'id'));

            $filter->equal('term_id', 'Filter by term')->select(Term::where([
                'enterprise_id' => Admin::user()->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name', 'id'));


            $u = Admin::user();
            $filter->equal('theology_class_id', 'Filter by class')->select(TheologyClass::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('id', 'Desc')
                ->get()->pluck('name_text', 'id'));

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



        $grid->disableBatchActions();
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->column('id', __('#ID'))->sortable();

        $grid->column('owner.avatar', __('Photo'))
            ->width(80)
            ->lightbox(['width' => 60, 'height' => 60]);


        $grid->column('academic_year_id', __('Academic year'))->sortable()->hide();
        $grid->column('term_id', __('Term'))->display(function () {
            return $this->term->name;
        });

        $grid->column('student_id', __('Student'))->display(function () {
            return $this->owner->name;
        })->sortable();


        $grid->column('theology_class_id', __('Class'))
            ->display(function () {
                return $this->theology_class->name;
            })->sortable();
        $grid->column('theology_termly_report_card_id', __('Theology termly report card id'))->hide();
        $grid->column('position', __('Position in class'))->display(function ($position) {
            $numFormat = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
            return $numFormat->format($position);
        })->sortable();
        $grid->column('total_marks', __('Total marks'))->sortable();
        $grid->column('average_aggregates', __('Average aggregates'))->sortable();
        $grid->column('grade', __('Grade'))->sortable();

        $grid->column('total_aggregates', __('Total aggregates'))->hide()->sortable();
        $grid->column('total_students', __('Total students'))->hide()->sortable();
        $grid->column('position', __('Position in class'))->display(function ($position) {
            if ($position < 1) {
                return "-";
            }
            $numFormat = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
            return $numFormat->format(((int)($position)));
        })->sortable();
        $grid->column('class_teacher_comment', __('Class Teacher Remarks'))->editable()->sortable();
        $grid->column('head_teacher_comment', __('Head Teacher Remarks'))->editable()->sortable();

        $grid->column('print', __('Print'))->display(function ($m) {
            return '<a target="_blank" href="' . url('print?theo_id=' . $this->id) . '" >print</a>';
        });

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
        $show = new Show(TheologryStudentReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('student_id', __('Student id'));
        $show->field('theology_class_id', __('Theology class id'));
        $show->field('theology_termly_report_card_id', __('Theology termly report card id'));
        $show->field('total_students', __('Total students'));
        $show->field('total_aggregates', __('Total aggregates'));
        $show->field('total_marks', __('Total marks'));
        $show->field('position', __('Position'));
        $show->field('class_teacher_comment', __('Class teacher comment'));
        $show->field('head_teacher_comment', __('Head teacher comment'));
        $show->field('class_teacher_commented', __('Class teacher commented'));
        $show->field('head_teacher_commented', __('Head teacher commented'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TheologryStudentReportCard());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->number('student_id', __('Student id'));
        $form->number('theology_class_id', __('Theology class id'));
        $form->number('theology_termly_report_card_id', __('Theology termly report card id'));
        $form->number('total_students', __('Total students'));
        $form->number('total_aggregates', __('Total aggregates'));
        $form->decimal('total_marks', __('Total marks'))->default(0.00);
        $form->number('position', __('Position'));
        $form->textarea('class_teacher_comment', __('Class teacher comment'));
        $form->textarea('head_teacher_comment', __('Head teacher comment'));
        $form->switch('class_teacher_commented', __('Class teacher commented'));
        $form->switch('head_teacher_commented', __('Head teacher commented'));

        return $form;
    }
}
