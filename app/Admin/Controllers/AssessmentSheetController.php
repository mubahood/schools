<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AssessmentSheet;
use App\Models\Term;
use App\Models\TermlyReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AssessmentSheetController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Assessment Sheets';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
/*         $a = AssessmentSheet::find(25);
        $a->title .= '.';
        $a->save();
        dd("done"); */
        
        $grid = new Grid(new AssessmentSheet());
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc');
        $grid->disableBatchActions();

        $grid->column('id', __('Id'))->hide();
        $grid->column('created_at', __('Created'))->hide();


        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                return Term::find($term_id)->name_text;
            });
        $grid->column('title', __('Title'));
        $grid->column('type', __('Type'));
        $grid->column('academic_class_sctream_id', __('Stream'))
            ->display(function ($academic_class_sctream_id) {
                if ($academic_class_sctream_id == null) {
                    return "N/A";
                }
                return AcademicClassSctream::find($academic_class_sctream_id)->name_text;
            });
        $grid->column('academic_class_id', __('Academic class'))
            ->display(function ($academic_class_id) {
                if ($academic_class_id == null) {
                    return "N/A";
                }
                return AcademicClass::find($academic_class_id)->name_text;
            });

        $grid->column('total_students', __('Total students'))->sortable();
        $grid->column('first_grades', __('First grades'))->sortable();
        $grid->column('second_grades', __('Second grades'))->sortable();
        $grid->column('third_grades', __('Third grades'))->sortable();
        $grid->column('fourth_grades', __('Fourth grades'))->sortable();
        $grid->column('x_grades', __('X grades'))->sortable();
        $grid->column('name_of_teacher', __('Name of teacher'))->sortable();

        $grid->column('generate', __('RE-Generate'))
            ->display(function ($generate) {
                $url = url('assessment-sheets-generate?id=' . $this->id);
                //open to new tab
                return '<a href="' . $url . '" target="_blank">Generate pdf</a>';
            });

        //pdf_link
        $grid->column('pdf_link', __('PDF Link'))
            ->display(function ($pdf_link) {
                if ($pdf_link == null) {
                    return "N/A";
                }
                $url = url('storage/'.$pdf_link);
                //open to new tab
                return '<a href="' . $url . '" target="_blank">Download pdf</a>'; 
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
        $show = new Show(AssessmentSheet::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('term_id', __('Term id'));
        $show->field('title', __('Title'));
        $show->field('type', __('Type'));
        $show->field('academic_class_sctream_id', __('Academic class sctream id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('termly_report_card_id', __('Termly report card id'));
        $show->field('total_students', __('Total students'));
        $show->field('first_grades', __('First grades'));
        $show->field('second_grades', __('Second grades'));
        $show->field('third_grades', __('Third grades'));
        $show->field('fourth_grades', __('Fourth grades'));
        $show->field('x_grades', __('X grades'));
        $show->field('name_of_teacher', __('Name of teacher'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AssessmentSheet());


        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);
        $form->radio('type', __('Target'))
            ->rules('required')
            ->options([
                'Class' => 'Class',
                'Stream' => 'Stream',
            ])
            ->when('Class', function (Form $form) {

                $u = Admin::user();
                $classes = [];
                foreach (
                    AcademicClass::where([
                        'enterprise_id' => $u->enterprise_id,
                    ])->orderBy('id', 'desc')->get()
                    as $class
                ) {
                    $classes[$class->id] = $class->name_text;
                }
                //academic_class_id
                $form->select('academic_class_id', __('Select Class'))->options($classes)->rules('required');
            })->when('Stream', function (Form $form) {
                $streams = [];
                $u = Admin::user();
                foreach (
                    AcademicClassSctream::where([
                        'enterprise_id' => $u->enterprise_id,
                    ])->orderBy('id', 'desc')->get()
                    as $stream
                ) {
                    $streams[$stream->id] = $stream->name_text;
                }
                $form->select('academic_class_sctream_id', __('Select Stream'))->options($streams)->rules('required');
            });


        $termly_report_cards = [];
        $u = Admin::user();
        foreach (
            TermlyReportCard::where([
                'enterprise_id' => $u->enterprise_id,
            ])->orderBy('id', 'desc')->get()
            as $termly_report_card
        ) {
            $termly_report_cards[$termly_report_card->id] = $termly_report_card->report_title;
        }
        //select termly_report_card_id
        $form->select('termly_report_card_id', __('Select Termly Report Card'))->options($termly_report_cards)->rules('required');


        return $form;
    }
}
