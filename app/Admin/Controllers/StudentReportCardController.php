<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\StudentReportCard;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Row;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use NumberFormatter;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Column;
use Encore\Admin\Widgets\Form as WidgetsForm;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Table;

class StudentReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Students report cards';

    protected function print(Content $content)
    {
        return $content
            ->title('Batch report card printing')
            ->description('Dashboard')
            ->row(function (Row $row) {

                $row->column(4, function (Column $column) {

                    $u = Admin::user();

                    $rows = [];
                    foreach (AcademicClass::where([
                        'enterprise_id' => $u->enterprise_id
                    ])
                        ->orderBy('id', 'Desc')
                        ->get() as $v) {
                        $rows[] = [
                            $v->id,
                            $v->name_text,
                            count($v->report_cards),
                            '<a target="_blank" href="' . url('print?calss_id=' . $v->id) . '">PRINT</a>'
                        ];
                    }

                    $headers = ['Id', 'Class', 'Report cards', 'Print'];


                    $table = new Table($headers, $rows);

                    $box = new Box('Class IDs', $table);

                    $box->style('success');

                    $column->append($box);
                });
            });
    }
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


            $filter->equal('academic_year_id', 'Filter by academic year')->select(AcademicYear::where([
                'enterprise_id' => Admin::user()->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name', 'id'));

            $filter->equal('term_id', 'Filter by term')->select(Term::where([
                'enterprise_id' => Admin::user()->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name', 'id'));


            $u = Admin::user();



            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('student_id', 'Student')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })
                ->ajax($ajax_url);


            $filter->equal('academic_class_id', 'Filter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('id', 'Desc')
                ->get()->pluck('name_text', 'id'));
            $filter->equal('grade', 'Filter by grade')->select([
                '1' => "First grade",
                '2' => "Second grade",
                '3' => "Third grade",
                '4' => "Fourth grade",
                'U' => "U (Failure)",
            ]);
        });



        $grid->disableBatchActions();
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->column('id', __('#ID'))->sortable();
        $grid->column('academic_year_id', __('Academic year'))->sortable()->hide();
        $grid->column('term_id', __('Term id'))->hide();


        $grid->column('owner.avatar', __('Photo'))
            ->width(80)
            ->lightbox(['width' => 60, 'height' => 60]);

        $grid->column('student_id', __('Student'))->display(function () {
            return $this->owner->name;
        });
        $grid->column('academic_class_id', __('Academic'))
            ->display(function () {
                return $this->academic_class->name;
            })->sortable();

        $grid->column('total_marks', __('Total marks'))->sortable();
        $grid->column('average_aggregates', __('Average aggregates'))->sortable();
        $grid->column('grade', __('Grade'))->sortable();

        $grid->column('position', __('Position in class'))->display(function ($position) {
            $numFormat = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
            return $numFormat->format($position);
        })->sortable();

        $grid->column('class_teacher_comment', __('Class Teacher Remarks'))->editable()->sortable();
        $grid->column('head_teacher_comment', __('Head Teacher Remarks'))->editable()->sortable();

        $grid->column('print', __('Print'))->display(function ($m) {
            return '<a target="_blank" href="' . url('print?id=' . $this->id) . '" >print</a>';
        });

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
