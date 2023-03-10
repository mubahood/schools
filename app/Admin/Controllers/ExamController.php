<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamHasClass;
use App\Models\Term;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ExamController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Exams';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*$e = Exam::find(1);
        $e->name .= "1";
        $e->save();
        die("|romina");*/

        //Utils::generate_marks(Admin::user()->enterprise_id);

        $grid = new Grid(new Exam());
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->column('id', __('ID'))->sortable();


        $terms = [];
        foreach (Term::where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc')
            ->get() as $v) {
            $terms[$v->id] = $v->name;
        }
        $grid->column('term_id', __('Term'))->display(function () {
            return $this->term->name;
        })->filter($terms);
        $grid->column('type', __('Type'))->filter([
            'B.O.T' => 'Begnining of term exam',
            'M.O.T' => 'Mid of term exam',
            'E.O.T' => 'End of term exam'
        ]);
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

            return round($percentage, 0) . "%";
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
        $show = new Show(Exam::findOrFail($id));

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

        $form = new Form(new Exam());
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
        $form->hidden('marks_generated', __('marks_generated'))->default(0)->value(0)->rules('required');
        $form->text('max_mark', __('Max mark'))->rules('required|max:100')->attribute('type', 'number');

        $form->radio('can_submit_marks', __('Can teachers submit marks?'))->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default(0);

        if ($form->isEditing()) {

            $form->radio('do_update', __('Do you want to update all related marks?'))->options([1 => 'Yes', 0 => 'No'])
                ->default(0);
        }
        $form->multipleSelect('classes')->options(
            AcademicClass::where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'academic_year_id' => $ay->id,
            ])->pluck('name', 'id')
        )->rules('required');



        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();



        return $form;
    }
}
