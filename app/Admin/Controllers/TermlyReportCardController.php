<?php

namespace App\Admin\Controllers;

use App\Models\GradingScale;
use App\Models\Term;
use App\Models\TermlyReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TermlyReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Termly report cards';

    /**
     * Make a grid builder.
     * 
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new TermlyReportCard());

/*         $x = TermlyReportCard::find(3);
        if($x == null){
            die("not found"); 
        }
        foreach ($x->report_cards as $r) {
            foreach ($r->items as $student_report) { 
                $student_report->delete(); 
                echo($student_report->id."<br>");
            }
            $r->delete();
            echo($r->id."<br>");
        }
        $x->delete();

        dd($x->delete); 
        $x->do_update = 1;
        $x->report_title .= rand(1, 10);
        $x->save(); */



        /*      

        dd("dpme"); */
        /*
        $x = TermlyReportCard::find(1);
        TermlyReportCard::grade_students($x);
        dd("remove");  */
        /*

     
        
        die("Anjane"); */

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->column('id', __('Id'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('has_beginning_term', __('Has beginning term'))->bool();
        $grid->column('has_mid_term', __('Has mid term'))->bool();
        $grid->column('has_end_term', __('Has end term'))->bool();
        $grid->column('report_title', __('Report title'));
        $grid->column('report_cards', __('Report cards'))->display(function () {
            return count($this->report_cards);
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
        $show = new Show(TermlyReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('has_beginning_term', __('Has beginning term'));
        $show->field('has_mid_term', __('Has mid term'));
        $show->field('has_end_term', __('Has end term'));
        $show->field('report_title', __('Report title'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new TermlyReportCard());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('academic_year_id', __('Academic year id'));

        $_terms = Term::where([
            'enterprise_id' => $u->enterprise_id
        ])
            ->orderBy('id', 'DESC')
            ->get();
        $terms = [];
        foreach ($_terms as  $v) {
            $terms[$v->id] = $v->academic_year->name . " - " . $v->name;
        }

        $scales = [];
        foreach (GradingScale::where([])
            ->orderBy('id', 'DESC')
            ->get() as $v) {
            $scales[$v->id] =  $v->name;
        }

        $form->select('term_id', __('Term'))->options($terms)
            ->creationRules(['required', "unique:termly_report_cards"]);
        $form->radio('has_beginning_term', __('Include beginning term exams?'))->options([1 => 'Yes', 0 => 'No'])->required();
        $form->radio('has_mid_term', __('Include Mid term exams?'))->options([1 => 'Yes', 0 => 'No'])->required();
        $form->radio('has_end_term', __('Include End of term exams?'))->options([1 => 'Yes', 0 => 'No'])->required();
        $form->text('report_title', __('Report title'));

        $form->select('grading_scale_id', __('Grading scale'))->options($scales)->required();

        if ($form->isEditing()) {
            $form->radio('do_update', __('Do you want to update all related report cards?'))->options([1 => 'Yes', 0 => 'No'])
                ->default(0);
        }

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();




        return $form;
    }
}
