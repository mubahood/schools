<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Promotion;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class PromotionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Promotion';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Promotion());
        $grid->model()->where([
            'enterprise_id' => Auth::user()->id
        ])->orderBy('id', 'desc');
        $grid->disableBatchActions();
        $grid->disableActions();
        /* $pro = Promotion::find(4);
        $pro->updated_at = Carbon::now();
        $pro->save(); */

        $grid->column('id', __('Id'))->sortable();
        $grid->column('from_class', __('Promoted from'))
            ->display(function () {
                return $this->fromClass->name_text;
            })->sortable();
        $grid->column('to_class', __('Promoted to'))
            ->display(function () {
                return $this->fromClass->name_text;
            })->sortable();
        $grid->column('method', __('Promotion Method'));
        $grid->column('report_card_id', __('Report card'))
            ->display(function () {
                return $this->report->report_title;
            })->sortable();
        $grid->column('mark', __('Pass Mark'))->sortable();
        $grid->column('grade', __('Grade'))->sortable();
        $grid->column('position', __('Position'))->sortable();
        $grid->column('details', __('Details'))->sortable();

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
        $show = new Show(Promotion::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('from_class', __('From class'));
        $show->field('to_class', __('To class'));
        $show->field('method', __('Method'));
        $show->field('student_id', __('Student id'));
        $show->field('report_card_id', __('Report card id'));
        $show->field('mark', __('Mark'));
        $show->field('grade', __('Grade'));
        $show->field('position', __('Position'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new Promotion());

        $u = Auth::user();
        $form->hidden('enterprise_id')->default($u->id);
        $form->hidden('details')->default('-');
        $classes = [];
        foreach (AcademicClass::where([
            'enterprise_id' => $u->enterprise_id
        ])->get() as $key => $class) {
            if (count($class->students) > 0) {
                $classes[$class->id] = $class->name_text;
            }
        }

        $form->select('from_class', 'From class')
            ->options($classes)->load('to_class', '/api/promotion-to-class/?enterprise_id=' . $u->enterprise_id)->rules('required');

        $form->select('to_class', 'To class')->load('report_card_id', '/api/promotion-termly-report-cards/?enterprise_id=' . $u->enterprise_id)->rules('required');
        $form->select('report_card_id', 'Report card')->rules('required');


        $form->radio('method', __('Promotion Method'))->options([
            'Marks' => 'By Total Marks',
            'Grade' => 'By Grade',
            'Position' => 'By Position',
            'Student' => 'By Specific Student',
        ])->when('Marks', function ($f) {
            $f->decimal('mark', __('Minimum mark'))
                ->help('Enter minimum mark a studend need to score to be promoted')
                ->rules('int')->attribute('type', 'number')
                ->rules('required');
        })
            ->when('Grade', function ($f) {
                $f->select('grade', __('Minimum grade'))
                    ->options([
                        1 => 'First grade',
                        2 => 'Second grade',
                        3 => 'Third grade',
                        4 => 'Fourth grade',
                        5 => 'U',
                    ])
                    ->help('Enter minimum grade a studend need to score to be promoted')
                    ->rules('int')->attribute('type', 'number')
                    ->rules('required');
            })
            ->when('Position', function ($f) {
                $f->decimal('position', __('Maximum position'))
                    ->help('Maximum position a student to be promoted.')
                    ->rules('int')->attribute('type', 'number')
                    ->rules('required');
            })
            ->when('Student', function ($f) {
                $u = Auth::user();
                $ajax_url = url(
                    '/api/ajax?'
                        . 'enterprise_id=' . $u->enterprise_id
                        . "&search_by_1=name"
                        . "&search_by_2=id"
                        . "&model=User"
                );
                $f->select('student_id', __('Minimum grade'))
                    ->ajax($ajax_url)
                    ->help('Select student you need to promote')
                    ->rules('int')->attribute('type', 'number')
                    ->rules('required');
            })
            ->rules('required');




        return $form;
    }
}
