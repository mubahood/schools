<?php

namespace App\Admin\Controllers;

use App\Models\FeesDataImport;
use App\Models\Term;
use App\Models\Utils;
use App\Services\FeesImportService;
use App\Services\FeesImportServiceOptimized;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeesDataImportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Fees Data Import';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FeesDataImport());

        $u =  Admin::user();
        //should only see their own enterprise data
        $grid->model()->where('enterprise_id', $u->enterprise_id)
            ->orderBy('created_at', 'desc');
        // Filters
        $grid->filter(function (Grid\Filter $filter) {

            $filter->like('title', 'Title');
            $filter->equal('status', 'Status')->select([
                'Pending' => 'Pending',
                'Processing' => 'Processing',
                'Completed' => 'Completed',
                'Failed' => 'Failed',
            ]);
            $filter->between('created_at', 'Created At')->datetime();
        });


        $grid->column('title', __('Title'))->limit(40);
        $grid->disableBatchActions();

        $grid->column('status', __('Status'))->dot([
            'Pending'    => 'warning',
            'Processing' => 'info',
            'Completed'  => 'success',
            'Failed'     => 'danger',
            'Cancelled'  => 'default',
        ])->sortable();

        // Progress indicator
        $grid->column('progress', __('Progress'))->display(function () {
            if ($this->status == 'Pending' || $this->total_rows == 0) {
                return '-';
            }
            $percentage = $this->total_rows > 0 ? round(($this->processed_rows / $this->total_rows) * 100, 1) : 0;
            $color = $this->status == 'Completed' ? 'success' : ($this->status == 'Failed' ? 'danger' : 'info');
            return "<div class='progress' style='margin-bottom:0;'>
                <div class='progress-bar progress-bar-{$color}' style='width:{$percentage}%'>{$percentage}%</div>
            </div>";
        });

        // Statistics
        $grid->column('statistics', __('Statistics'))->display(function () {
            if ($this->total_rows == 0) {
                return '-';
            }
            return "Total: <strong>{$this->total_rows}</strong> | " .
                "✓ <span class='text-success'>{$this->success_count}</span> | " .
                "✗ <span class='text-danger'>{$this->failed_count}</span> | " .
                "⊘ <span class='text-muted'>{$this->skipped_count}</span>";
        });

        $grid->column('created_at', __('Created'))
            ->display(function ($createdAt) {
                return Utils::my_date_3($createdAt);
            })->sortable();

        $grid->column('identify_by', __('Identify By'))->label([
            'school_pay_account_id' => 'info',
            'reg_number' => 'primary',
        ]);

        $grid->column('file_path', __('Import File'))->display(function ($path) {
            $url = url('storage/' . $path);
            return $url
                ? "<a href='" . $url . "' target='_blank'><i class='fa fa-download'></i> Download</a>"
                : '-';
        });

        // Columns
        $grid->column('creator_id', __('Imported By'))->display(function ($creatorId) {
            return $this->creator ? $this->creator->name : 'N/A';
        })->sortable();

        //action buttons
        $grid->column('actions', __('Actions'))->display(function () {
            $validateLink = url("fees-data-import-validate?id={$this->id}");
            $importLink = url("fees-data-import-do-import?id={$this->id}");
            $retryLink = url("fees-data-import-retry?id={$this->id}");
            $target = " target='_blank'";
            $buttons = [];

            if ($this->status == 'Pending') {
                $buttons[] = "<a href='{$validateLink}' class='btn btn-xs btn-info'{$target}>Validate</a>";
                $buttons[] = "<a href='{$importLink}' class='btn btn-xs btn-primary'{$target}>Import Data</a>";
            } elseif ($this->status == 'Processing') {
                $buttons[] = "<span class='btn btn-xs btn-default' disabled>Processing...</span>";
            } elseif ($this->status == 'Completed') {
                $buttons[] = "<a href='" . url("fees-data-import-records?fees_data_import_id={$this->id}") . "' class='btn btn-xs btn-success'>View Records</a>";
                if ($this->failed_count > 0) {
                    $buttons[] = "<a href='{$retryLink}' class='btn btn-xs btn-warning'{$target}>Retry Failed ({$this->failed_count})</a>";
                }
            } elseif ($this->status == 'Failed') {
                $buttons[] = "<a href='{$validateLink}' class='btn btn-xs btn-info'{$target}>Validate</a>";
                $buttons[] = "<a href='{$importLink}' class='btn btn-xs btn-warning'{$target}>Try Again</a>";
            }

            return implode(' ', $buttons);
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
        $show = new Show(FeesDataImport::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('enterprise.name', __('Enterprise'));
        $show->field('creator.username', __('Created By'));
        $show->field('title', __('Title'));
        $show->field('identify_by', __('Identify By'));
        $show->field('school_pay_column', __('School Pay Column'));
        $show->field('reg_number_column', __('Reg Number Column'));
        $show->field('services_columns', __('Services Columns'));
        $show->field('current_balance_column', __('Current Balance Column'));
        $show->field('previous_fees_term_balance_column', __('Previous Term Balance Column'));
        $show->field('file_path', __('Import File'))->as(function ($path) {
            return $path
                ? "<a href='" . url("storage/" . $path) . "' target='_blank'>Download</a>"
                : '-';
        });
        $show->field('status', __('Status'))->as(function ($status) {
            return strtoupper($status);
        })->label();
        $show->field('summary', __('Summary'))->unescape();
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
        $form = new Form(new FeesDataImport());
        $user = Admin::user();

        $collumn_names = [
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'H' => 'H',
            'I' => 'I',
            'J' => 'J',
            'K' => 'K',
            'L' => 'L',
            'M' => 'M',
            'N' => 'N',
            'O' => 'O',
            'P' => 'P',
            'Q' => 'Q',
            'R' => 'R',
            'S' => 'S',
            'T' => 'T',
            'U' => 'U',
            'V' => 'V',
            'W' => 'W',
            'X' => 'X',
            'Y' => 'Y',
            'Z' => 'Z',
        ];

        // Show status badge if editing existing record
        if ($form->isEditing()) {
            $form->display('id', 'Import ID');
            $form->display('status', 'Status')->with(function ($value) {
                $badges = [
                    'Pending' => 'default',
                    'Processing' => 'info',
                    'Completed' => 'success',
                    'Failed' => 'danger',
                    'Cancelled' => 'warning',
                ];
                $badge = $badges[$value] ?? 'default';
                return "<span class='label label-{$badge}'>{$value}</span>";
            });

            $form->display('batch_identifier', 'Batch Identifier')->help('Unique identifier for this import batch');

            // Show progress if processing or completed
            if (in_array($form->model()->status, ['Processing', 'Completed', 'Failed'])) {
                $model = $form->model();
                $progressHtml = $this->generateProgressDisplay($model);
                $form->html($progressHtml);
            }

            // Show lock status if locked
            if ($form->model()->is_locked) {
                $lockedBy = $form->model()->lockedBy;
                $lockedAt = \App\Models\Utils::my_date_3($form->model()->locked_at);
                $form->html("<div class='alert alert-warning'><i class='fa fa-lock'></i> <strong>Locked</strong> by {$lockedBy->name} at {$lockedAt}</div>");
            }

            $form->divider();
        }

        // Automatically assign enterprise and creator
        $form->hidden('enterprise_id')->value($user->enterprise_id);
        $form->hidden('created_by_id')->value($user->id);

        // Basic Information
        $form->text('title', 'Import Title')
            ->rules('required|max:255')
            ->placeholder('e.g., Term 2 Fees Import - January 2024')
            ->help('Give this import a descriptive title for easy identification');

        $getItemsToArrays = Term::getItemsToArray([
            'enterprise_id' => Admin::user()->enterprise_id
        ]);
        // Term Selection (NEW)
        $form->select('term_id', 'Academic Term')
            ->options($getItemsToArrays)
            ->help('Select the academic term this import is for (optional but recommended)');

        $form->divider('Student Identification Settings');

        // Student Identification Method
        $form->radio('identify_by', 'Identify Students By')
            ->options([
                'school_pay_account_id' => 'School Pay Account ID',
                'reg_number'            => 'Registration Number',
            ])
            ->rules('required')
            ->default('school_pay_account_id')
            ->help('<strong>Important:</strong> Choose how to match rows to students in the system.')
            ->when('reg_number', function (Form $form) use ($collumn_names) {
                $form->radio('reg_number_column', 'Registration Number Column')
                    ->rules('required')
                    ->options($collumn_names)
                    ->help('Select the Excel column that contains registration numbers (e.g., "STU001", "2024-001")');
            })
            ->when('school_pay_account_id', function (Form $form) use ($collumn_names) {
                $form->radio('school_pay_column', 'School Pay Column')
                    ->rules('required')
                    ->options($collumn_names)
                    ->help('Select the Excel column that contains School Pay Account IDs (e.g., "SP12345")');
            });

        $form->divider('Service Columns Configuration');

        // Services Columns
        $form->checkbox('services_columns', 'Services Columns')
            ->options($collumn_names)
            ->rules('required')
            ->help('<strong>Select all columns</strong> that contain service fees. Each selected column should have a service name in the header row and amounts in data rows.');

        $form->divider('Balance Columns Configuration');

        // Previous Term Balance Column (Fixed label typo)
        $form->radio('previous_fees_term_balance_column', 'Previous Term Balance Column')
            ->options($collumn_names)
            ->help('Select the column containing previous term balances (leave empty if not applicable). <br><strong>Positive</strong> = Student owes money, <strong>Negative</strong> = School owes student (overpayment)');

        // Current Balance Column
        $form->radio('current_balance_column', 'Current Balance Column')
            ->options($collumn_names)
            ->rules('required')
            ->help('Select the column containing current balance before this import. This is used for validation purposes.');

        // Balance Sign Handling
        $form->radio('cater_for_balance', 'Balance Column Has Negative Sign?')
            ->options([
                'Yes' => 'Yes - Negative numbers show credits (e.g., -5000)',
                'No'  => 'No - All numbers are positive, separate indicator for credits',
            ])
            ->default('Yes')
            ->rules('required')
            ->help('Does the balance column use negative numbers (-) to indicate credit balances?');

        $form->divider('Import File');

        // File Upload
        $form->file('file_path', 'Excel/CSV File')
            ->rules('required|mimes:xlsx,csv|max:51200') // 50MB max
            ->uniqueName()
            ->removable()
            ->help('<strong>Upload Requirements:</strong><br>
                    • File format: .xlsx or .csv<br>
                    • Maximum size: 50 MB<br>
                    • First row must contain column headers<br>
                    • Data starts from row 2<br>
                    • <strong>Note:</strong> Duplicate files will be detected and rejected automatically');

        // Show validation/processing buttons if editing
        if ($form->isEditing()) {
            $model = $form->model();

            $form->divider('Actions');

            if ($model->status === 'Pending') {
                $form->html('
                    <div class="form-group">
                        <label class="col-sm-2 control-label"></label>
                        <div class="col-sm-8">
                            <a href="' . url('fees-data-import-validate?import_id=' . $model->id) . '" 
                               class="btn btn-info" target="_blank">
                                <i class="fa fa-check-circle"></i> Validate Import
                            </a>
                            <p class="help-block">Click to validate the file before processing. This will check for errors without making changes.</p>
                        </div>
                    </div>
                ');
            }

            if ($model->status === 'Pending' || $model->status === 'Failed') {
                $form->html('
                    <div class="form-group">
                        <label class="col-sm-2 control-label"></label>
                        <div class="col-sm-8">
                            <a href="' . url('fees-data-import-do-import-optimized?import_id=' . $model->id) . '" 
                               class="btn btn-success" 
                               onclick="return confirm(\'Start processing this import? This may take several minutes for large files.\')">
                                <i class="fa fa-play"></i> Start Import
                            </a>
                            <p class="help-block"><strong>Important:</strong> Validate the import first before starting. The import process cannot be undone.</p>
                        </div>
                    </div>
                ');
            }

            if ($model->status === 'Completed' && $model->failed_count > 0) {
                $form->html('
                    <div class="form-group">
                        <label class="col-sm-2 control-label"></label>
                        <div class="col-sm-8">
                            <a href="' . url('fees-data-import-retry?import_id=' . $model->id) . '" 
                               class="btn btn-warning" target="_blank">
                                <i class="fa fa-refresh"></i> Retry Failed Records (' . $model->failed_count . ')
                            </a>
                            <p class="help-block">Attempt to re-process failed records from this import.</p>
                        </div>
                    </div>
                ');
            }

            // View Records button
            $form->html('
                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-8">
                        <a href="' . admin_url('fees-data-import-records?import_id=' . $model->id) . '" 
                           class="btn btn-primary" target="_blank">
                            <i class="fa fa-list"></i> View Import Records
                        </a>
                        <p class="help-block">View detailed records for each row in this import.</p>
                    </div>
                </div>
            ');
        }

        // Prevent editing of completed/processing imports
        $form->saving(function (Form $form) {
            if ($form->isEditing()) {
                $status = $form->model()->status;
                if (in_array($status, ['Completed', 'Processing'])) {
                    admin_error('Not Allowed', "Cannot modify an import with status: {$status}");
                    return false;
                }
            }
        });

        $form->disableReset();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }

    /**
     * Generate HTML for progress display
     */
    protected function generateProgressDisplay($model)
    {
        $percentage = $model->getProgressPercentage();
        $total = $model->total_rows ?? 0;
        $processed = $model->processed_rows ?? 0;
        $success = $model->success_count ?? 0;
        $failed = $model->failed_count ?? 0;
        $skipped = $model->skipped_count ?? 0;

        $progressColor = 'info';
        if ($model->status === 'Completed') {
            $progressColor = $failed > 0 ? 'warning' : 'success';
        } elseif ($model->status === 'Failed') {
            $progressColor = 'danger';
        }

        return "
        <div class='box box-{$progressColor}'>
            <div class='box-header with-border'>
                <h3 class='box-title'>Import Progress</h3>
            </div>
            <div class='box-body'>
                <div class='progress' style='height: 25px;'>
                    <div class='progress-bar progress-bar-{$progressColor}' 
                         role='progressbar' 
                         aria-valuenow='{$percentage}' 
                         aria-valuemin='0' 
                         aria-valuemax='100' 
                         style='width: {$percentage}%;'>
                        {$percentage}%
                    </div>
                </div>
                <table class='table table-condensed' style='margin-top: 10px;'>
                    <tr>
                        <td><strong>Total Rows:</strong></td>
                        <td>{$total}</td>
                        <td><strong>Processed:</strong></td>
                        <td>{$processed}</td>
                    </tr>
                    <tr>
                        <td><strong><span class='text-success'>Success:</span></strong></td>
                        <td><span class='text-success'>{$success}</span></td>
                        <td><strong><span class='text-danger'>Failed:</span></strong></td>
                        <td><span class='text-danger'>{$failed}</span></td>
                    </tr>
                    <tr>
                        <td><strong><span class='text-warning'>Skipped:</span></strong></td>
                        <td><span class='text-warning'>{$skipped}</span></td>
                        <td><strong>Started:</strong></td>
                        <td>" . ($model->started_at ? \App\Models\Utils::my_date_3($model->started_at) : 'N/A') . "</td>
                    </tr>
                    <tr>
                        <td><strong>Completed:</strong></td>
                        <td>" . ($model->completed_at ? \App\Models\Utils::my_date_3($model->completed_at) : 'N/A') . "</td>
                        <td><strong>Duration:</strong></td>
                        <td>" . ($model->started_at && $model->completed_at ? gmdate('H:i:s', strtotime($model->completed_at) - strtotime($model->started_at)) : 'N/A') . "</td>
                    </tr>
                </table>
            </div>
        </div>
        ";
    }
}
