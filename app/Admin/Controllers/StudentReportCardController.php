<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\StudentReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;

class StudentReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Students report cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StudentReportCard());


        /*  $grid->header(function ($query) {
            return new Box('Gender ratio', 'Romina Love');
        });
 */
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $u = Admin::user();
            $filter->equal('academic_class_id', 'Filter by class')->select(AcademicClass::where([
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



        $grid->column('id', __('#ID'))->sortable();
        $grid->column('academic_year_id', __('Academic year'))->sortable();
        $grid->column('term_id', __('Term id'));
        $grid->column('student_id', __('Student'))->display(function () {
            return $this->owner->name;
        });
        $grid->column('academic_class_id', __('Academic class id'))
            ->display(function () {
                return $this->academic_class->name;
            })->sortable();
        $grid->column('print', __('Print'))->display(function ($m) {
            return '<a target="_blank" href="' . url('print?id=' . $this->id) . '" >print</a>';
        });
        $grid->column('termly_report_card_id', __('Termly report card id'));

        return $grid;
    }

    /**
     * Make a show builder.q                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(StudentReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('student_id', __('Student id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('termly_report_card_id', __('Termly report card id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StudentReportCard());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->number('student_id', __('Student id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('termly_report_card_id', __('Termly report card id'));

        return $form;
    }
}
