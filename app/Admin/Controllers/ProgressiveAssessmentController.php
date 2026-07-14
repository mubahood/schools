<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\GradingScale;
use App\Models\Subject;
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
            ->with(['term', 'term.academic_year'])
            ->orderBy('id', 'desc')->get();

        return $content
            ->title('Progressive Assessment — Performance Statistics')
            ->description('Test performance analytics by assessment and class')
            ->row(function (Row $row) use ($eid, $assessments) {
                $totalRecords = StudentTestRecord::where('enterprise_id', $eid)->count();
                $totalReports = StudentProgressiveReport::where('enterprise_id', $eid)->count();
                $totalPdfs    = StudentProgressiveReport::where('enterprise_id', $eid)
                    ->whereNotNull('pdf_url')->where('pdf_url', '!=', '')->count();
                $grade1Count  = StudentProgressiveReport::where('enterprise_id', $eid)
                    ->where('grade', '1')->count();
                $avgMarks     = StudentProgressiveReport::where('enterprise_id', $eid)
                    ->where('total_marks', '>', 0)->avg('total_marks');

                foreach ([
                    ['Test Records',       'pencil-square-o', $totalRecords, 'Student × Subject slots',  'student-test-records', false],
                    ['Reports Generated',  'file-text-o',     $totalReports, 'Student report cards',      'student-progressive-reports', false],
                    ['PDFs Ready',         'file-pdf-o',      $totalPdfs,    'Generated PDF files',       'student-progressive-reports', true],
                    ['Grade 1 Students',   'trophy',          $grade1Count,  'Top performers',            'student-progressive-reports?grade=1', false],
                    ['Avg Score',          'bar-chart',       $avgMarks ? round($avgMarks, 1) : '—', 'Across all assessments', 'pa-stats', true],
                    ['Assessments',        'tasks',           $assessments->count(), 'Total configured',  'progressive-assessments', false],
                ] as [$ttl, $ico, $num, $sub, $link, $dark]) {
                    $row->column(2, fn($c) => $c->append(view('widgets.box-5', [
                        'title' => $ttl, 'icon' => $ico, 'number' => is_numeric($num) ? number_format($num) : $num,
                        'sub_title' => $sub, 'link' => admin_url($link), 'is_dark' => $dark,
                    ])));
                }
            })
            ->row(function (Row $row) use ($assessments) {
                $gradeColors = ['1'=>'#27ae60','2'=>'#2ecc71','3'=>'#f39c12','4'=>'#e67e22','U'=>'#e74c3c','X'=>'#95a5a6'];

                foreach ($assessments as $pa) {
                    $classIds = is_array($pa->classes) ? array_map('intval', $pa->classes) : [];
                    if (empty($classIds)) continue;

                    $row->column(12, function (Column $col) use ($pa, $classIds, $gradeColors) {
                        $gradeCounts = [];
                        foreach (['1','2','3','4','U','X'] as $g) {
                            $gradeCounts[$g] = StudentProgressiveReport::where(
                                'progressive_assessment_id', $pa->id
                            )->where('grade', $g)->count();
                        }
                        $totalStudents = array_sum($gradeCounts);

                        $barHtml = '<div style="display:flex;gap:6px;align-items:flex-end;height:60px;margin-bottom:8px;">';
                        foreach ($gradeCounts as $g => $cnt) {
                            $pct   = $totalStudents > 0 ? round($cnt / $totalStudents * 100) : 0;
                            $ht    = max(4, (int)($pct * 0.55));
                            $barHtml .= '<div style="display:flex;flex-direction:column;align-items:center;min-width:40px;">'
                                . '<span style="font-size:11px;font-weight:bold;">' . $cnt . '</span>'
                                . '<div style="background:' . $gradeColors[$g] . ';width:36px;height:' . $ht . 'px;border-radius:3px 3px 0 0;" title="Grade ' . $g . ': ' . $cnt . '"></div>'
                                . '<span style="font-size:10px;color:#555;">Grd ' . $g . '</span>'
                                . '</div>';
                        }
                        $barHtml .= '</div>';

                        $rows = [];
                        foreach ($classIds as $classId) {
                            $class = AcademicClass::find($classId);
                            if (!$class) continue;

                            $classReports = StudentProgressiveReport::where([
                                'progressive_assessment_id' => $pa->id,
                                'academic_class_id'         => $classId,
                            ])->get();
                            if ($classReports->isEmpty()) continue;

                            $classTotal = $classReports->count();
                            $avgM = $classReports->where('total_marks', '>', 0)->avg('total_marks');
                            $avgA = $classReports->where('total_aggregates', '>', 0)->avg('total_aggregates');
                            $topName = $classReports->where('position', 1)->first()?->owner?->name ?? '—';

                            $gDist = [];
                            foreach (['1','2','3','4','U','X'] as $g) {
                                $n = $classReports->where('grade', $g)->count();
                                if ($n > 0) $gDist[] = '<span style="background:' . $gradeColors[$g] . ';color:#fff;padding:1px 5px;border-radius:3px;font-size:11px;">G' . $g . ':' . $n . '</span>';
                            }

                            $filledSlots = StudentTestRecord::where([
                                'progressive_assessment_id' => $pa->id,
                                'academic_class_id'         => $classId,
                            ])->where('average_score', '>', 0)->count();
                            $totalSlots = StudentTestRecord::where([
                                'progressive_assessment_id' => $pa->id,
                                'academic_class_id'         => $classId,
                            ])->count();
                            $completion = $totalSlots > 0 ? round($filledSlots / $totalSlots * 100) . '%' : '0%';

                            $printUrl = url('pa-batch-print?pa_id=' . $pa->id . '&class_id=' . $classId);
                            $listUrl  = admin_url('student-progressive-reports?progressive_assessment_id=' . $pa->id . '&academic_class_id=' . $classId);
                            $marksUrl = admin_url('student-test-records?progressive_assessment_id=' . $pa->id . '&academic_class_id=' . $classId);

                            $rows[] = [
                                '<b>' . $class->short_name . '</b>',
                                $classTotal,
                                $avgM  ? round($avgM, 1)  : '—',
                                $avgA  ? round($avgA, 1)  : '—',
                                $topName,
                                implode(' ', $gDist),
                                $completion,
                                '<a href="' . $marksUrl . '" class="btn btn-xs btn-warning">Marks</a> '
                                . '<a href="' . $listUrl  . '" class="btn btn-xs btn-default">Reports</a> '
                                . '<a href="' . $printUrl . '" class="btn btn-xs btn-primary" target="_blank">Print</a>',
                            ];
                        }

                        if (empty($rows)) return;

                        $table = new Table(
                            ['Class','Students','Avg Marks','Avg Aggr','Top Student','Grade Distribution','Marks Entered','Actions'],
                            $rows
                        );
                        $table->setBordered(true)->setStriped(true);

                        $termName = $pa->term?->name_text ?? '';
                        $col->append((new Box(
                            $pa->title . ($termName ? ' — ' . $termName : ''),
                            $barHtml . $table->render()
                        ))->style('info')->render());
                    });
                }

                if ($assessments->isEmpty()) {
                    $row->column(12, fn($c) => $c->append(
                        (new Box('No Data', '<p class="text-muted p-3">No progressive assessments yet. <a href="' . admin_url('progressive-assessments/create') . '">Create one →</a></p>'))->render()
                    ));
                }
            })
            ->row(function (Row $row) use ($assessments) {
                foreach ($assessments as $pa) {
                    $reports   = StudentProgressiveReport::where('progressive_assessment_id', $pa->id)->get();
                    if ($reports->isEmpty()) continue;
                    $reportIds = $reports->pluck('id');
                    $items     = StudentProgressiveReportItem::whereIn('student_progressive_report_id', $reportIds)
                        ->with('subject')->get();

                    $bySubject = [];
                    foreach ($items as $item) {
                        $sName = $item->subject?->subject_name ?? 'Unknown';
                        if (!isset($bySubject[$sName])) $bySubject[$sName] = ['marks'=>[],'aggrs'=>[]];
                        if ($item->average_mark > 0) $bySubject[$sName]['marks'][] = $item->average_mark;
                        if ($item->aggregates > 0)   $bySubject[$sName]['aggrs'][] = $item->aggregates;
                    }
                    if (empty($bySubject)) continue;

                    $sRows = [];
                    foreach ($bySubject as $sName => $data) {
                        $marks   = $data['marks'];
                        $aggrs   = $data['aggrs'];
                        $avg     = count($marks) ? round(array_sum($marks) / count($marks), 1) : '—';
                        $hi      = count($marks) ? max($marks) : '—';
                        $lo      = count($marks) ? min($marks) : '—';
                        $avgAg   = count($aggrs) ? round(array_sum($aggrs) / count($aggrs), 1) : '—';
                        $sRows[] = [$sName, count($marks), $avg, $hi, $lo, $avgAg];
                    }
                    usort($sRows, fn($a, $b) => $b[2] <=> $a[2]);

                    $row->column(12, function (Column $col) use ($pa, $sRows) {
                        $table = new Table(['Subject','Students','Avg Mark','Highest','Lowest','Avg Aggr'], $sRows);
                        $table->setBordered(true)->setStriped(true);
                        $col->append((new Box('Subject Performance — ' . $pa->title, $table))->style('success')->render());
                    });
                }
            });
    }

    // ── GRID ─────────────────────────────────────────────────────────────────
    protected function grid()
    {
        $grid = new Grid(new ProgressiveAssessment());
        $u    = Admin::user();
        $eid  = $u->enterprise_id;

        // Pre-load all class names to avoid N×1 queries in the 'classes' column
        $allClasses = AcademicClass::where('enterprise_id', $eid)->pluck('short_name', 'id');

        $grid->model()
            ->where('enterprise_id', $eid)
            ->with(['term', 'term.academic_year', 'grading_scale'])
            ->withCount([
                'test_records',
                'reports',
                'reports as pdf_reports_count' => fn($q) => $q->whereNotNull('pdf_url')->where('pdf_url', '!=', ''),
            ])
            ->orderBy('id', 'desc');

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        // ── Filters ───────────────────────────────────────────────────────
        $grid->filter(function ($filter) use ($eid) {
            $filter->disableIdFilter();

            $terms = [];
            foreach (Term::where('enterprise_id', $eid)->orderBy('id','desc')->get() as $t) {
                $terms[$t->id] = ($t->academic_year->name ?? '') . ' – ' . ($t->name_text ?? $t->name);
            }
            $filter->equal('term_id', 'Term')->select($terms);
            $filter->equal('can_submit_tests', 'Marks Entry')->select(['Yes'=>'Open','No'=>'Closed']);
            $filter->equal('display_to_parents', 'Visible to Parents')->select(['Yes'=>'Yes','No'=>'No']);
        });

        // ── Columns ───────────────────────────────────────────────────────
        $grid->column('id', '#')->sortable()->width(50);

        $grid->column('title', 'Assessment')->display(function () {
            /** @var ProgressiveAssessment $this */
            $url   = admin_url('progressive-assessments/' . $this->id . '/edit');
            $label = '<a href="' . $url . '" style="font-weight:600;color:#1a6b3c;">' . e($this->title) . '</a>';
            $yr    = $this->term?->academic_year?->name ?? '';
            return $label . ($yr ? '<br><small class="text-muted">' . $yr . '</small>' : '');
        });

        $grid->column('term_id', 'Term')->display(function () {
            /** @var ProgressiveAssessment $this */
            return $this->term ? ($this->term->name_text ?? $this->term->name) : '—';
        });

        $grid->column('number_of_tests', 'Tests')
            ->sortable()
            ->display(function ($n) {
                $allowed = is_array($this->allowed_tests) ? $this->allowed_tests : [];
                $open    = !empty($allowed)
                    ? 'T' . implode(', T', $allowed) . ' open'
                    : 'All ' . $n . ' open';
                return $n . '<br><small class="text-muted">' . $open . '</small>';
            });

        $grid->column('can_submit_tests', 'Marks Entry')
            ->using(['Yes' => 'Open', 'No' => 'Closed'])
            ->label(['Yes' => 'success', 'No' => 'danger']);

        $grid->column('classes', 'Classes')->display(function ($val) use ($allClasses) {
            if (!is_array($val) || empty($val)) return '<span class="text-muted">—</span>';
            $names = array_filter(array_map(fn($id) => $allClasses[$id] ?? null, $val));
            return implode(' &nbsp; ', array_map(fn($n) => '<span class="label label-default">' . e($n) . '</span>', $names));
        });

        $grid->column('records_reports', 'Records / Reports')->display(function () {
            /** @var ProgressiveAssessment $this */
            return '<small>'
                . '<b>' . number_format($this->test_records_count ?? 0) . '</b> records<br>'
                . '<b>' . number_format($this->reports_count ?? 0) . '</b> reports &nbsp; '
                . '<b>' . number_format($this->pdf_reports_count ?? 0) . '</b> PDFs'
                . '</small>';
        });

        $grid->column('display_to_parents', 'Parents')
            ->using(['Yes' => 'Visible', 'No' => 'Hidden'])
            ->label(['Yes' => 'success', 'No' => 'default']);

        $grid->column('grading_scale_id', 'Scale')->display(function () {
            /** @var ProgressiveAssessment $this */
            return $this->grading_scale?->name ?? '<span class="text-muted">—</span>';
        });

        $grid->column('actions_extra', 'Actions')->display(function () {
            /** @var ProgressiveAssessment $this */
            $id        = $this->id;
            $marksUrl  = admin_url('student-test-records?progressive_assessment_id=' . $id);
            $reportsUrl= admin_url('student-progressive-reports?progressive_assessment_id=' . $id);
            $statsUrl  = admin_url('pa-stats');
            $printUrl  = admin_url('pa-report-card-printing');
            $sheetsUrl = admin_url('progressive-assessment-sheets?progressive_assessment_id=' . $id);

            return '<div style="white-space:nowrap;">'
                . '<a href="' . $marksUrl   . '" class="btn btn-xs btn-warning"  title="Marks Entry"><i class="fa fa-pencil"></i> Marks</a> '
                . '<a href="' . $reportsUrl . '" class="btn btn-xs btn-default"  title="Reports"><i class="fa fa-file-text-o"></i> Reports</a> '
                . '<a href="' . $statsUrl   . '" class="btn btn-xs btn-info"     title="Stats"><i class="fa fa-bar-chart"></i> Stats</a> '
                . '<a href="' . $printUrl   . '" class="btn btn-xs btn-primary"  title="Print"><i class="fa fa-print"></i> Print</a> '
                . '<a href="' . $sheetsUrl  . '" class="btn btn-xs btn-success"  title="Sheets"><i class="fa fa-file-excel-o"></i> Sheets</a>'
                . '</div>';
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
        $eid  = $u->enterprise_id;

        $form->hidden('enterprise_id')->default($eid);
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        // ── Edit: show live stats banner ───────────────────────────────────
        if ($form->isEditing()) {
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
            });

            $paId = (int) request()->route('progressive_assessment');
            if ($paId) {
                $pa       = ProgressiveAssessment::find($paId);
                $recCount = StudentTestRecord::where('progressive_assessment_id', $paId)->count();
                $rptCount = StudentProgressiveReport::where('progressive_assessment_id', $paId)->count();
                $pdfCount = StudentProgressiveReport::where('progressive_assessment_id', $paId)
                    ->whereNotNull('pdf_url')->where('pdf_url', '!=', '')->count();
                $g1Count  = StudentProgressiveReport::where('progressive_assessment_id', $paId)->where('grade','1')->count();

                $marksUrl   = admin_url('student-test-records?progressive_assessment_id=' . $paId);
                $reportsUrl = admin_url('student-progressive-reports?progressive_assessment_id=' . $paId);
                $statsUrl   = admin_url('pa-stats');
                $printUrl   = admin_url('pa-report-card-printing');
                $sheetsUrl  = admin_url('progressive-assessment-sheets');

                $form->html(
                    '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:14px 18px;margin-bottom:4px;">'
                    . '<div style="font-weight:700;color:#166534;margin-bottom:10px;font-size:13px;">Quick Actions &amp; Live Status</div>'
                    . '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">'
                    . '<a href="' . $marksUrl   . '" class="btn btn-sm btn-warning"  target="_blank"><i class="fa fa-pencil"></i> Marks Entry</a>'
                    . '<a href="' . $reportsUrl . '" class="btn btn-sm btn-default"  target="_blank"><i class="fa fa-file-text-o"></i> View Reports</a>'
                    . '<a href="' . $statsUrl   . '" class="btn btn-sm btn-info"     target="_blank"><i class="fa fa-bar-chart"></i> Performance Stats</a>'
                    . '<a href="' . $printUrl   . '" class="btn btn-sm btn-primary"  target="_blank"><i class="fa fa-print"></i> Print Report Cards</a>'
                    . '<a href="' . $sheetsUrl  . '" class="btn btn-sm btn-success"  target="_blank"><i class="fa fa-file-excel-o"></i> Assessment Sheets</a>'
                    . '</div>'
                    . '<div style="display:flex;gap:20px;flex-wrap:wrap;">'
                    . '<span><b style="font-size:18px;color:#1a6b3c;">' . number_format($recCount) . '</b><br><small style="color:#555;">Test Records</small></span>'
                    . '<span><b style="font-size:18px;color:#1565c0;">' . number_format($rptCount) . '</b><br><small style="color:#555;">Reports</small></span>'
                    . '<span><b style="font-size:18px;color:#7c3aed;">' . number_format($pdfCount) . '</b><br><small style="color:#555;">PDFs Ready</small></span>'
                    . '<span><b style="font-size:18px;color:#b45309;">' . number_format($g1Count)  . '</b><br><small style="color:#555;">Grade 1 Students</small></span>'
                    . '</div>'
                    . '</div>',
                    'Overview'
                );
            }
        }

        // ── Terms & years ──────────────────────────────────────────────────
        $terms = [];
        foreach (Term::where('enterprise_id', $eid)->orderBy('id', 'desc')->get() as $t) {
            $terms[$t->id] = ($t->academic_year->name ?? '') . ' – ' . ($t->name_text ?? $t->name);
        }
        $scales = GradingScale::where('enterprise_id', $eid)->pluck('name', 'id');

        $year    = $u->ent->active_academic_year();
        $classes = [];
        foreach (AcademicClass::where([
            'enterprise_id'    => $eid,
            'academic_year_id' => $year->id,
        ])->orderBy('name')->get() as $c) {
            $classes[$c->id] = $c->name_text;
        }

        // ── Section 1: Basic Information ───────────────────────────────────
        $form->divider('Basic Information');

        if ($form->isCreating()) {
            $form->select('term_id', 'Term')
                ->options($terms)
                ->rules('required')
                ->help('One Progressive Assessment per term is allowed.');
        } else {
            $form->select('term_id', 'Term')->options($terms)->readOnly();
        }

        $form->text('title', 'Assessment Title')
            ->rules('required')
            ->placeholder('e.g. Progressive Assessment – Term 1 2026');

        $form->select('grading_scale_id', 'Grading Scale')
            ->options($scales)
            ->rules('required')
            ->help('The scale used to compute grades for this assessment.');

        $form->number('number_of_tests', 'Number of Tests (1–10)')
            ->default(10)->min(1)->max(10)
            ->rules('required|integer|min:1|max:10')
            ->help('How many test slots appear per subject. Teachers fill scores for each slot individually.');

        $form->multipleSelect('classes', 'Classes to Track')
            ->options($classes)
            ->help('Only students in these classes will appear in this assessment.');

        // ── Excluded subjects (edit only — classes must be saved first) ────
        if ($form->isEditing()) {
            $paId = (int) request()->route('progressive_assessment');
            $paForSubjects = $paId ? ProgressiveAssessment::find($paId) : null;

            $subjectOptions = [];
            if ($paForSubjects && is_array($paForSubjects->classes) && !empty($paForSubjects->classes)) {
                $classIds = array_map('intval', $paForSubjects->classes);

                // Preload class short names to avoid N+1
                $classNames = AcademicClass::whereIn('id', $classIds)->pluck('short_name', 'id');

                $subjects = Subject::whereIn('academic_class_id', $classIds)
                    ->where('show_in_report', 'Yes')
                    ->orderBy('subject_name')
                    ->get();

                // Group by normalised name (case-insensitive, trimmed)
                // to detect cross-class duplicates
                $byNorm = $subjects->groupBy(fn($s) => strtolower(trim($s->subject_name)));

                $rows = [];
                foreach ($byNorm as $norm => $group) {
                    $multiClass = $group->count() > 1;
                    foreach ($group as $s) {
                        $cls   = $classNames[$s->academic_class_id] ?? '';
                        $label = trim($s->subject_name)
                            . ($multiClass && $cls ? ' — ' . $cls : '');
                        $rows[] = ['id' => $s->id, 'label' => $label, 'sort' => strtolower($label)];
                    }
                }
                usort($rows, fn($a, $b) => strcmp($a['sort'], $b['sort']));
                foreach ($rows as $row) {
                    $subjectOptions[$row['id']] = $row['label'];
                }
            }

            if (!empty($subjectOptions)) {
                // Count how many test records already exist for currently-excluded subjects
                $currentExcluded  = is_array($paForSubjects->excluded_subjects) ? $paForSubjects->excluded_subjects : [];
                $existingExcCount = empty($currentExcluded) ? 0
                    : StudentTestRecord::where('progressive_assessment_id', $paId)
                        ->whereIn('subject_id', $currentExcluded)->count();

                $helpText = 'Tick subjects to exclude from this assessment. They will be skipped during record generation and will not appear in report cards or grade calculations.';
                if ($existingExcCount > 0) {
                    $helpText .= ' <strong style="color:#dc2626;">' . number_format($existingExcCount)
                        . ' existing record(s) found for excluded subjects — use "Delete excluded records" below to remove them.</strong>';
                }

                $form->multipleSelect('excluded_subjects', 'Exclude Subjects')
                    ->options($subjectOptions)
                    ->help($helpText);
            } else {
                $form->html(
                    '<div class="alert alert-warning" style="margin:0;">No subjects found. Save your class selections first, then return here to configure subject exclusions.</div>',
                    'Exclude Subjects'
                );
            }
        }

        // ── Section 2: Marks Entry ─────────────────────────────────────────
        $form->divider('Marks Entry Settings');

        $testOpts = [];
        for ($i = 1; $i <= 10; $i++) {
            $testOpts[(string) $i] = 'T' . $i;
        }

        $form->radioCard('can_submit_tests', 'Allow teachers to enter test marks?')
            ->options(['Yes' => 'Open — Teachers can submit', 'No' => 'Closed — No new submissions'])
            ->default('No')
            ->when('Yes', function (Form $form) use ($testOpts) {
                $form->checkbox('allowed_tests', 'Tests open for entry')
                    ->options($testOpts)
                    ->help('Select which tests (T1–T10) teachers can currently fill in. Leave ALL unchecked to open every test up to the configured count.');
            });

        // ── Section 3: Report Card Visibility ─────────────────────────────
        $form->divider('Report Card Display Settings');

        $form->radioCard('display_to_parents', 'Make reports visible to parents on their portal?')
            ->options(['Yes' => 'Yes — Parents can view', 'No' => 'No — Hidden from parents'])
            ->default('No');

        $form->radioCard('display_positions', 'Show student positions on report cards?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default('Yes');

        $form->radioCard('display_class_teacher_comments', 'Show class teacher comment section?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default('Yes');

        $form->textarea('hm_communication', 'Head Teacher Communication')
            ->rows(3)
            ->help('This message appears in the Head Teacher section on every student\'s report card.');

        $form->textarea('bottom_message', 'Footer / Bottom Message')
            ->rows(2)
            ->help('Appears at the very bottom of each report card (e.g. next test date, term dates, school motto).');

        // ── Section 4: Data Management (edit only) ─────────────────────────
        if ($form->isEditing()) {
            $form->divider('Data Management');

            $form->radioCard('generate_records', 'Generate / Re-generate test records for all students?')
                ->options(['Yes' => 'Yes — Run now', 'No' => 'No'])
                ->default('No')
                ->help('Creates missing test records for every active student × subject in the selected classes. Excluded subjects are automatically skipped. Safe to re-run — existing records are not overwritten.');

            $form->radioCard('delete_excluded_records', 'Delete existing records for excluded subjects?')
                ->options(['Yes' => 'Yes — Delete now', 'No' => 'No'])
                ->default('No')
                ->help('Permanently removes all test records AND report items for subjects currently in the exclusion list above. Run this after adding subjects to the exclusion list to clean up already-created records. This action cannot be undone.');

            $form->radioCard('delete_records_for_non_active', 'Remove records for inactive/removed students?')
                ->options(['Yes' => 'Yes — Delete inactive', 'No' => 'No'])
                ->default('No')
                ->help('Deletes test records belonging to students whose status is not active.');

            $form->divider('Compute Reports');

            $form->radioCard('reports_generate', 'Compute / Re-compute student reports from current marks?')
                ->options(['Yes' => 'Yes — Compute now', 'No' => 'No'])
                ->default('No')
                ->help('Calculates averages, grades, and aggregates for each student and saves report cards. Run after all marks are entered.');

            $form->radioCard('generate_positions', 'Generate / Re-generate student positions?')
                ->options(['Yes' => 'Yes — Rank now', 'No' => 'No'])
                ->default('No')
                ->help('Ranks students by total marks within each class or stream. Run after computing reports.');

            $form->radio('positioning_type', 'Rank students by')
                ->options(['Class' => 'Whole Class', 'Stream' => 'Stream / Section'])
                ->default('Class');

            $form->radioCard('generate_comments', 'Auto-generate class teacher comments?')
                ->options(['Yes' => 'Yes — Generate now', 'No' => 'No'])
                ->default('No')
                ->help('Writes a comment on each student\'s report based on their grade and position.');
        }

        return $form;
    }
}
