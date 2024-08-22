<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassFee;
use App\Models\Term;
use App\Models\TheologyClass;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AcademicClassFeeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School fees - billing';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        /*         $fee = AcademicClassFee::find(42);
        $fee->academic_class_id = 1;
        $fee->name .= '.';
        $fee->amount = 20000;
        $fee->save();   */

        // $fee = AcademicClassFee::find(117);
        // AcademicClassFee::process_bill($fee);
        // dd($fee);

        $grid = new Grid(new AcademicClassFee());
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();

            $filter->equal('academic_class_id', 'Fliter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()
                ->pluck('name_text', 'id'));
            $filter->group('amount', "Fliter by amount", function ($group) {
                $group->gt('Greater than');
                $group->lt('Less than');
                $group->equal('Equal to');
            });
        });


        $grid->disableBatchActions();
        $grid->column('id', __('#Fee ID'))->sortable()->width(50);
        $grid->column('academic_class_id', __('Class'))->display(function () {
            return $this->academic_class->name_text;
        })->sortable()->width(100);

        $grid->column('name', __('Fee Name'))->sortable();
        $grid->column('amount', __('Amount'))->display(function () {
            return '<span style="float: right;">' . $this->amount_text . '</span>';
        })->sortable()->width(150);

        $terms = [];
        $active_term = 0;
        foreach (
            Term::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->orderBy('id', 'desc')->get() as $key => $term
        ) {
            $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            if ($term->is_active) {
                $active_term = $term->id;
            }
        }
        if (!isset($_GET['due_term_id'])) {
            $grid->model()->where('due_term_id', $active_term);
        }


        $grid->column('due_term_id', __('Due term'))->display(function ($x) {
            $t = Term::find($x);
            if ($t == null) {
                return $x;
            }
            return '<span style="float: right;">Term ' . $t->name_text . '</span>';
        })
            ->sortable()
            ->filter($terms);

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
        $show = new Show(AcademicClassFee::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('name', __('Name'));
        $show->field('amount', __('Amount'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AcademicClassFee());
        $u = Admin::user();
        $year = $u->ent->active_academic_year();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $form->radio('type', "Class type")
            ->options([
                'Secular' => 'Secular class',
                'Theology' => 'Theology class',
            ])
            ->when('Secular', function ($form) {
                $u = Admin::user();
                $year = $u->ent->active_academic_year();
                $form->select('academic_class_id', 'Select Class')
                    ->options(
                        AcademicClass::where([
                            'enterprise_id' => $u->enterprise_id,
                            /* 'academic_year_id' => $year->id */
                        ])
                            ->orderBy('id', 'desc')
                            ->get()
                            ->pluck('name_text', 'id')
                    )->rules('required');
            })
            ->when('Theology', function ($form) {
                $u = Admin::user();
                $year = $u->ent->active_academic_year();
                $form->select('theology_class_id', 'Select Theology Class')
                    ->options(
                        TheologyClass::where([
                            'enterprise_id' => $u->enterprise_id,
                            /*       'academic_year_id' => $year->id */
                        ])
                            ->orderBy('id', 'desc')
                            ->get()
                            ->pluck('name_text', 'id')
                    )->rules('required');
            })
            ->rules('required');


        $form->text('name', __('Fee name'))->rules('required');
        $form->text('amount', __('Amount'))->rules('required|int');

        $terms = [];
        $active_term = 0;
        foreach (
            Term::where(
                'enterprise_id',
                Admin::user()->enterprise_id
            )->orderBy('id', 'desc')->get() as $key => $term
        ) {
            $terms[$term->id] = "Term " . $term->name . " - " . $term->academic_year->name;
            if ($term->is_active) {
                $active_term = $term->id;
            }
        }

        $form->select('due_term_id', 'Due term')->options($terms)
            ->default($active_term)
            ->rules('required');


        $form->radio('cycle', "Billing cycle")
            ->options([
                'Termly' => 'Termly',
                'One time' => 'One time',
            ])->rules('required');


        return $form;
    }
}
