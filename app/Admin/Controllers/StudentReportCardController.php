<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AcademicYear;
use App\Models\StudentHasClass;
use App\Models\StudentReportCard;
use App\Models\Term;
use App\Models\TermlyReportCard;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

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
                    $ent = $u->enterprise;
                    $term = $ent->dpTerm();
                    $term_id = $term->id;
                    $rows = [];

                    foreach (
                        AcademicClass::where([
                            'enterprise_id' => $u->enterprise_id,
                            'academic_year_id' => $term->academic_year_id
                        ])
                            ->orderBy('id', 'Desc')
                            ->get() as $v
                    ) {



                        $rs = StudentReportCard::where([
                            'term_id' => $term_id,
                            'academic_class_id' => $v->id,
                        ])->get();



                        $rows[] = [
                            $v->id,
                            $v->name_text,
                            count($rs),
                            '<a target="_blank" href="' . url('print?calss_id=' . $v->id) . '&term_id=' . $term_id . '&termly_report_card_id=1">PRINT REPORTS</a>',

                            /*                             '<a target="_blank" href="' . url('print?calss_id=' . $v->id) . '&term_id=' . $term_id . '&termly_report_card_id=1&task=blank">PRINT BLANK</a>' */
                        ];
                    }

                    $headers = ['Id', 'Class', 'Report cards', 'Print'];


                    $table = new Table($headers, $rows);

                    $box = new Box('2nd Term 2023', $table);

                    $box->style('success');

                    $column->append($box);
                });


                $row->column(4, function (Column $column) {
                    $u = Admin::user();
                    $rows = [];

                    foreach (
                        AcademicClass::where([
                            'enterprise_id' => $u->enterprise_id
                        ])
                            ->orderBy('id', 'Desc')
                            ->get() as $v
                    ) {

                        $term_id = 5;
                        $rs = StudentReportCard::where([
                            'term_id' => $term_id,
                            'academic_class_id' => $v->id,
                        ])->get();


                        $rows[] = [
                            $v->id,
                            $v->name_text,
                            count($rs),
                            '<a target="_blank" href="' . url('print?calss_id=' . $v->id) . '&term_id=' . $term_id . '&termly_report_card_id=1">PRINT REPORTS</a>',

                            '<a target="_blank" href="' . url('print?calss_id=' . $v->id) . '&term_id=' . $term_id . '&termly_report_card_id=1&task=blank">PRINT BLANK</a>'
                        ];
                    }

                    $headers = ['Id', 'Class', 'Report cards', 'Print', 'Blank'];


                    $table = new Table($headers, $rows);

                    $box = new Box('2nd Term', $table);

                    $box->style('success');

                    $column->append($box);
                });


                $row->column(4, function (Column $column) {

                    $u = Admin::user();
                    $rows = [];

                    foreach (
                        AcademicClass::where([
                            'enterprise_id' => $u->enterprise_id
                        ])
                            ->orderBy('id', 'Desc')
                            ->get() as $v
                    ) {

                        $term_id = 6;
                        if (Auth::user()->enterprise_id == 9) {
                            $t = Term::where([
                                'enterprise_id' => 9
                            ])->first();
                            $term_id = $t->id;
                        }

                        $rs = StudentReportCard::where([
                            'term_id' => $term_id,
                            'academic_class_id' => $v->id,
                        ])->get();


                        $rows[] = [
                            $v->id,
                            $v->name_text,
                            count($rs),
                            '<a target="_blank" href="' . url('print?calss_id=' . $v->id) . '&term_id=' . $term_id . '&termly_report_card_id=2">PRINT</a>'
                        ];
                    }

                    $headers = ['Id', 'Class', 'Report cards', 'Print'];


                    $table = new Table($headers, $rows);

                    $box = new Box('3RD Term', $table);

                    $box->style('success');

                    $column->append($box);
                });



                $row->column(4, function (Column $column) {

                    $u = Admin::user();
                    $rows = [];

                    foreach (
                        AcademicClass::where([
                            'enterprise_id' => $u->enterprise_id
                        ])
                            ->orderBy('id', 'Desc')
                            ->get() as $v
                    ) {

                        $term_id = 7;
                        if (Auth::user()->enterprise_id == 9) {
                            $t = Term::where([
                                'enterprise_id' => 9
                            ])->first();
                            $term_id = $t->id;
                        }

                        $rs = StudentReportCard::where([
                            'term_id' => $term_id,
                            'academic_class_id' => $v->id,
                        ])->get();


                        $rows[] = [
                            $v->id,
                            $v->name_text,
                            count($rs),
                            '<a target="_blank" href="' . url('print?calss_id=' . $v->id) . '&term_id=' . $term_id . '&termly_report_card_id=3">PRINT</a>',
                            '<a target="_blank" href="' . url('print?calss_id=' . $v->id) . '&term_id=' . $term_id . '&termly_report_card_id=1&task=blank">PRINT BLANK</a>'
                        ];
                    }

                    $headers = ['Id', 'Class', 'Report cards', 'Print', 'Print Blank',];


                    $table = new Table($headers, $rows);

                    $box = new Box('1ST Term - 2023', $table);

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
        /*  
        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        foreach (StudentReportCard::all() as $c) {

            $stream = StudentHasClass::where([
                'academic_class_id' => $c->academic_class_id,
                'administrator_id' => $c->student_id
            ])
                ->orderBy('id', 'desc')
                ->first();

            if ($stream == null) {
                continue;
            } 
            if ($stream->stream_id == 0) {
                continue;
            } 

            $c->stream_id = $stream->stream_id;
            $c->save();  

            echo $c->id."<br>";
        }
 
        $r = TermlyReportCard::find(3);
        $r::grade_students($r);
     
        die("simple test"); */
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

            $u = Admin::user();




            $filter->equal('term_id', 'Filter by term')->select(Term::where([
                'enterprise_id' => Admin::user()->enterprise_id
            ])->orderBy('id', 'Desc')->get()->pluck('name_text', 'id'));




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


            $streams = [];
            foreach (
                AcademicClassSctream::where(
                    [
                        'enterprise_id' => $u->enterprise_id,
                    ]
                )
                    ->orderBy('id', 'desc')
                    ->get() as $ex
            ) {
                $streams[$ex->id] = $ex->academic_class->short_name . " - " . $ex->name;
            }
            $filter->equal('stream_id', 'Filter by Stream')->select($streams);
            $filter->equal('grade', 'Filter by grade')->select([
                '1' => "First grade",
                '2' => "Second grade",
                '3' => "Third grade",
                '4' => "Fourth grade",
                'U' => "U (Failure)",
            ]);
        });



        $grid->disableBatchActions();
        $grid->disableCreateButton();

        $grid->column('id', __('#ID'))
            ->display(function ($id) {
                $u = Admin::user();
                if ($this->owner->enterprise_id != $u->enterprise_id) {
                    $this->delete();
                }
                return $id;
            })
            ->sortable();
        $grid->column('academic_year_id', __('Academic year'))->sortable()->hide();

        $grid->column('owner.avatar', __('Photo'))
            ->width(80)
            ->lightbox(['width' => 60, 'height' => 60]);

        $grid->column('student_id', __('Student'))->display(function () {

            /* if ($this->total_marks < 1) {
                TermlyReportCard::preocess_report_card($this);
            }
            if ($this->total_marks < 1) {
                $this->delete();
                dd("deleted " . $this->id);
            }
            if ($this->owner == null) {
                $this->delete();
                return "-";
            } */

            return $this->owner->name;
        });
        $grid->column('academic_class_id', __('Class'))
            ->display(function () {
                return $this->academic_class->name_text;
            })->sortable();

        $grid->column('stream_id', __('Stream'))
            ->display(function () {
                if ($this->stream == null) {
                    return "-";
                }
                return $this->stream->name;
            })
            ->sortable();

        $grid->column('total_marks', __('Total marks'))->editable()->sortable();
        $grid->column('average_aggregates', __('Average aggregates'))->editable()->sortable();
        $grid->column('grade', __('Grade'))->editable()->sortable();

        $grid->column('position', __('Position in class'))->display(function ($position) {
            $numFormat = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
            return $numFormat->format($position);
        })->editable()->sortable();
        $grid->column('total_students', __('Total Students'))->editable()->sortable();

        $grid->column('class_teacher_comment', __('Class Teacher Remarks'))->editable('textarea')->sortable();
        $grid->column('head_teacher_comment', __('Head Teacher Remarks'))->editable('textarea')->sortable();
        $grid->column('sports_comment', __('Sports Comment'))->editable('textarea')->sortable();
        $grid->column('mentor_comment', __('Mentor\'s Comment'))->editable('textarea')->sortable();
        $grid->column('nurse_comment', __('Nurse\'s Comment'))->editable('textarea')->sortable();



        $grid->column('print', __('GENERATE'))->display(function ($m) {
            $d = '<a class="btn btn-xs btn-primary" style="margin-bottom: 5px;" target="_blank" href="' . url('print?id=' . $this->id) . '" >PRINT</a><br>';
            $d .= '<a class="btn btn-xs btn-info" style="margin-bottom: 5px;" target="_blank" href="' . url('generate-report-card?id=' . $this->id) . '" >GENERATE PDF NOW</a><br>';
            if (
                ($this->pdf_url != null) &&
                strlen($this->pdf_url) > 3
            ) {
                $url = url('storage/files/' . $this->pdf_url);
                $d .= '<a class="btn btn-xs btn-success" style="margin-bottom: 5px;" target="_blank" href="' . $url . '" >DOWNLOAD PDF</a>';

                $d .= '<br><a class="btn btn-xs btn-warning" style="margin-bottom: 5px;" target="_blank" href="' . url('student-report-card-items?student_report_card_id=' . $this->id) . '" >EDIT</a>';
                //add send email button
                $d .= '<br><a class="btn btn-xs btn-danger" style="margin-bottom: 5px;" target="_blank" href="' . url('send-report-card?task=email&id=' . $this->id) . '" >SEND EMAIL</a>';
                //add send sms
                $d .= '<br><a class="btn btn-xs btn-danger" style="margin-bottom: 5px;" target="_blank" href="' . url('send-report-card?task=sms&id=' . $this->id) . '" >SEND SMS</a>';
            }


            return $d;
        });
        $grid->column('is_ready', __('Ready for parent view'))->editable('select', ['No' => 'No', 'Yes' => 'Yes'])->sortable();
        $grid->column('created_at', __('DATE'))->sortable();
        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                $t = Term::find($term_id);
                if ($t == null) {
                    return $term_id;
                }

                return $t->name_text;
            })->sortable();
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

        $id = Request::segment(2);
        $item = StudentReportCard::find($id);
        if ($item == null) {
            $id = Request::segment(1);
            $item = StudentReportCard::find($id);
        }
        if ($item == null) {
            die("Item not found.");
        }

        $form->hidden('enterprise_id', __('Enterprise id'));
        $form->display('_term_id', __('Term'))->default($item->term->name_text);
        $form->display('_student_id', __('Student'))->default($item->owner->name);
        $form->display('_academic_class_id', __('Class'))->default($item->academic_class->name);
        $form->display('_termly_report_card_id', __('Report card'))->default($item->termly_report_card->report_title);


        $form->divider();
        $form->decimal('total_marks')->rules('required');
        $form->decimal('total_aggregates')->rules('required');
        $form->decimal('average_aggregates')->rules('required');
        $form->decimal('grade')->rules('required');
        $form->select('grade', 'Grade')->options([
            1 => 'Grade 1',
            2 => 'Grade 2',
            3 => 'Grade 3',
            4 => 'Grade 4',
            5 => 'Failure - F',
            6 => 'Uganda pass - U',
        ])->rules('required');
        $form->decimal('position', 'Position in class')->rules('required');
        $form->decimal('total_students', 'Total students in class')->rules('required');
        $form->text('class_teacher_comment', 'Class teacher\'s comment');
        $form->text('head_teacher_comment', 'Head teacher\'s comment');
        $form->text('sports_comment');
        $form->text('mentor_comment');
        $form->text('nurse_comment');




        return $form;
    }
}
