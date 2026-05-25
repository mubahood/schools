<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\GradingScale;
use App\Models\ProgressiveAssessment;
use App\Models\StudentProgressiveReport;
use App\Models\StudentProgressiveReportItem;
use App\Models\StudentTestRecord;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;

class ProgressiveAssessmentController extends AdminController
{
    protected $title = 'Progressive Assessment';

    // ── STATS DASHBOARD ───────────────────────────────────────────────────────
    public function stats(Content $content)
    {
        $u   = Admin::user();
        $eid = $u->enterprise_id;

        $assessments = ProgressiveAssessment::where('enterprise_id', $eid)
            ->orderBy('id', 'desc')->get();

        return $content
            ->title('Progressive Assessment — Performance Statistics')
            ->description('Test performance analytics by assessment and class')
            ->row(function (Row $row) use ($eid, $assessments) {
                // ── top summary boxes ──────────────────────────────────────
                $totalRecords = StudentTestRecord::where('enterprise_id', $eid)->count();
                $totalReports = StudentProgressiveReport::where('enterprise_id', $eid)->count();
                $totalPdfs    = StudentProgressiveReport::where('enterprise_id', $eid)
                    ->whereNotNull('pdf_url')->where('pdf_url', '!=', '')->count();
                $grade1Count  = StudentProgressiveReport::where('enterprise_id', $eid)
                    ->where('grade', '1')->count();

                $avgMarks = StudentProgressiveReport::where('enterprise_id', $eid)
                    ->where('total_marks', '>', 0)->avg('total_marks');

                $row->column(2, fn($c) => $c->append(view('widgets.box-5', [
                    'title'     => 'Test Records',
                    'icon'      => 'pencil-square-o',
                    'number'    => number_format($totalRecords),
                    'sub_title' => 'Student × Subject slots',
                    'link'      => admin_url('student-test-records'),
                    'is_dark'   => false,
                ])));

                $row->column(2, fn($c) => $c->append(view('widgets.box-5', [
                    'title'     => 'Reports Generated',
                    'icon'      => 'file-text-o',
                    'number'    => number_format($totalReports),
                    'sub_title' => 'Student report cards',
                    'link'      => admin_url('student-progressive-reports'),
                    'is_dark'   => false,
                ])));

                $row->column(2, fn($c) => $c->append(view('widgets.box-5', [
                    'title'     => 'PDFs Ready',
                    'icon'      => 'file-pdf-o',
                    'number'    => number_format($totalPdfs),
                    'sub_title' => 'Generated PDF files',
                    'link'      => admin_url('student-progressive-reports'),
                    'is_dark'   => true,
                ])));

                $row->column(2, fn($c) => $c->append(view('widgets.box-5', [
                    'title'     => 'Grade 1 Students',
                    'icon'      => 'trophy',
                    'number'    => number_format($grade1Count),
                    'sub_title' => 'Top performers',
                    'link'      => admin_url('student-progressive-reports?grade=1'),
                    'is_dark'   => false,
                ])));

                $row->column(2, fn($c) => $c->append(view('widgets.box-5', [
                    'title'     => 'Average Score',
                    'icon'      => 'bar-chart',
                    'number'    => $avgMarks ? round($avgMarks, 1) : '—',
                    'sub_title' => 'Across all assessments',
                    'link'      => admin_url('student-progressive-reports'),
                    'is_dark'   => true,
                ])));

                $row->column(2, fn($c) => $c->append(view('widgets.box-5', [
                    'title'     => 'Assessments',
                    'icon'      => 'tasks',
                    'number'    => $assessments->count(),
                    'sub_title' => 'Total configured',
                    'link'      => admin_url('progressive-assessments'),
                    'is_dark'   => false,
                ])));
            })
            ->row(function (Row $row) use ($eid, $assessments) {
                // ── per-assessment breakdown ───────────────────────────────
                foreach ($assessments as $pa) {
                    $classIds = is_array($pa->classes) ? array_map('intval', $pa->classes) : [];
                    if (empty($classIds)) continue;

                    $row->column(12, function (Column $col) use ($pa, $eid, $classIds) {
                        // Grade distribution for whole PA
                        $gradeCounts = [];
                        foreach (['1','2','3','4','U','X'] as $g) {
                            $gradeCounts[$g] = StudentProgressiveReport::where([
                                'progressive_assessment_id' => $pa->id,
                            ])->where('grade', $g)->count();
                        }
                        $totalStudents = array_sum($gradeCounts);

                        // Build HTML grade bar
                        $gradeColors = ['1'=>'#27ae60','2'=>'#2ecc71','3'=>'#f39c12','4'=>'#e67e22','U'=>'#e74c3c','X'=>'#95a5a6'];
                        $barHtml = '<div style="display:flex;gap:6px;align-items:flex-end;height:60px;margin-bottom:8px;">';
                        foreach ($gradeCounts as $g => $cnt) {
                            $pct  = $totalStudents > 0 ? round($cnt / $totalStudents * 100) : 0;
                            $ht   = max(4, (int)($pct * 0.55));
                            $barHtml .= '<div style="display:flex;flex-direction:column;align-items:center;min-width:40px;">'
                                . '<span style="font-size:11px;font-weight:bold;">' . $cnt . '</span>'
                                . '<div style="background:' . $gradeColors[$g] . ';width:36px;height:' . $ht . 'px;border-radius:3px 3px 0 0;" title="Grade ' . $g . ': ' . $cnt . '"></div>'
                                . '<span style="font-size:10px;color:#555;">Grd ' . $g . '</span>'
                                . '</div>';
                        }
                        $barHtml .= '</div>';

                        // Per-class stats table
                        $rows = [];
                        foreach ($classIds as $classId) {
                            $class = AcademicClass::find($classId);
                            if (!$class) continue;

                            $classReports = StudentProgressiveReport::where([
                                'progressive_assessment_id' => $pa->id,
                                'academic_class_id'         => $classId,
                            ])->get();

                            $classTotal  = $classReports->count();
                            if ($classTotal === 0) continue;

                            $avgM = $classReports->where('total_marks', '>', 0)->avg('total_marks');
                            $avgA = $classReports->where('total_aggregates', '>', 0)->avg('total_aggregates');

                            $topReport = $classReports->where('position', 1)->first();
                            $topName   = $topReport?->owner?->name ?? '—';

                            $gDist = [];
                            foreach (['1','2','3','4','U','X'] as $g) {
                                $n = $classReports->where('grade', $g)->count();
                                $gDist[] = $n > 0
                                    ? '<span style="background:' . $gradeColors[$g] . ';color:#fff;padding:1px 5px;border-radius:3px;font-size:11px;">'
                                      . 'G' . $g . ':' . $n . '</span>'
                                    : '';
                            }
                            $gDistHtml = implode(' ', array_filter($gDist));

                            // Test completion rate
                            $n = $pa->number_of_tests;
                            $totalSlots = $classTotal * $n;  // simplified; use test records count
                            $filledSlots = StudentTestRecord::where([
                                'progressive_assessment_id' => $pa->id,
                                'academic_class_id'         => $classId,
                            ])->where('average_score', '>', 0)->count();
                            $completion = $totalSlots > 0 ? round($filledSlots / max(1, StudentTestRecord::where([
                                'progressive_assessment_id' => $pa->id,
                                'academic_class_id'         => $classId,
                            ])->count()) * 100) . '%' : '0%';

                            $printUrl = url('pa-batch-print?pa_id=' . $pa->id . '&class_id=' . $classId);
                            $listUrl  = admin_url('student-progressive-reports?progressive_assessment_id=' . $pa->id . '&academic_class_id=' . $classId);

                            $rows[] = [
                                '<b>' . $class->short_name . '</b>',
                                $classTotal,
                                $avgM  ? round($avgM, 1)  : '—',
                                $avgA  ? round($avgA, 1)  : '—',
                                $topName,
                                $gDistHtml,
                                $completion,
                                '<a href="' . $listUrl  . '" class="btn btn-xs btn-default">View</a> '
                                . '<a href="' . $printUrl . '" class="btn btn-xs btn-primary" target="_blank">Print</a>',
                            ];
                        }

                        if (empty($rows)) return;

                        $table = new Table(
                            ['Class', 'Students', 'Avg Marks', 'Avg Aggr', 'Top Student', 'Grade Distribution', 'Marks Entered', 'Actions'],
                            $rows
                        );
                        $table->setBordered(true);
                        $table->setStriped(true);

                        $termName = $pa->term?->name_text ?? '';
                        $boxBody  = $barHtml . $table->render();
                        $col->append((new Box(
                            $pa->title . ($termName ? ' — ' . $termName : ''),
                            $boxBody
                        ))->style('info')->render());
                    });
                }

                if ($assessments->isEmpty()) {
                    $row->column(12, fn($c) => $c->append(
                        (new Box('No Data', '<p class="text-muted p-3">No progressive assessments have been created yet. <a href="' . admin_url('progressive-assessments/create') . '">Create one now →</a></p>'))->render()
                    ));
                }
            })
            ->row(function (Row $row) use ($eid, $assessments) {
                // ── subject performance per PA ─────────────────────────────
                foreach ($assessments as $pa) {
                    $reports = StudentProgressiveReport::where('progressive_assessment_id', $pa->id)->get();
                    if ($reports->isEmpty()) continue;

                    $reportIds = $reports->pluck('id');
                    $items = StudentProgressiveReportItem::whereIn('student_progressive_report_id', $reportIds)
                        ->with('subject')->get();

                    // Group by subject
                    $bySubject = [];
                    foreach ($items as $item) {
                        $sName = $item->subject?->subject_name ?? 'Unknown';
                        if (!isset($bySubject[$sName])) $bySubject[$sName] = ['marks'=>[], 'aggrs'=>[]];
                        if ($item->average_mark > 0) $bySubject[$sName]['marks'][] = $item->average_mark;
                        if ($item->aggregates > 0)   $bySubject[$sName]['aggrs'][] = $item->aggregates;
                    }

                    if (empty($bySubject)) continue;

                    $sRows = [];
                    foreach ($bySubject as $sName => $data) {
                        $marks = $data['marks'];
                        $aggrs = $data['aggrs'];
                        $avg   = count($marks) > 0 ? round(array_sum($marks) / count($marks), 1) : '—';
                        $hi    = count($marks) > 0 ? max($marks) : '—';
                        $lo    = count($marks) > 0 ? min($marks) : '—';
                        $avgAg = count($aggrs) > 0 ? round(array_sum($aggrs) / count($aggrs), 1) : '—';
                        $sRows[] = [$sName, count($marks), $avg, $hi, $lo, $avgAg];
                    }

                    usort($sRows, fn($a, $b) => $b[2] <=> $a[2]);

                    $row->column(12, function (Column $col) use ($pa, $sRows) {
                        $table = new Table(['Subject','Students','Avg Mark','Highest','Lowest','Avg Aggr'], $sRows);
                        $table->setBordered(true);
                        $table->setStriped(true);
                        $col->append((new Box('Subject Performance — ' . $pa->title, $table))->style('success')->render());
                    });
                }
            });
    }

    // ── GRID ─────────────────────────────────────────────────────────────────
    protected function grid()
    {
        $grid = new Grid(new ProgressiveAssessment());

        $u = Admin::user();
        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc');

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->column('id', '#')->sortable();
        $grid->column('title', 'Title')->sortable();
        $grid->column('term.name', 'Term')->display(function () {
            return $this->term ? ($this->term->name_text ?? $this->term->name) : '—';
        });
        $grid->column('number_of_tests', 'Tests')->sortable();
        $grid->column('can_submit_tests', 'Open for Entry')->using(['Yes' => 'Yes', 'No' => 'No'])
            ->label(['Yes' => 'success', 'No' => 'danger']);

        $grid->column('records_count', 'Records')->display(function () {
            return number_format(StudentTestRecord::where('progressive_assessment_id', $this->id)->count());
        });
        $grid->column('reports_count', 'Reports')->display(function () {
            return number_format(StudentProgressiveReport::where('progressive_assessment_id', $this->id)->count());
        });

        $grid->column('classes', 'Classes')->display(function ($val) {
            if (!is_array($val) || empty($val)) return '—';
            return AcademicClass::whereIn('id', $val)->pluck('short_name')->implode(', ');
        });

        $grid->column('quick_links', 'Quick Actions')->display(function () {
            $statsUrl = admin_url('pa-stats?pa_id=' . $this->id);
            $printUrl = admin_url('pa-report-card-printing');
            return '<a href="' . $statsUrl . '" class="btn btn-xs btn-info">Stats</a> '
                . '<a href="' . $printUrl . '" class="btn btn-xs btn-primary">Print</a>';
        });

        return $grid;
    }

    // ── DETAIL ───────────────────────────────────────────────────────────────
    protected function detail($id)
    {
        $show = new Show(ProgressiveAssessment::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('title', 'Title');
        $show->field('term_id', 'Term ID');
        $show->field('number_of_tests', 'Number of Tests');
        $show->field('can_submit_tests', 'Open for Entry');
        $show->field('created_at', 'Created');
        return $show;
    }

    // ── FORM ─────────────────────────────────────────────────────────────────
    protected function form()
    {
        $form = new Form(new ProgressiveAssessment());
        $u    = Admin::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        $terms = [];
        foreach (Term::where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc')->get() as $t) {
            $terms[$t->id] = ($t->academic_year->name ?? '') . ' – ' . $t->name;
        }

        $scales = GradingScale::where('enterprise_id', $u->enterprise_id)->pluck('name', 'id');

        $year    = $u->ent->active_academic_year();
        $classes = [];
        foreach (AcademicClass::where([
            'enterprise_id'    => $u->enterprise_id,
            'academic_year_id' => $year->id,
        ])->orderBy('name')->get() as $c) {
            $classes[$c->id] = $c->name_text;
        }

        $form->divider('Basic Information');

        if ($form->isCreating()) {
            $form->select('term_id', 'Term')->options($terms)->rules('required');
        } else {
            $form->select('term_id', 'Term')->options($terms)->readOnly();
        }

        $form->text('title', 'Assessment Title')->rules('required')
            ->placeholder('e.g. Progressive Assessment – Term 1 2026');
        $form->select('grading_scale_id', 'Grading Scale')->options($scales)->rules('required');
        $form->number('number_of_tests', 'Number of Tests (1–10)')->default(10)
            ->min(1)->max(10)->rules('required|integer|min:1|max:10');
        $form->multipleSelect('classes', 'Classes to Track')->options($classes);

        $form->divider('Submission Settings');
        $form->radioCard('can_submit_tests', 'Allow teachers to enter test marks?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');

        if ($form->isEditing()) {
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
            });

            $form->divider('Generate Records');
            $form->radioCard('generate_records', 'Generate / Re-generate test records for all students?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->radioCard('delete_records_for_non_active', 'Delete records for inactive students?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');

            $form->divider('Generate Reports');
            $form->radioCard('reports_generate', 'Compute / Re-compute reports from current test marks?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->radioCard('generate_positions', 'Generate / Re-generate student positions?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->radio('positioning_type', 'Positioning by')
                ->options(['Class' => 'By Class', 'Stream' => 'By Stream'])->default('Class');
            $form->radioCard('generate_comments', 'Auto-generate class teacher comments?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');

            $form->divider('Report Card Settings');
            $form->radioCard('display_to_parents', 'Make reports visible to parents?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->radioCard('display_positions', 'Show positions on report cards?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes');
            $form->radioCard('display_class_teacher_comments', 'Show class teacher comments?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes');
            $form->textarea('hm_communication', 'Head Teacher Communication');
            $form->textarea('bottom_message', 'Bottom Message / Footer');
        }

        return $form;
    }
}
