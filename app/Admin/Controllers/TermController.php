<?php

namespace App\Admin\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TermController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Terms';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Term());
        $grid->model()
            ->orderBy('id', 'Desc')
            ->where(
                [
                    'enterprise_id' => Admin::user()->enterprise_id,
                    'academic_year_id' => Admin::user()->ent->dp_year,
                ]
            );

        $grid->disableBatchActions();

        $grid->column('id', __('Term ID'))->sortable();
        $grid->column('academic_year_id', __('Academic year'))
            ->display(function ($t) {
                return $this->academic_year->name;
            });
        $grid->column('name', __('Term'))->display(function ($t) {
            return "Term $t";
        });
        $grid->column('starts', __('Starts'));
        $grid->column('ends', __('Ends'));
        $grid->column('details', __('Details'))->hide();
        $grid->column('is_active', __('Status'))->display(function ($is_active) {
            if ($is_active) {
                return "Active";
            } else {
                return "Not Active";
            }
        })->label();


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
        $show = new Show(Term::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('name', __('Name'));
        $show->field('starts', __('Starts'));
        $show->field('ends', __('Ends'));
        $show->field('details', __('Details'));
        $show->field('is_active', __('Is active'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Term());

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();


        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $form->select('academic_year_id', 'Academic year')
            ->options(
                AcademicYear::where([
                    'enterprise_id' => $u->enterprise_id,
                    'is_active' => 1,
                ])->get()
                    ->pluck('name', 'id')
            )->rules('required');


        $form->select('name', 'Term')
            ->options([
                1 => 'Term 1',
                2 => 'Term 2',
                3 => 'Term 3',
            ])->rules('required');



        $form->date('starts', __('Term Starts'))->default(date('Y-m-d'))->required();
        $form->date('ends', __('Term Ends'))->required();
        $form->textarea('details', __('Details'));
        $form->radio('is_active', __('is_active'))->options([
            1 => 'Set as current term',
            0 => 'Not current term',
        ]);

        return $form;
    }
}
