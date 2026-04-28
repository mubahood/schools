<?php

namespace App\Admin\Controllers;

use App\Exports\EmployeeMonitoringReportExport;
use App\Models\AcademicClass;
use App\Models\EmployeeMonitoringRecord;
use App\Models\EmployeeMonitoringReportRecord;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeMonitoringReportRecordController extends AdminController
{
    protected $title = 'EMT Reports';

    protected function grid()
    {
        $grid = new Grid(new EmployeeMonitoringReportRecord());
        $u = Admin::user();

        $grid->model()->where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc');
        $grid->disableBatchActions();

        $grid->column('id', 'ID')->sortable();
        $grid->column('report_name', 'Report Name')->sortable();
        $grid->column('report_type', 'Type')->display(function ($value) {
            $labels = [
                'individual_teacher' => 'Individual Teacher',
                'subject_wise' => 'Subject Wise',
                'class_wise' => 'Class Wise',
                'term_trend' => 'Term Trend',
            ];
            return $labels[$value] ?? $value;
        });
        $grid->column('status', 'Status')->label([
            'Pending' => 'warning',
            'Completed' => 'success',
            'Skipped' => 'default',
        ]);
        $grid->column('generated_at', 'Generated At')->display(function ($v) {
            return $v ? date('d M Y H:i', strtotime($v)) : '-';
        });

        $grid->column('downloads', 'Downloads')->display(function () {
            $buttons = [];
            if (!empty($this->pdf_path)) {
                $buttons[] = '<a class="btn btn-xs btn-danger" target="_blank" href="' . url('storage/' . $this->pdf_path) . '"><i class="fa fa-file-pdf-o"></i> PDF</a>';
            }
            if (!empty($this->excel_path)) {
                $buttons[] = '<a class="btn btn-xs btn-success" target="_blank" href="' . url('storage/' . $this->excel_path) . '"><i class="fa fa-file-excel-o"></i> Excel</a>';
            }
            return empty($buttons) ? '<span class="text-muted">-</span>' : implode(' ', $buttons);
        });

        $grid->column('generate_report', 'Generate PDF')->display(function () {
            $url = admin_url('employee-monitoring-report-records/' . $this->id . '/generate');
            return '<a class="btn btn-xs btn-primary" target="_blank" href="' . $url . '"><i class="fa fa-cogs"></i> Generate</a>';
        });

        $grid->actions(function ($actions) {
            $url = admin_url('employee-monitoring-report-records/' . $actions->getKey() . '/generate');
            $actions->append('<a href="' . $url . '" target="_blank" title="Generate"><i class="fa fa-cogs"></i></a>');
        });

        return $grid;
    }

    public function generate($id)
    {
        $u = Admin::user();
        $report = EmployeeMonitoringReportRecord::where('enterprise_id', $u->enterprise_id)->findOrFail($id);

        try {
            $records = $this->resolveRecords($report, $u->enterprise_id);

            if ($records->count() < 1) {
                $report->status = 'Skipped';
                $report->error_message = 'No records found for selected report parameters.';
                $report->generated_at = now();
                $report->generated_by = $u->id;
                $report->save();

                return $this->renderGenerateResultPage(
                    $report,
                    'No Records Found',
                    'No monitoring records matched the selected parameters. Adjust filters and generate again.',
                    null,
                    null,
                    false
                );
            }

            $exportRows = $records->map(function (EmployeeMonitoringRecord $row) {
                return [
                    'Date' => $row->monitored_on ? $row->monitored_on->format('Y-m-d') : '',
                    'Term' => optional($row->term)->name_text,
                    'Teacher' => optional($row->employee)->name,
                    'Subject' => optional($row->subject)->subject_name,
                    'Class' => optional($row->academicClass)->name_text,
                    'Time In' => $row->time_in,
                    'Time Out' => $row->time_out,
                    'Hours' => $row->hours,
                    'Monitor Name' => $row->monitor_name,
                    'Monitor Role' => $row->monitor_role,
                    'Status' => $row->status,
                    'Comment' => $row->comment,
                ];
            });

            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', (string) ($report->report_name ?: 'emt-report')));
            $stamp = now()->format('Ymd_His');

            $excelRelativePath = 'reports/emt/' . $slug . '-' . $stamp . '.xlsx';
              $excelStored = Excel::store(new EmployeeMonitoringReportExport($exportRows), $excelRelativePath, 'admin');
              if (!$excelStored) {
                  throw new \Exception('Failed to save Excel report file.');
              }

            $pdfRelativePath = 'reports/emt/' . $slug . '-' . $stamp . '.pdf';
            $enterprise = $report->enterprise;
            $params = (array) $report->parameters;
            $reportTypeLabels = [
                'individual_teacher' => 'Individual Teacher Performance',
                'subject_wise' => 'Subject-wise Performance',
                'class_wise' => 'Class-wise Performance',
                'term_trend' => 'Academic Term Performance Trend',
            ];

            $totalRecords = $records->count();
            $totalHours = round((float) $records->sum(function (EmployeeMonitoringRecord $row) {
                return (float) $row->hours;
            }), 2);
            $avgHours = $totalRecords > 0
                ? round($totalHours / $totalRecords, 2)
                : 0.0;
            $avgDurationMinutes = (int) round((float) $records->avg(function (EmployeeMonitoringRecord $row) {
                if (!empty($row->duration_minutes)) {
                    return (float) $row->duration_minutes;
                }
                return ((float) $row->hours) * 60;
            }));

            $statusBreakdown = [
                'Pending' => $records->where('status', 'Pending')->count(),
                'Completed' => $records->where('status', 'Completed')->count(),
                'Skipped' => $records->where('status', 'Skipped')->count(),
            ];

            $recordsWithTime = $records->filter(function (EmployeeMonitoringRecord $row) {
                return !empty($row->time_in) && !empty($row->time_out);
            })->count();

            $teacherGroups = $records->filter(function (EmployeeMonitoringRecord $row) {
                return !empty($row->employee_id);
            })->groupBy('employee_id')->sortByDesc(function ($group) {
                return $group->count();
            });
            $subjectGroups = $records->filter(function (EmployeeMonitoringRecord $row) {
                return !empty($row->subject_id);
            })->groupBy('subject_id')->sortByDesc(function ($group) {
                return $group->count();
            });
            $classGroups = $records->filter(function (EmployeeMonitoringRecord $row) {
                return !empty($row->academic_class_id);
            })->groupBy('academic_class_id')->sortByDesc(function ($group) {
                return $group->count();
            });

            $topTeacherGroup = $teacherGroups->first();
            $topSubjectGroup = $subjectGroups->first();
            $topClassGroup = $classGroups->first();

            $dateStart = $records->min('monitored_on');
            $dateEnd = $records->max('monitored_on');

            $filtersApplied = [
                'Term' => !empty($params['term_id'])
                    ? optional(Term::find($params['term_id']))->name_text
                    : null,
                'Class' => !empty($params['academic_class_id'])
                    ? optional(AcademicClass::find($params['academic_class_id']))->name_text
                    : null,
                'Subject' => !empty($params['subject_id'])
                    ? optional(Subject::find($params['subject_id']))->subject_name
                    : null,
                'Teacher' => !empty($params['employee_id'])
                    ? optional(User::find($params['employee_id']))->name
                    : null,
                'Date Range' => (!empty($params['from_date']) && !empty($params['to_date']))
                    ? ($params['from_date'] . ' to ' . $params['to_date'])
                    : null,
            ];

            $summary = [
                'report_type_label' => $reportTypeLabels[$report->report_type] ?? $report->report_type,
                'total_records' => $totalRecords,
                'total_hours' => $totalHours,
                'avg_hours' => $avgHours,
                'avg_duration_minutes' => $avgDurationMinutes,
                'records_with_time' => $recordsWithTime,
                'unique_teachers' => $records->pluck('employee_id')->filter()->unique()->count(),
                'unique_subjects' => $records->pluck('subject_id')->filter()->unique()->count(),
                'unique_classes' => $records->pluck('academic_class_id')->filter()->unique()->count(),
                'status_breakdown' => $statusBreakdown,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'top_teacher' => $topTeacherGroup ? [
                    'name' => optional(optional($topTeacherGroup)->first()->employee)->name,
                    'count' => $topTeacherGroup->count(),
                ] : null,
                'top_subject' => $topSubjectGroup ? [
                    'name' => optional(optional($topSubjectGroup)->first()->subject)->subject_name,
                    'count' => $topSubjectGroup->count(),
                ] : null,
                'top_class' => $topClassGroup ? [
                    'name' => optional(optional($topClassGroup)->first()->academicClass)->name_text,
                    'count' => $topClassGroup->count(),
                ] : null,
                'filters_applied' => $filtersApplied,
            ];

            $pdf = App::make('dompdf.wrapper');
            $pdf->setPaper('A4', 'landscape');
            $pdf->loadHTML(view('print.employee-monitoring-report', [
                'report' => $report,
                'records' => $records,
                'enterprise' => $enterprise,
                'summary' => $summary,
            ]));
              $pdfStored = Storage::disk('admin')->put($pdfRelativePath, $pdf->output());
              if (!$pdfStored) {
                  throw new \Exception('Failed to save PDF report file.');
              }

            $report->status = 'Completed';
            $report->error_message = null;
            $report->generated_at = now();
            $report->generated_by = $u->id;
            $report->excel_path = $excelRelativePath;
            $report->pdf_path = $pdfRelativePath;
            $report->save();

            return $this->renderGenerateResultPage(
                $report,
                'Report Generated Successfully',
                'Your EMT report is ready. Open PDF or Excel using the action buttons below.',
                url('storage/' . $pdfRelativePath),
                url('storage/' . $excelRelativePath),
                false
            );
        } catch (\Throwable $th) {
            $report->status = 'Skipped';
            $report->error_message = $th->getMessage();
            $report->save();

            return $this->renderGenerateResultPage(
                $report,
                'Report Generation Failed',
                e($th->getMessage()),
                null,
                null,
                true
            );
        }
    }

    private function renderGenerateResultPage(
        EmployeeMonitoringReportRecord $report,
        string $title,
        string $message,
        ?string $pdfUrl,
        ?string $excelUrl,
        bool $isError = false
    ): string {
        $enterprise = $report->enterprise;
        $brandColor = '#1f6feb';
        if ($enterprise && !empty($enterprise->color) && preg_match('/^#?[0-9A-Fa-f]{6}$/', (string) $enterprise->color)) {
            $brandColor = strpos((string) $enterprise->color, '#') === 0
                ? (string) $enterprise->color
                : ('#' . $enterprise->color);
        }

        $statusColor = $isError ? '#b42318' : '#14532d';
        $logoHtml = '';
        if ($enterprise && !empty($enterprise->logo)) {
            $logoHtml = '<img src="' . e(url('storage/' . ltrim((string) $enterprise->logo, '/'))) . '" alt="Logo" style="width:48px;height:48px;object-fit:contain;display:block;">';
        }

        $pdfButton = '';
        if (!empty($pdfUrl)) {
            $pdfButton = '<a href="' . e($pdfUrl) . '" target="_blank" style="display:inline-block;padding:9px 12px;background:#b42318;color:#fff;text-decoration:none;font-size:12px;font-weight:700;border:1px solid #8f1c13;margin-right:6px;">Open PDF</a>';
        }

        $excelButton = '';
        if (!empty($excelUrl)) {
            $excelButton = '<a href="' . e($excelUrl) . '" target="_blank" style="display:inline-block;padding:9px 12px;background:#166534;color:#fff;text-decoration:none;font-size:12px;font-weight:700;border:1px solid #14532d;">Open Excel</a>';
        }

        $backButton = '<a href="' . e(admin_url('employee-monitoring-report-records')) . '" style="display:inline-block;padding:9px 12px;background:#fff;color:#111827;text-decoration:none;font-size:12px;font-weight:700;border:1px solid #d1d5db;margin-left:6px;">Back to Reports</a>';

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>EMT Report Generation</title>
</head>
<body style="margin:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:820px;margin:28px auto;padding:0 12px;">
        <div style="height:7px;background:' . e($brandColor) . ';"></div>
        <div style="background:#fff;border:1px solid #d1d5db;padding:14px;">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="width:58px;vertical-align:top;">' . $logoHtml . '</td>
                    <td style="vertical-align:top;">
                        <div style="font-size:17px;font-weight:700;line-height:1.2;">' . e(strtoupper((string) ($enterprise->name ?? 'School Dynamics'))) . '</div>
                        <div style="font-size:11px;color:#4b5563;margin-top:2px;">Employee Monitoring Tool (EMT)</div>
                    </td>
                    <td style="text-align:right;vertical-align:top;">
                        <div style="font-size:11px;color:#6b7280;">Reference</div>
                        <div style="font-size:13px;font-weight:700;">EMT-' . e(str_pad((string) $report->id, 5, '0', STR_PAD_LEFT)) . '</div>
                    </td>
                </tr>
            </table>

            <div style="margin-top:12px;padding:10px;border:1px solid #d1d5db;background:#f9fafb;">
                <div style="font-size:15px;font-weight:700;color:' . e($statusColor) . ';">' . e($title) . '</div>
                <div style="font-size:12px;color:#374151;margin-top:4px;">' . $message . '</div>
                <div style="font-size:11px;color:#6b7280;margin-top:6px;">Report: ' . e((string) $report->report_name) . ' | Type: ' . e((string) $report->report_type) . '</div>
            </div>

            <div style="margin-top:12px;">' . $pdfButton . $excelButton . $backButton . '</div>
        </div>
    </div>
</body>
</html>';
    }

    protected function detail($id)
    {
        $show = new Show(EmployeeMonitoringReportRecord::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('report_name', 'Report Name');
        $show->field('report_type', 'Report Type');
        $show->field('status', 'Status');
        $show->field('parameters', 'Parameters')->json();
        $show->field('generated_at', 'Generated At');
        $show->field('error_message', 'Error');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new EmployeeMonitoringReportRecord());
        $u = Admin::user();
        $activeTerm = $u->ent ? $u->ent->active_term() : null;
        $initialClasses = [];
        if ($activeTerm) {
            $initialClasses = AcademicClass::where('enterprise_id', $u->enterprise_id)
                ->where('academic_year_id', $activeTerm->academic_year_id)
                ->orderBy('name')
                ->get()
                ->pluck('name_text', 'id')
                ->toArray();
        }

        $form->hidden('enterprise_id')->default($u->enterprise_id);

        $form->text('report_name', 'Report Name')->rules('required');
        $addCascadingFields = function (Form $form, bool $includeTeacher = true) use ($u, $activeTerm, $initialClasses) {
            $termField = $form->select('term_id', 'Term')->options(
                Term::where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc')->get()->pluck('name_text', 'id')
            )->default($activeTerm ? $activeTerm->id : null);

            $termField->load('academic_class_id', admin_url('employee-monitoring-report-records/ajax/classes-by-term'));

            $form->select('academic_class_id', 'Class')
                ->options(function ($id) use ($u, $initialClasses) {
                    if (!$id) {
                        return $initialClasses;
                    }
                    $class = AcademicClass::where('enterprise_id', $u->enterprise_id)->find($id);
                    return $class ? [$class->id => $class->name_text] : [];
                })
                ->load('subject_id', admin_url('employee-monitoring-report-records/ajax/subjects-by-class'))
                ->help('Select term first, then class list cascades automatically.');

            $form->select('subject_id', 'Subject')
                ->options(function ($id) use ($u) {
                    if (!$id) {
                        return [];
                    }
                    $subject = Subject::where('enterprise_id', $u->enterprise_id)->find($id);
                    return $subject ? [$subject->id => $subject->subject_name] : [];
                })
                ->load('employee_id', admin_url('employee-monitoring-report-records/ajax/teachers-by-subject'))
                ->help('Select class first, then subjects list cascades automatically.');

            if ($includeTeacher) {
                $form->select('employee_id', 'Teacher')->options(function ($id) use ($u) {
                    if (!$id) {
                        return [];
                    }
                    $teacher = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])->find($id);
                    return $teacher ? [$teacher->id => $teacher->name] : [];
                })->help('Select subject first, then teacher list cascades automatically.');
            }

            $form->dateRange('from_date', 'to_date', 'Date Range')
                ->help('Use this together with term/class/subject/teacher filters for focused reports.');
        };

        $form->radio('report_type', 'Report Type')->options([
            'individual_teacher' => 'Individual Teacher Performance',
            'subject_wise' => 'Subject-wise Performance',
            'class_wise' => 'Class-wise Performance',
            'term_trend' => 'Academic Term Performance Trend',
        ])->default('individual_teacher')
            ->rules('required')
            ->when('individual_teacher', function (Form $form) use ($addCascadingFields) {
                $addCascadingFields($form, true);
            })
            ->when('subject_wise', function (Form $form) use ($addCascadingFields) {
                $addCascadingFields($form, false);
            })
            ->when('class_wise', function (Form $form) use ($addCascadingFields) {
                $addCascadingFields($form, false);
            })
            ->when('term_trend', function (Form $form) use ($addCascadingFields) {
                $addCascadingFields($form, true);
            });

        $form->saving(function (Form $form) {
            $from = request()->input('from_date');
            $to = request()->input('to_date');
            $reportType = (string) request()->input('report_type');
            $termId = request()->input('term_id');
            $subjectId = request()->input('subject_id');
            $employeeId = request()->input('employee_id');
            $classId = request()->input('academic_class_id');

            if (($from && !$to) || (!$from && $to)) {
                throw new \Exception('Both start and end date must be provided for date range filter.');
            }
            if ($from && $to && strtotime($from) > strtotime($to)) {
                throw new \Exception('Date range is invalid. Start date cannot be after end date.');
            }

            if ($reportType === 'individual_teacher' && empty($employeeId)) {
                throw new \Exception('Teacher is required for Individual Teacher Performance report type.');
            }
            if ($reportType === 'subject_wise' && empty($subjectId)) {
                throw new \Exception('Subject is required for Subject-wise Performance report type.');
            }
            if ($reportType === 'class_wise' && empty($classId)) {
                throw new \Exception('Class is required for Class-wise Performance report type.');
            }
            if ($reportType === 'term_trend' && empty($termId) && empty($from) && empty($to)) {
                throw new \Exception('Provide at least a term or a full date range for Academic Term Performance Trend.');
            }

            $form->parameters = [
                'from_date' => $from,
                'to_date' => $to,
                'term_id' => $termId,
                'subject_id' => $subjectId,
                'employee_id' => $employeeId,
                'academic_class_id' => $classId,
            ];

            $form->generated_by = Admin::user()->id;
            $form->generated_at = null;
            $form->pdf_path = null;
            $form->excel_path = null;
            $form->error_message = null;
            $form->status = 'Pending';
        });

        $form->ignore([
            'from_date',
            'to_date',
            'term_id',
            'subject_id',
            'employee_id',
            'academic_class_id',
        ]);

        return $form;
    }

    private function resolveRecords(EmployeeMonitoringReportRecord $report, int $enterpriseId)
    {
        $params = (array) $report->parameters;

        $query = EmployeeMonitoringRecord::with(['term', 'employee', 'subject', 'academicClass'])
            ->where('enterprise_id', $enterpriseId);

        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $query->whereBetween('monitored_on', [$params['from_date'], $params['to_date']]);
        }

        foreach (['term_id', 'subject_id', 'employee_id', 'academic_class_id'] as $field) {
            if (!empty($params[$field])) {
                $query->where($field, $params[$field]);
            }
        }

        $reportType = (string) $report->report_type;

        if ($reportType === 'subject_wise') {
            $query->orderBy('subject_id')->orderBy('monitored_on', 'desc');
        } elseif ($reportType === 'class_wise') {
            $query->orderBy('academic_class_id')->orderBy('monitored_on', 'desc');
        } elseif ($reportType === 'term_trend') {
            $query->orderBy('term_id')->orderBy('monitored_on');
        } else {
            $query->orderBy('employee_id')->orderBy('monitored_on', 'desc');
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function ajaxClassesByTerm(Request $request)
    {
        $u = Admin::user();
        $termId = (int) $request->get('q');
        $data = [];

        if ($termId < 1) {
            return ['data' => $data];
        }

        $term = Term::where('enterprise_id', $u->enterprise_id)->find($termId);
        if (!$term) {
            return ['data' => $data];
        }

        $classes = AcademicClass::where('enterprise_id', $u->enterprise_id)
            ->where('academic_year_id', $term->academic_year_id)
            ->orderBy('name')
            ->limit(200)
            ->get();

        foreach ($classes as $class) {
            $data[] = [
                'id' => (string) $class->id,
                'text' => $class->name_text,
            ];
        }

        return ['data' => $data];
    }

    public function ajaxSubjectsByClass(Request $request)
    {
        $u = Admin::user();
        $classId = (int) $request->get('q');
        $data = [];

        if ($classId < 1) {
            return ['data' => $data];
        }

        $subjects = Subject::where('enterprise_id', $u->enterprise_id)
            ->where('academic_class_id', $classId)
            ->orderBy('subject_name')
            ->limit(300)
            ->get();

        foreach ($subjects as $subject) {
            $data[] = [
                'id' => (string) $subject->id,
                'text' => $subject->subject_name,
            ];
        }

        return ['data' => $data];
    }

    public function ajaxTeachersBySubject(Request $request)
    {
        $u = Admin::user();
        $subjectId = (int) $request->get('q');
        $data = [];

        if ($subjectId < 1) {
            return ['data' => $data];
        }

        $subject = Subject::where('enterprise_id', $u->enterprise_id)->find($subjectId);
        if (!$subject) {
            return ['data' => $data];
        }

        $teacherIds = array_values(array_unique(array_filter([
            (int) $subject->subject_teacher,
            (int) $subject->teacher_1,
            (int) $subject->teacher_2,
            (int) $subject->teacher_3,
        ])));

        if (count($teacherIds) < 1) {
            return ['data' => $data];
        }

        $teachers = User::where('enterprise_id', $u->enterprise_id)
            ->where('user_type', 'employee')
            ->whereIn('id', $teacherIds)
            ->orderBy('name')
            ->get();

        foreach ($teachers as $teacher) {
            $data[] = [
                'id' => (string) $teacher->id,
                'text' => $teacher->name,
            ];
        }

        return ['data' => $data];
    }
}
