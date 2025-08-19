<?php

namespace App\Admin\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\TermlyReportCard;
use App\Models\TheologryStudentReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyTermlyReportCard;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use NumberFormatter;

class TheologryStudentReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Theology Report Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new TheologryStudentReportCard());

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
            ])->orderBy('id', 'Desc')->get()->pluck('name_text', 'id'));


            $u = Admin::user();
            $filter->equal('theology_class_id', 'Filter by class')->select(TheologyClass::where([
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
            $filter->equal('student_id', 'Student')->select(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })->ajax($ajax_url);
        });



        if (!Admin::user()->isRole('dos')) {
            $grid->disableBatchActions();
            $grid->disableActions();
        }


        $grid->disableCreateButton();
        $grid->column('id', __('#ID'))->sortable();

        $grid->column('owner.avatar', __('Photo'))
            ->width(80)
            ->lightbox(['width' => 60, 'height' => 60]);


        $grid->column('academic_year_id', __('Academic year'))->sortable()->hide();
        $grid->column('term_id', __('Term'))->display(function () {
            return $this->term->name;
        });

        $grid->column('student_id', __('Student'))->display(function () {
            return $this->owner->name;
        })->sortable();


        $grid->column('theology_class_id', __('Class'))
            ->display(function () {
                return $this->theology_class->name;
            })->sortable();

        /* $grid->column('stream_id', __('Stream'))
            ->display(function () {
                if ($this->stream == null) {
                    return "-";
                }
                return $this->stream->name;
            })
            ->sortable(); */

        $grid->column('theology_termly_report_card_id', __('Theology termly report card id'))
            ->display(function () {
                if ($this->termly_report_card == null) {
                    $this->delete();
                    return 'N/A';
                }
                return $this->termly_report_card->report_title;
            })->sortable();
        $grid->column('position', __('Position in class'))->display(function ($position) {
            $numFormat = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
            return $numFormat->format($position);
        })->sortable();
        $grid->column('total_students', __('Total Students'))->editable()->sortable();
        $grid->column('total_marks', __('Total marks'))->editable()->sortable();
        $grid->column('average_aggregates', __('Average aggregates'))->editable()->sortable();
        $grid->column('grade', __('Grade'))->editable()->sortable();

        $grid->column('total_aggregates', __('Total aggregates'))->hide()->sortable();
        $grid->column('position', __('Position in class'))->display(function ($position) {
            if ($position < 1) {
                return "-";
            }
            $numFormat = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
            return $numFormat->format(((int)($position)));
        })->editable()->sortable();

        $ent = Admin::user()->ent;
        $year = $ent->dpYear();
        $term = $ent->active_term();
        $reportCard = TermlyReportCard::where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'term_id' => $term->id,
        ])->first();

        $thoReportCard = TheologyTermlyReportCard::where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'term_id' => $term->id,
        ])->first();


        $grid->column('details', 'Marks', 'Expand to view MARKS')->expand(function ($model) use ($reportCard, $thoReportCard) {

            if (!$reportCard)                 return 'No termly report card found.';
            if (!$owner = $this->owner)       return 'No student found.';

            $marks = $reportCard->get_student_marks($owner->id);
            if (!$marks || !count($marks))    return 'No marks found.';

            $rows        = [];
            $totalMarks  = 0;
            $totalAggr   = 0;           // drop if grades are letters

            foreach ($marks as $m) {
                if ($m->subject == null) {
                    $m->delete();
                    continue;
                }

                $rows[] = [
                    $m->subject->subject_name,
                    $m->bot_score . " ({$m->bot_grade})",
                    $m->mot_score . " ({$m->mot_grade})",
                    $m->eot_score . " ({$m->eot_grade})",
                    (int) $m->total_score_display,
                    $m->aggr_name,
                ];

                $totalMarks += $m->total_score_display;
                $totalAggr  += (int) $m->aggr_name;   // remove if letters only
            }

            $table  = new Table(['Subject', 'B.O.T', 'M.O.T', 'E.O.T', 'Score', 'Grade'], $rows);
            $table->style('success');
            $table->striped();
            $table->bordered();
            $table->responsive();
            $table->hover();
            $table->setBordered(true);
            $table->setStriped(true);


            $table2 = null;

            if ($thoReportCard != null) {
                $marks2 = $thoReportCard->get_student_marks($owner->id);
                if ($marks2 != null && count($marks2) > 0) {
                    $table2 = new Table(['Subject', 'B.O.T', 'M.O.T', 'E.O.T', 'Score', 'Grade'], $rows);
                    $rows2 = [];
                    $totalMarks2 = 0;
                    $totalAggr2 = 0;           // drop if grades are letters
                    foreach ($marks2 as $m) {
                        if ($m->subject == null) {
                            $m->delete();
                            continue;
                        }
                        $rows2[] = [
                            $m->subject->name,
                            $m->bot_score . " ({$m->bot_grade})",
                            $m->mot_score . " ({$m->mot_grade})",
                            $m->eot_score . " ({$m->eot_grade})",
                            (int) $m->total_score_display,
                            $m->aggr_name,
                        ];

                        $totalMarks2 += $m->total_score_display;
                        $totalAggr2  += (int) $m->aggr_name;   // remove if letters only
                    }
                    $table2 = new Table(['Subject', 'B.O.T', 'M.O.T', 'E.O.T', 'Score', 'Grade'], $rows2);
                    $table2->style('success');
                    $table2->striped();
                    $table2->bordered();
                    $table2->responsive();
                    $table2->hover();
                    $table2->setBordered(true);
                    $table2->setStriped(true);
                }
            }

            $content = (new Box('Secular Marks', $table))->render();
            if ($table2 != null) {
                $table2->setBordered(true);
                $table2->setStriped(true);
                $content .= (new Box('Theology Marks', $table2))->render();
            }

            return $content;
        },);



        $grid->column('class_teacher_comment', __('Class Teacher Remarks'))->editable()
            ->sortable();
        $grid->column('head_teacher_comment', __('Head Teacher Remarks'))->hide()->editable()->sortable();

        /*         $grid->column('print', __('Print'))->display(function ($m) {
            return '<a target="_blank" href="' . url('print?theo_id=' . $this->id) . '" >print</a>';
        }); */

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
        $show = new Show(TheologryStudentReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('student_id', __('Student id'));
        $show->field('theology_class_id', __('Theology class id'));
        $show->field('theology_termly_report_card_id', __('Theology termly report card id'));
        $show->field('total_students', __('Total students'));
        $show->field('total_aggregates', __('Total aggregates'));
        $show->field('total_marks', __('Total marks'));
        $show->field('position', __('Position'));
        $show->field('class_teacher_comment', __('Class teacher comment'));
        $show->field('head_teacher_comment', __('Head teacher comment'));
        $show->field('class_teacher_commented', __('Class teacher commented'));
        $show->field('head_teacher_commented', __('Head teacher commented'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TheologryStudentReportCard());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->number('student_id', __('Student id'));
        $form->number('theology_class_id', __('Theology class id'));
        $form->number('theology_termly_report_card_id', __('Theology termly report card id'));
        $form->number('total_students', __('Total students'));
        $form->number('total_aggregates', __('Total aggregates'));
        $form->decimal('total_marks', __('Total marks'))->default(0.00);
        $form->number('position', __('Position'));
        $form->textarea('class_teacher_comment', __('Class teacher comment'));
        $form->textarea('head_teacher_comment', __('Head teacher comment'));
        $form->switch('class_teacher_commented', __('Class teacher commented'));
        $form->switch('head_teacher_commented', __('Head teacher commented'));

        return $form;
    }
}
