<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\GradingScale;
use App\Models\StudentReportCard;
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



        // die("done");
        // $x->reports_generate = 'No';
        // $x->reports_include_bot = 'Yes';
        // $x->hm_communication .= '1';
        // $x->save();
        // dd($x);


        $grid = new Grid(new TermlyReportCard());


        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->disableBatchActions();
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');
        $grid->column('id', __('ID'))->sortable();
        $grid->column('academic_year.name', __('Academic Year'));
        $grid->column('term.name', __('Term'))->display(function () {
            return 'Term ' . $this->academic_year->name . " - " . $this->term->name;
        });

        $grid->column('report_title', __('Report title'))->hide();
        $grid->column('marks', __('Marks'))->display(function () {
            return number_format(count($this->mark_records));
        });

        $grid->column('bot', __('B.O.T'))->display(function () {
            $total = count($this->mark_records);
            if ($total == 0) {
                return "0 (0%)";
            }
            $total_sub = count($this->mark_records->where('bot_is_submitted', 'Yes'));
            $pecentage = ($total_sub / $total) * 100;
            return number_format($total_sub) . " (" . number_format($pecentage) . "%)";
        });


        $grid->column('mot', __('M.O.T'))->display(function () {
            $total = count($this->mark_records);
            if ($total == 0) {
                return "0 (0%)";
            }
            $total_mot = count($this->mark_records->where('mot_is_submitted', 'Yes'));
            $pecentage = ($total_mot / $total) * 100;
            return number_format($total_mot) . " (" . number_format($pecentage) . "%)";
        });

        $grid->column('eot', __('E.O.T'))->display(function () {
            $total = count($this->mark_records);
            if ($total == 0) {
                return "0 (0%)";
            }
            $total_mot = count($this->mark_records->where('eot_is_submitted', 'Yes'));
            $pecentage = ($total_mot / $total) * 100;
            return number_format($total_mot) . " (" . number_format($pecentage) . "%)";
        });
        $grid->column('report_cards_count', __('Report cards'))->display(function () {
            $table = (new StudentReportCard())->getTable();
            $sql = "SELECT COUNT(*) as count FROM $table WHERE termly_report_card_id = $this->id";
            $count = \DB::select($sql);
            return $count[0]->count;
        });

        $grid->column('has_beginning_term', __('Has beginning term'))->bool()->hide();
        $grid->column('has_mid_term', __('Has mid term'))->bool()->hide();
        $grid->column('has_end_term', __('Has end term'))->bool()->hide();

        /* Generate reports for which classes column will be added*/

        $grid->column('classes', __('Generate Reports for Classes'))->display(function ($classes) {
            if ($classes ==  null || !is_array($classes)) {
                return '';
            }
            //display classes names seperated by comma
            $db_classes = AcademicClass::whereIn('id', $classes)->get();
            $text = '';
            $isFirst = true;
            foreach ($db_classes as $class) {
                if (!$isFirst) {
                    $text .= ', ';
                }
                $text .= $class->short_name;
                $isFirst = false;
            }
            return $text;
        });
        //regenerate reports for selected classes button

        $grid->column('regenerate', __('Regenerate REPORT'))->display(function () {
            return '<a class="btn btn-sm btn-primary" target="_blank" href="' . url('generate-report-cards?id=' . $this->id) . '" >RE-GENERATE REPORT</a>';
        });
        $grid->column('regenerate-pdf', __('Regenerate PDFs'))->display(function () {
            return '<a class="btn btn-sm btn-primary" target="_blank" href="' . url('generate-report-cards-pdf?id=' . $this->id) . '" >RE-GENERATE PDFs</a>';
        });


        /* $grid->column('print', __('Print'))->display(function ($m) {
            $d = '<a class="btn btn-sm btn-info" target="_blank" href="' . url('generate-report-cards?id=' . $this->id) . '" >BULK PDFs GENERATE</a><br>';
            return $d;
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
        //$x = TermlyReportCard::find(16);
        /*  TermlyReportCard::do_generate_class_teacher_comment($x);
        dd($x); */
        // $x->generate_marks = 'Yes';
        // TermlyReportCard::do_generate_marks($x);
        // die('termly-report-cards');
        // $x->positioning_type = 'Stream';
        // $x->reports_generate = 'Yes';
        // //TermlyReportCard::do_generate_positions($x);
        // TermlyReportCard::do_reports_generate($x);
        // dd("done");
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
        $form->divider('Basic Information');
        foreach (GradingScale::where([])
            ->orderBy('id', 'DESC')
            ->get() as $v) {
            $scales[$v->id] =  $v->name;
        }

        if ($form->isCreating()) {
            $form->select('term_id', __('Term'))->options($terms)
                ->creationRules(['required']);
        } else {
            $form->select('term_id', __('Term'))->options($terms)
                ->readOnly();
        }
        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        if ($form->isEditing()) {
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
            });

            $form->divider('Marks Settings');
            $form->radioCard('generate_marks', 'Generate/Re-generate marks for all students?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No')
                ->when('Yes', function (Form $form) {
                    $u = Admin::user();
                    $year = $u->ent->active_academic_year();
                    $academicClasses = AcademicClass::where([
                        'enterprise_id' => $u->enterprise_id,
                        'academic_year_id' => $year->id,
                    ])
                        ->orderBy('id', 'DESC')
                        ->get();
                    $classes = [];
                    foreach ($academicClasses as  $v) {
                        $classes[$v->id] = $v->name_text;
                    }
                    $form->multipleSelect('generate_marks_for_classes', 'Generate marks for which classes?')
                        ->options($classes);
                });

            $form->radioCard('delete_marks_for_non_active', 'Delete marks for non active students?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->decimal('bot_max', __('Max marks for Beginning Of Term exams'))->default(0);
            $form->decimal('mot_max', __('Max marks for Middle Of Term exams'))->default(0);
            $form->decimal('eot_max', __('Max marks for End Of Term exams'))->default(0);
            $form->divider('Marks Display Settings');
            $form->radioCard('display_bot_to_teachers', 'Display Beginning Of Term marks to teachers?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('display_mot_to_teachers', 'Display Middle Of Term marks to teachers?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('display_eot_to_teachers', 'Display End Of Term marks to teachers?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('display_bot_to_others', 'Display Beginning Of Term marks to others?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('display_mot_to_others', 'Display Middle Of Term marks to others?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('display_eot_to_others', 'Display End Of Term marks to others?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->divider('Submission Settings');
            $form->radioCard('can_submit_bot', 'Can teachers submit Beginning Of Term marks?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('can_submit_mot', 'Can teachers submit Middle Of Term marks?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('can_submit_eot', 'Can teachers submit End Of Term marks?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->divider('Reports Settings');
            $form->text('report_title', __('Report title'));
            $form->select('grading_scale_id', __('Grading scale'))->options($scales)->required();

            $form->radioCard('reports_generate', 'Generate reports?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No')
                ->when('Yes', function (Form $form) {
                    $u = Admin::user();
                    $year = $u->ent->active_academic_year();
                    $academicClasses = AcademicClass::where([
                        'enterprise_id' => $u->enterprise_id,
                        'academic_year_id' => $year->id,
                    ])
                        ->orderBy('id', 'DESC')
                        ->get();
                    $classes = [];
                    foreach ($academicClasses as  $v) {
                        $classes[$v->id] = $v->name_text;
                    }
                    $form->multipleSelect('classes', 'Generate reports for which classes?')
                        ->options($classes);

                    $form->radioCard('reports_include_bot', 'Include Beginning Of Term marks in reports?')
                        ->options(['Yes' => 'Yes', 'No' => 'No'])
                        ->default('No');
                    $form->radioCard('reports_include_mot', 'Include Middle Of Term marks in reports?')
                        ->options(['Yes' => 'Yes', 'No' => 'No'])
                        ->default('No');
                    $form->radioCard('reports_include_eot', 'Include End Of Term marks in reports?')
                        ->options(['Yes' => 'Yes', 'No' => 'No'])
                        ->default('No');
                    $form->divider();
                    $form->radioCard('positioning_method', 'Positioning method')
                        ->options(['Average' => 'Use Average Mark', 'Specific' => 'Use Specific'])
                        ->when('Specific', function (Form $form) {
                            $form->radio('positioning_exam', 'Use marks for which exam')
                                ->options([
                                    'bot' => 'Use Beginning Of Term exams marks',
                                    'mot' => 'Use Middle Of Term exams marks',
                                    'eot' => 'Use End Of Term exams marks',
                                ])->rules('required');
                        })
                        ->rules('required');
                });

            $form->radioCard('reports_delete_for_non_active', 'Delete reports for non active students?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');

            $form->radioCard('generate_class_teacher_comment', 'Generate Class Teacher\'s comment?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('generate_head_teacher_comment', 'Generate Head Teacher\'s comment?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('generate_positions', 'Generate positions?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->when('Yes', function (Form $form) {
                    $form->radio('positioning_type', 'Positioning Type')
                        ->options(['Stream' => 'By Stream', 'Class' => 'By Class']);
                })
                ->default('No');


            $form->radio('display_positions', 'Display positions on report cards?')
                ->options([
                    'Yes' => 'System Automated Positions',
                    'Manual' => 'Manual Positions entry space',
                    'No' => 'Do not display positions',
                ])
                ->default('No');
            $form->radio('display_class_teacher_comments', 'Display class teacher\'s comments?')
                ->options([
                    'Yes' => 'System based Class Teacher\'s comments',
                    'Manual' => 'Manual Class Teacher\'s comments entry space',
                ])
                ->default('No');
            /* display_class_other_comments field*/
            $form->radio('display_class_other_comments', 'Display class other\'s comments?')
                ->options([
                    'Yes' => 'Yes',
                    'No' => 'No',
                ])
                ->default('No');

            //class_teacher_comment

            $form->divider('Reports Display Settings');
            $form->radioCard('reports_who_fees_balance', 'Display fees balance?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->radioCard('reports_display_report_to_parents', 'Display reports to parents?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No');
            $form->divider('Reports Template');
            $form->radioCard('reports_template', 'Reports template')
                ->options([
                    'Template_1' => 'Template 1',
                    'Template_2' => 'Template 2',
                    'Template_3' => 'Template 3',
                    'Template_4' => 'Template 4',
                ]);
            $form->radioCard('user_custom_header', 'Use custom header?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No')
                ->when('Yes', function (Form $form) {
                    $form->image('custom_header_image', 'Custom header');
                });
            $form->radioCard('use_background_image', 'Use background image?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No')
                ->when('Yes', function (Form $form) {
                    $form->image('background_image', 'Background image');
                });
            $form->textarea('hm_communication', 'Head Teacher Communication');
            $form->quill('bottom_message', 'Bottom Message');
        }

        return $form;
    }
}
