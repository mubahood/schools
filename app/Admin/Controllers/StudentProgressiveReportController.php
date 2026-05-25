<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\ProgressiveAssessment;
use App\Models\StudentProgressiveReport;
use App\Models\StudentProgressiveReportItem;
use App\Models\Term;
use App\Models\Utils;
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

class StudentProgressiveReportController extends AdminController
{
    protected $title = 'Test Reports';

    // ── DASHBOARD ─────────────────────────────────────────────────────────────
    public function dashboard(Content $content)
    {
        $u   = Admin::user();
        $ent = $u->ent;

        return $content
            ->title('Progressive Assessment — Batch Printing')
            ->description('Generate and print report cards by class')
            ->row(function (Row $row) use ($u, $ent) {

                $assessments = ProgressiveAssessment::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('id', 'desc')->get();

                if ($assessments->isEmpty()) {
                    $row->column(12, function (Column $col) {
                        $col->append((new Box('No Assessments', '<p class="text-muted">No Progressive Assessments have been created yet.</p>'))->render());
                    });
                    return;
                }

                foreach ($assessments as $pa) {
                    $row->column(12, function (Column $col) use ($pa, $u) {

                        $classIds = is_array($pa->classes) ? array_map('intval', $pa->classes) : [];
                        if (empty($classIds)) return;

                        $rows = [];
                        foreach ($classIds as $classId) {
                            $class = AcademicClass::find($classId);
                            if (!$class) continue;

                            $total    = StudentProgressiveReport::where([
                                'progressive_assessment_id' => $pa->id,
                                'academic_class_id'         => $classId,
                            ])->count();

                            $withPdf  = StudentProgressiveReport::where([
                                'progressive_assessment_id' => $pa->id,
                                'academic_class_id'         => $classId,
                            ])->whereNotNull('pdf_url')->where('pdf_url', '!=', '')->count();

                            $printUrl  = url('pa-batch-print?pa_id=' . $pa->id . '&class_id=' . $classId);
                            $genAllUrl = url('pa-generate-all-pdfs?pa_id=' . $pa->id . '&class_id=' . $classId);
                            $listUrl   = url(config('admin.route.prefix') . '/student-progressive-reports?progressive_assessment_id=' . $pa->id . '&academic_class_id=' . $classId);

                            $rows[] = [
                                $classId,
                                '<b>' . $class->name_text . '</b>',
                                $total,
                                $withPdf . ' / ' . $total,
                                '<a target="_blank" href="' . $genAllUrl . '" class="btn btn-xs btn-info">GENERATE ALL PDFs</a>',
                                '<a target="_blank" href="' . $printUrl  . '" class="btn btn-xs btn-primary">BATCH PRINT</a>',
                                '<a href="' . $listUrl . '" class="btn btn-xs btn-default">VIEW LIST</a>',
                            ];
                        }

                        if (!empty($rows)) {
                            $table = new Table(
                                ['Class ID', 'Class', 'Reports', 'PDFs Ready', 'Generate', 'Print', 'View'],
                                $rows
                            );
                            $box = new Box('📋 ' . $pa->title . ' — ' . ($pa->term->name_text ?? ''), $table);
                            $box->style('primary');
                            $col->append($box);
                        }
                    });
                }
            });
    }

    // ── GRID ──────────────────────────────────────────────────────────────────
    protected function grid()
    {
        $grid = new Grid(new StudentProgressiveReport());

        $u    = Admin::user();
        $ent  = $u->ent;
        $year = $ent->active_academic_year();

        $grid->perPages([100, 200, 500]);

        $grid->export(function ($export) {
            $export->filename('progressive-reports.csv');
            $export->except(['created_at','updated_at']);
            $export->originalValue(['total_marks','total_aggregates','position','grade']);
        });

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('academic_class_id')
            ->orderBy('position');

        $grid->disableBatchActions();
        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->filter(function ($filter) use ($u, $year) {
            $filter->disableIdFilter();

            $paOptions = [];
            foreach (ProgressiveAssessment::where('enterprise_id', $u->enterprise_id)
                ->orderBy('id', 'desc')->get() as $pa) {
                $paOptions[$pa->id] = $pa->title . ' (' . ($pa->term->name_text ?? '') . ')';
            }
            $filter->equal('progressive_assessment_id', 'Progressive Assessment')->select($paOptions);

            $filter->equal('academic_class_id', 'Class')->select(
                AcademicClass::where(['enterprise_id' => $u->enterprise_id, 'academic_year_id' => $year->id])
                    ->orderBy('name')->get()->pluck('name_text', 'id')
            );

            $streams = [];
            foreach (AcademicClassSctream::where('enterprise_id', $u->enterprise_id)
                ->orderBy('id', 'desc')->get() as $s) {
                if (!$s->academic_class || $s->academic_class->academic_year_id != $year->id) continue;
                $streams[$s->id] = $s->academic_class->short_name . ' – ' . $s->name;
            }
            $filter->equal('stream_id', 'Stream')->select($streams);

            $filter->equal('term_id', 'Term')->select(
                Term::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('id', 'desc')->get()->pluck('name_text', 'id')
            );

            $filter->equal('grade', 'Grade')->select([
                '1' => 'Grade 1', '2' => 'Grade 2', '3' => 'Grade 3',
                '4' => 'Grade 4', 'U' => 'U (Ungraded)', 'X' => 'X (No marks)',
            ]);
        });

        // ── columns ──────────────────────────────────────────────────────────
        $grid->column('id', '#')->sortable()->hide();

        $grid->column('owner.avatar', 'Photo')
            ->width(60)
            ->lightbox(['width' => 55, 'height' => 55]);

        $grid->column('student_id', 'Student')->display(function () {
            return $this->owner?->name ?? '—';
        })->sortable();

        $grid->column('academic_class_id', 'Class')->display(function () {
            return $this->academic_class?->short_name ?? '—';
        })->sortable();

        $grid->column('stream_id', 'Stream')->display(function () {
            return $this->stream?->name ?? '—';
        })->sortable();

        $grid->column('total_marks', 'Marks')->editable()->sortable();
        $grid->column('total_aggregates', 'Aggregates')->editable()->sortable();
        $grid->column('grade', 'Grade')
            ->editable('select', ['1'=>'Grade 1','2'=>'Grade 2','3'=>'Grade 3','4'=>'Grade 4','U'=>'U','X'=>'X'])
            ->label(['1'=>'success','2'=>'success','3'=>'warning','4'=>'warning','U'=>'danger','X'=>'default'])
            ->sortable();

        $grid->column('position', 'Position')->display(function ($pos) {
            if (!$pos) return '—';
            $sfx = match(($pos % 100 >= 11 && $pos % 100 <= 13) ? 0 : $pos % 10) {
                1=>'st', 2=>'nd', 3=>'rd', default=>'th'
            };
            return $pos . $sfx . ' / ' . ($this->total_students ?? '?');
        })->editable()->sortable();

        $grid->column('class_teacher_comment', 'Class Teacher Remarks')->editable('textarea')->sortable();
        $grid->column('head_teacher_comment',  'Head Teacher Remarks')->editable('textarea')->sortable();

        // ── Expand: subject marks breakdown ──────────────────────────────────
        $grid->column('details', 'Marks Breakdown')->expand(function ($model) {
            $items = $model->items()->with('subject')->orderBy('id')->get();
            if ($items->isEmpty()) return '<em>No subject records found.</em>';

            $pa     = $model->progressive_assessment;
            $n      = $pa ? (int) $pa->number_of_tests : 5;
            $header = ['Subject'];
            for ($i = 1; $i <= $n; $i++) $header[] = 'T' . $i;
            $header = array_merge($header, ['Avg', 'Grd', 'Aggr', 'Remarks']);

            $rows = [];
            foreach ($items as $item) {
                $scores = is_array($item->test_scores)
                    ? $item->test_scores
                    : (json_decode($item->test_scores ?? '', true) ?? []);

                $row = [$item->subject->subject_name ?? '—'];
                for ($i = 1; $i <= $n; $i++) {
                    $s = $scores[$i - 1] ?? null;
                    $v = $s['score'] ?? null;
                    $row[] = ($v !== null && $v > 0) ? $v : ($s['submitted'] === 'Yes' ? '*' : '—');
                }
                $row[] = $item->average_mark > 0 ? $item->average_mark : '—';
                $row[] = $item->grade_name ?? '—';
                $row[] = $item->aggregates ?? '—';
                $row[] = $item->remarks ?? '';
                $rows[] = $row;
            }

            $table = new Table($header, $rows);
            $table->setBordered(true);
            $table->setStriped(true);
            return (new Box($model->owner?->name . ' — Subject Marks', $table))->render();
        });

        // ── Generate/Actions column ───────────────────────────────────────────
        $grid->column('actions_col', 'GENERATE')->display(function () {
            $btn = '';

            // Edit comments
            $editUrl = url(config('admin.route.prefix') . '/student-progressive-reports/' . $this->id . '/edit');
            $btn .= '<a class="btn btn-xs btn-warning mb-1" href="' . $editUrl . '">EDIT</a><br>';

            // Generate PDF
            $genUrl = url('pa-generate-pdf?id=' . $this->id);
            $btn .= '<a class="btn btn-xs btn-info mb-1" target="_blank" href="' . $genUrl . '">GENERATE PDF</a><br>';

            if (!empty($this->pdf_url)) {
                $fileUrl = url('storage/files/' . $this->pdf_url);

                // Print / view
                $printUrl = url('pa-print?id=' . $this->id);
                $btn .= '<a class="btn btn-xs btn-primary mb-1" target="_blank" href="' . $printUrl . '">PRINT</a><br>';

                // Download
                $btn .= '<a class="btn btn-xs btn-success mb-1" target="_blank" href="' . $fileUrl . '">DOWNLOAD PDF</a><br>';

                // WhatsApp
                $owner = $this->owner;
                $phone = $owner?->getParentPhonNumber() ?? null;
                if ($phone && strlen($phone) > 5) {
                    $recipient = Utils::prepare_phone_number($phone);
                    $pa    = $this->progressive_assessment;
                    $date  = now()->format('d. m. Y');
                    $school = $this->ent?->name ?? '';
                    $child  = $owner->name;
                    $term   = $pa?->term?->name ?? '';
                    $yr     = $pa?->term?->academic_year?->name ?? date('Y');

                    $msg  = "{$date},\n\n";
                    $msg .= "Dear Parent / Guardian,\n\n";
                    $msg .= "This is {$school}. Please find below the Progressive Assessment Report Card ";
                    $msg .= "of your child *{$child}* for {$term} {$yr}.\n\n";
                    $msg .= "Kindly click the link below to download:\n{$fileUrl}\n\n";
                    $msg .= "Thank you.";

                    $waUrl = 'https://wa.me/' . $recipient . '?text=' . urlencode($msg);
                    $btn .= '<a class="btn btn-xs btn-success mb-1" target="_blank" href="' . $waUrl . '">WHATSAPP (' . $recipient . ')</a><br>';
                } else {
                    $btn .= '<small class="text-muted">No parent phone</small><br>';
                }
            }

            return $btn;
        });

        $grid->column('is_ready', 'Visible to Parents')
            ->editable('select', ['1' => 'Yes', '0' => 'No'])
            ->using(['1' => 'Yes', '0' => 'No'])
            ->label(['1' => 'success', '0' => 'default'])
            ->sortable();

        $grid->column('date_generated', 'Generated')->display(function ($d) {
            return $d ? date('d M Y H:i', strtotime($d)) : '<span class="text-muted">—</span>';
        })->sortable();

        return $grid;
    }

    // ── Generate PDF (single) ─────────────────────────────────────────────────
    public function generatePdf($id)
    {
        $report = StudentProgressiveReport::findOrFail($id);
        $u      = Admin::user();
        if ($report->enterprise_id !== $u->enterprise_id) abort(403);

        try {
            $name = $report->download_self();
        } catch (\Throwable $e) {
            admin_error('Error', $e->getMessage());
            return redirect()->back();
        }

        return redirect(url('storage/files/' . $name));
    }

    // ── DETAIL ────────────────────────────────────────────────────────────────
    protected function detail($id)
    {
        $show = new Show(StudentProgressiveReport::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('student_text', 'Student');
        $show->field('academic_class_text', 'Class');
        $show->field('total_marks', 'Total Marks');
        $show->field('total_aggregates', 'Total Aggregates');
        $show->field('position', 'Position');
        $show->field('grade', 'Grade');
        $show->field('class_teacher_comment', 'Class Teacher Comment');
        $show->field('head_teacher_comment', 'Head Teacher Comment');
        $show->field('is_ready', 'Visible to Parents');
        $show->field('date_generated', 'Date Generated');
        return $show;
    }

    // ── FORM ─────────────────────────────────────────────────────────────────
    protected function form()
    {
        $form = new Form(new StudentProgressiveReport());
        $u    = Admin::user();

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        $id   = request()->segment(2);
        $item = StudentProgressiveReport::find($id);

        if ($item) {
            $form->display('_student',    'Student')->default($item->owner?->name ?? '');
            $form->display('_class',      'Class')->default($item->academic_class?->name_text ?? '');
            $form->display('_assessment', 'Assessment')->default($item->progressive_assessment?->title ?? '');
            $form->divider();
        }

        $form->number('total_marks',      'Total Marks');
        $form->number('total_aggregates', 'Total Aggregates');
        $form->select('grade', 'Grade')->options([
            '1' => 'Grade 1', '2' => 'Grade 2', '3' => 'Grade 3',
            '4' => 'Grade 4', 'U' => 'U', 'X' => 'X',
        ]);
        $form->number('position',        'Position in Class');
        $form->number('total_students',  'Total Students');

        $form->divider('Comments');
        $form->textarea('class_teacher_comment', 'Class Teacher Comment');
        $form->textarea('head_teacher_comment',  'Head Teacher Comment');
        $form->radioCard('is_ready', 'Visible to Parents')
            ->options(['1' => 'Yes', '0' => 'No'])->default('0');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        return $form;
    }
}
