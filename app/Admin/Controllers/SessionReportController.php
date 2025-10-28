<?php

namespace App\Admin\Controllers;

use App\Models\SessionReport;
use App\Models\User;
use App\Models\AcademicClass;
use App\Models\TheologyClass;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SessionReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Session Attendance Reports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SessionReport());
        $u = Admin::user();
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'DESC');

        $grid->disableBatchActions();
        $grid->quickSearch('title')->placeholder('Search by title...');

        $grid->column('id', __('ID'))->sortable();

        $grid->column('title', __('Report Title'))
            ->display(function ($title) {
                return '<strong>' . $title . '</strong>';
            });



        $grid->column('type', __('Report Type'))
            ->display(function ($type) {
                $colors = [
                    'Weekly' => 'primary',
                    'Monthly' => 'success',
                    'Termly' => 'warning',
                    'Custom' => 'info'
                ];
                $color = $colors[$type] ?? 'default';
                return "<span class='label label-{$color}'>{$type}</span>";
            });

        $grid->column('start_date', __('Start Date'))
            ->display(function ($date) {
                return date('D, M d, Y', strtotime($date));
            })->sortable();

        $grid->column('end_date', __('End Date'))
            ->display(function ($date) {
                return date('D, M d, Y', strtotime($date));
            })->sortable();

        $grid->column('total_days', __('Days'))->sortable();

        $grid->column('attendance_summary', __('Attendance'))
            ->display(function () {
                $total = $this->total_boys_present + $this->total_girls_present;
                $boys = $this->total_boys_present;
                $girls = $this->total_girls_present;
                return "Total: <strong>{$total}</strong> (Boys: {$boys}, Girls: {$girls})";
            });

        $grid->column('teacher_1_on_duty_id', __('Teacher 1 on Duty'))
            ->display(function ($id) {
                if (!$id) return '-';
                $teacher = User::find($id);
                return $teacher ? $teacher->name : '-';
            });

        $grid->column('teacher_2_on_duty_id', __('Teacher 2 on Duty'))
            ->display(function ($id) {
                if (!$id) return '-';
                $teacher = User::find($id);
                return $teacher ? $teacher->name : '-';
            });

        $grid->column('pdf_processed', __('PDF Status'))
            ->display(function ($status) {
                if ($status == 'Yes') {
                    return "<span class='label label-success'>Generated</span>";
                }
                return "<span class='label label-warning'>Pending</span>";
            });

        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return date('M d, Y', strtotime($date));
            })->sortable();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            
            // Download Button
            $downloadUrl = url("/session-report-pdf/{$actions->getKey()}");
            $actions->append("<a href='{$downloadUrl}' class='btn btn-xs btn-success' target='_blank'><i class='fa fa-download'></i> Download</a>");
            
            // Regenerate Button
            $regenerateUrl = url("/attendance-report?id={$actions->getKey()}&regenerate=1");
            $actions->append("<a href='{$regenerateUrl}' class='btn btn-xs btn-warning' target='_blank'><i class='fa fa-refresh'></i> Regenerate</a>");
        });

        //attendance-report
        $grid->column('attendance_report', __('PDF Actions'))
            ->display(function () {
                $buttons = '';
                
                // View PDF Button (if PDF exists)
                if ($this->pdf_processed == 'Yes' && $this->pdf_path) {
                    $url = url("/session-report-pdf/{$this->id}");
                    $buttons .= "<a href='{$url}' class='' target='_blank' title='View PDF Report'><i class='fa fa-file-pdf-o'></i> View PDF</a> <br>";
                    
                    // Regenerate Button
                    $regenerateUrl = url("/attendance-report?id={$this->id}&regenerate=1");
                    $buttons .= "<a href='{$regenerateUrl}' class=''  target='_blank'  title='Regenerate PDF'><i class='fa fa-refresh'></i> Regenerate</a>";
                } else {
                    $buttons .= "<span class='label label-default'>No PDF Generated Yet</span>";
                }
                
                return $buttons;
            });

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
        $show = new Show(SessionReport::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('title', __('Report Title'));
        $show->field('type', __('Report Type'));

        $show->divider();

        $show->field('start_date', __('Start Date'))->as(function ($date) {
            return date('l, F j, Y', strtotime($date));
        });
        $show->field('end_date', __('End Date'))->as(function ($date) {
            return date('l, F j, Y', strtotime($date));
        });
        $show->field('total_days', __('Total Days'));

        $show->divider();

        $show->field('teacher_1_on_duty_id', __('Teacher 1 on Duty'))->as(function ($id) {
            if (!$id) return '-';
            $teacher = User::find($id);
            return $teacher ? $teacher->name : 'Not Set';
        });

        $show->field('teacher_2_on_duty_id', __('Teacher 2 on Duty'))->as(function ($id) {
            if (!$id) return '-';
            $teacher = User::find($id);
            return $teacher ? $teacher->name : 'Not Set';
        });

        $show->field('head_of_week_id', __('Head of Week'))->as(function ($id) {
            if (!$id) return '-';
            $teacher = User::find($id);
            return $teacher ? $teacher->name : 'Not Set';
        });

        $show->divider();

        $show->field('total_boys_present', __('Total Boys Present'));
        $show->field('total_girls_present', __('Total Girls Present'));
        $show->field('attendance_summary', __('Total Attendance'))->as(function () {
            return $this->total_boys_present + $this->total_girls_present;
        });

        $show->divider();

        $show->field('top_absentees', __('Top Absentees'))->unescape();
        $show->field('top_punctuals', __('Top Punctual Students'))->unescape();

        $show->divider();

        $show->field('remarks', __('Remarks'))->unescape();

        $show->divider();

        $show->field('target_audience_type', __('Target Audience Type'));
        $show->field('target_audience_data', __('Target Audience'))->json();

        $show->field('pdf_processed', __('PDF Generated'))->using(['Yes' => 'Yes', 'No' => 'No', null => 'Pending']);
        $show->field('pdf_path', __('PDF Path'));

        $show->divider();

        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SessionReport());

        $u = Admin::user();
        $form->hidden('enterprise_id')->value($u->enterprise_id);

        // Report Basic Information
        $form->html('<h4 style="margin-top: 0; padding: 10px; background: #f5f5f5; border-left: 3px solid #3c8dbc;"><i class="fa fa-file-text"></i> Report Information</h4>');

        $form->date('start_date', __('Start Date'))
            ->default(date('Y-m-d'))
            ->rules('required')
            ->help('First day of the reporting period');

        $form->date('end_date', __('End Date'))
            ->default(date('Y-m-d'))
            ->rules('required|after_or_equal:start_date')
            ->help('Last day of the reporting period');

        $form->radio('type', __('Report Type'))
            ->options([
                'CLASS_ATTENDANCE' => 'Class Attendance Report',
                'THEOLOGY_ATTENDANCE' => 'Theology Class Attendance',
                'STUDENT_REPORT' => 'Student Daily Report',
                'ACTIVITY_ATTENDANCE' => 'Activity Participation',
                'STUDENT_MEAL' => 'Student Meals Report',
                'STUDENT_LEAVE' => 'Student Leave Report',
            ])
            ->default('CLASS_ATTENDANCE')
            ->rules('required')
            ->help('Select the type of attendance report to generate');

        $form->divider();

        // Teachers Section
        $form->html('<h4 style="margin-top: 20px; padding: 10px; background: #f5f5f5; border-left: 3px solid #00a65a;"><i class="fa fa-users"></i> Staff on Duty</h4>');

        // Get teachers for selection
        $teachers = User::where([
            'enterprise_id' => $u->enterprise_id,
            'status' => 1
        ])->whereIn('user_type', ['employee', 'admin'])
            ->orderBy('name', 'ASC')
            ->get()
            ->pluck('name', 'id');

        $form->select('teacher_1_on_duty_id', __('Teacher 1 on Duty'))
            ->options($teachers)
            ->rules('required')
            ->help('Select the first teacher on duty for this period');

        $form->select('teacher_2_on_duty_id', __('Teacher 2 on Duty'))
            ->options($teachers)
            ->help('Select the second teacher on duty (optional)');

        $form->select('head_of_week_id', __('Head of Week'))
            ->options($teachers)
            ->help('Select the head teacher responsible for this period');

        $form->divider();

        // Additional Information
        $form->html('<h4 style="margin-top: 20px; padding: 10px; background: #f5f5f5; border-left: 3px solid #f39c12;"><i class="fa fa-comments"></i> Additional Notes (Optional)</h4>');

        $form->textarea('remarks', __('General Remarks'))
            ->rows(4)
            ->help('Add any important observations, notes, or recommendations about attendance during this period');

        $form->divider();

        // Instructions
        $form->html('<div style="padding: 15px; background: #d9edf7; border: 1px solid #bce8f1; border-radius: 4px; margin-top: 20px;">
            <h4 style="margin-top: 0; color: #31708f;"><i class="fa fa-info-circle"></i> Important Information</h4>
            <ul style="margin-bottom: 0; color: #31708f;">
                <li>The report will be <strong>automatically processed</strong> after saving</li>
                <li>Attendance data will be <strong>calculated automatically</strong> from the participant records</li>
                <li>A <strong>PDF report</strong> will be generated automatically</li>
                <li>You can <strong>regenerate</strong> the PDF anytime from the reports list</li>
                <li>Make sure the <strong>date range</strong> includes the days you want to report on</li>
            </ul>
        </div>');

        // Process report automatically after save
        $form->saved(function (Form $form) {
            $report = SessionReport::find($form->model()->id);
            if ($report) {
                try {
                    $report->do_process();
                    admin_success('Success', 'Report generated successfully with PDF!');
                } catch (\Exception $e) {
                    admin_error('Error', 'Report saved but processing failed: ' . $e->getMessage());
                }
            }
        });

        return $form;
    }
}
