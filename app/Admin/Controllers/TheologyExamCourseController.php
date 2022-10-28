<?php

namespace App\Admin\Controllers;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\TheologyClass;
use App\Models\TheologyExam;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TheologyExamCourseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Theology Exams';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*$e = TheologyExam::find(1);
        $e->name .= "1";
        $e->save();
        die("|romina");*/
        $grid = new Grid(new TheologyExam());
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->column('id', __('EXAM #ID'))->hide();
        $grid->column('created_at', __('Created'))->display(function ($x) {
            return Utils::my_date($x);
        })->sortable();
        $grid->column('term.name', __('Term'));
        $grid->column('type', __('Type'));
        $grid->column('name', __('Name'));
        $grid->column('max_mark', __('Max mark'));
        $grid->column('_marks', __('All Marks'))->display(function () {
            return count($this->marks);
        });
        $grid->column('submitted', __('Submitted Marks'))->display(function () {
            return $this->submitted();
        });

        $grid->column('not_submitted', __('Not Submitted Marks'))->display(function () {
            return $this->not_submitted();
        });

        $grid->column('percentage', __('Submitted Marks percentage'))->display(function () {
            $tot = count($this->marks);
            $submitted = $this->submitted();
            $percentage = 0;
            if ($tot > 0) {
                $percentage = ($submitted / $tot) * 100;
            }

            return round($percentage,0) . "%";
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
        $show = new Show(TheologyExam::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('term_id', __('Term id'));
        $show->field('type', __('Type'));
        $show->field('name', __('Name'));
        $show->field('max_mark', __('Max mark'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */

    protected function form()
    {

        $form = new Form(new TheologyExam());
        $u = Admin::user();

        $ay = AcademicYear::where([
            'is_active' => 1,
            'enterprise_id' => $u->enterprise_id,
        ])->first();

        $terms = [];
        if ($ay != null) {
            foreach ($ay->terms as $v) {
                $terms[$v->id] = "Term " . $v->name . " - " . $ay->name;
            }
        }

        if (empty($terms)) {
            admin_error('No term was found in any active academic year.', 'You need to have at least active academic year with a term in it.');
        }


        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $form->select('term_id', 'Term')->options(
            $terms
        )->rules('required');

        $form->select('type', 'Exam')->options([
            'B.O.T' => 'Begnining of term exam',
            'M.O.T' => 'Mid of term exam',
            'E.O.T' => 'End of term exam'
        ])->rules('required');
        $form->text('name', __('Exam Name'))->rules('required');
        $form->text('max_mark', __('Max mark'))->rules('required|max:100')->attribute('type', 'number');

        $form->multipleSelect('classes')->options(
            TheologyClass::where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'academic_year_id' => $ay->id,
            ])->pluck('name', 'id')
        )->rules('required');




        return $form;
    }
}
