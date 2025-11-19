<?php

namespace App\Admin\Controllers;

use App\Models\FeesDataImport;
use App\Models\Term;
use App\Models\Utils;
use App\Services\FeesImportService;
use App\Services\FeesImportServiceOptimized;
use App\Services\FeesImportServiceCSV;
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
        // $grid->disableBatchActions();

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
            $importLink = url("fees-data-import-do-import-optimized?id={$this->id}");
            $retryLink = url("fees-data-import-retry?id={$this->id}");
            $duplicateLink = url("fees-data-import-duplicate?id={$this->id}");
            $target = " target='_blank'";
            $buttons = [];

            if ($this->status == 'Pending') {
                $buttons[] = "<a href='{$validateLink}' class='btn btn-xs btn-info'{$target}>Validate</a>";
                $buttons[] = "<a href='{$importLink}' class='btn btn-xs btn-primary'{$target}>Import Data</a>";
            } elseif ($this->status == 'Processing') {
                $buttons[] = "<span class='btn btn-xs btn-default' disabled>Processing...</span>";
                // Allow retry in case process crashed
                if ($this->failed_count > 0 || $this->skipped_count > 0) {
                    $failedSkipped = $this->failed_count + $this->skipped_count;
                    $buttons[] = "<a href='{$retryLink}' class='btn btn-xs btn-warning'{$target} onclick='return confirm(\"Process may have crashed. Retry {$failedSkipped} failed/skipped records?\")'><i class='fa fa-refresh'></i> Retry ({$failedSkipped})</a>";
                }
            } elseif ($this->status == 'Completed') {
                $buttons[] = "<a target='_blank' href='" . url("fees-data-import-records?fees_data_import_id={$this->id}") . "' class='btn btn-xs btn-success'>View Records</a>";
                // Show retry if there are failed or skipped records
                $failedSkipped = $this->failed_count + $this->skipped_count;
                if ($failedSkipped > 0) {
                    $buttons[] = "<a href='{$retryLink}' class='btn btn-xs btn-warning'{$target}><i class='fa fa-refresh'></i> Retry Failed/Skipped ({$failedSkipped})</a>";
                }
            } elseif ($this->status == 'Failed') {
                $buttons[] = "<a href='{$validateLink}' class='btn btn-xs btn-info'{$target}>Validate</a>";
                $buttons[] = "<a href='{$importLink}' class='btn btn-xs btn-warning'{$target}>Try Again</a>";
            }

            // Add duplicate button for any status except Processing
            if ($this->status != 'Processing') {
                $buttons[] = "<a href='{$duplicateLink}' class='btn btn-xs btn-default' onclick='return confirm(\"Duplicate this import? A new import will be created with the same settings.\")'>
                    <i class='fa fa-copy'></i> Duplicate
                </a>";
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
        $show->field('services_columns', __('Services Columns'))->as(function ($columns) {
            if (empty($columns)) return '<span class="text-muted">None</span>';
            if (is_array($columns)) {
                return '<span class="label label-success">' . implode('</span> <span class="label label-success">', $columns) . '</span>';
            }
            return $columns;
        })->unescape();
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

            $form->radio('status', 'Status')->options([
                'Pending' => 'Pending',
                'Processing' => 'Processing',
                'Completed' => 'Completed',
                'Failed' => 'Failed',
                'Cancelled' => 'Cancelled',
            ]);

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
                'school_pay_account_id' => 'School Pay Code (SchoolPay ID)',
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
                $form->radio('school_pay_column', 'School Pay Code Column')
                    ->rules('required')
                    ->options($collumn_names)
                    ->help('Select the column that contains School Pay Payment Codes (e.g., "1003839865" - the 10-digit code shown in students table)');
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

        // File Upload (CSV only for performance)
        $form->file('file_path', 'CSV File')
            ->rules('required|mimes:csv,txt|max:51200') // 50MB max, CSV only
            ->uniqueName()
            ->removable()
            ->help('<strong>Upload Requirements:</strong><br>
                    • <strong>CSV format ONLY</strong> (for optimal performance)<br>
                    • Maximum size: 50 MB<br>
                    • First row must contain column headers<br>
                    • Data starts from row 2<br>
                    • To convert Excel to CSV: File → Save As → CSV (Comma delimited)<br>
                    • <strong>Note:</strong> CSV files process 10-100x faster than Excel files');

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
                            <a href="' . url('fees-data-import-retry?id=' . $model->id) . '" 
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

            // Duplicate button (available for all statuses except Processing)
            if ($model->status !== 'Processing') {
                $form->html('
                    <div class="form-group">
                        <label class="col-sm-2 control-label"></label>
                        <div class="col-sm-8">
                            <a href="' . url('fees-data-import-duplicate?id=' . $model->id) . '" 
                               class="btn btn-default" 
                               onclick="return confirm(\'Duplicate this import?\\n\\nA new import will be created with:\\n• Same file\\n• Same configuration settings\\n• Reset status (Pending)\\n• All counters reset to zero\\n\\nYou can then modify settings before processing.\')">
                                <i class="fa fa-copy"></i> Duplicate This Import
                            </a>
                            <p class="help-block">Create a copy of this import with the same file and settings. Useful for re-importing with different configurations or to a different term.</p>
                        </div>
                    </div>
                ');
            }
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

    /**
     * Validate Fees Data Import
     */
    public function validate()
    {
        // Set limits for validation of large files
        set_time_limit(-1);
        ini_set('memory_limit', '2048M'); // 2GB for very large files
        ini_set('max_execution_time', '0');

        $u = Admin::user();
        if ($u == null) {
            return "You are not logged in";
        }

        $import = FeesDataImport::find(request('id'));
        if ($import == null) {
            return "Fees Data Import not found";
        }

        if ($import->enterprise_id != $u->enterprise_id) {
            return "Access denied. This import belongs to a different enterprise.";
        }

        try {
            // Use CSV service for better performance
            $service = new FeesImportServiceCSV();
            $validation = $service->validateImport($import);

            echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 900px;'>";
            echo "<h2>Import Validation Results</h2>";
            echo "<h3>Import: " . htmlspecialchars($import->title) . "</h3>";

            if ($validation['valid']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<strong>✓ Validation Passed!</strong><br>";
                echo "The import file is ready to be processed.";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<strong>✗ Validation Failed</strong><br>";
                echo "Please fix the errors below before processing the import.";
                echo "</div>";
            }

            // Show statistics (excluding student_list and services_summary which we'll display separately)
            if (!empty($validation['stats'])) {
                echo "<h4>File Statistics</h4>";
                echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0;'>";
                foreach ($validation['stats'] as $key => $value) {
                    if ($key === 'student_list' || $key === 'services_summary') continue; // Skip these - display separately
                    
                    $displayKey = ucwords(str_replace('_', ' ', $key));
                    $displayValue = is_array($value) ? json_encode($value) : htmlspecialchars($value);
                    
                    echo "<tr style='border-bottom: 1px solid #ddd;'>";
                    echo "<td style='padding: 8px; font-weight: bold;'>{$displayKey}:</td>";
                    echo "<td style='padding: 8px;'>{$displayValue}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }

            // Show services summary
            if (!empty($validation['stats']['services_summary'])) {
                echo "<h4>Services Configuration</h4>";
                echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0; max-width: 600px;'>";
                echo "<thead><tr style='background: #f5f5f5;'>";
                echo "<th style='padding: 10px; text-align: left; border: 1px solid #ddd; width: 100px;'>Column</th>";
                echo "<th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Service Title</th>";
                echo "</tr></thead><tbody>";
                foreach ($validation['stats']['services_summary'] as $service) {
                    $isEmpty = strpos($service['title'], '(Empty') !== false;
                    $titleColor = $isEmpty ? 'color: #dc3545; font-style: italic;' : 'color: #333;';
                    echo "<tr>";
                    echo "<td style='padding: 8px; border: 1px solid #ddd; font-weight: bold; text-align: center; background: #f8f9fa;'>{$service['column']}</td>";
                    echo "<td style='padding: 8px; border: 1px solid #ddd; {$titleColor}'>" . htmlspecialchars($service['title']) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            }

            // Show detailed student list with match status
            if (!empty($validation['stats']['student_list'])) {
                $studentList = $validation['stats']['student_list'];
                $matched = array_filter($studentList, fn($s) => $s['found']);
                $notMatched = array_filter($studentList, fn($s) => !$s['found']);
                
                echo "<h4>Student Match Details</h4>";
                echo "<p style='color: #666; margin-bottom: 10px;'>
                    <strong style='color: #28a745;'>✓ " . count($matched) . " Found</strong> | 
                    <strong style='color: #dc3545;'>✗ " . count($notMatched) . " Not Found</strong>
                </p>";
                
                // Add filter buttons
                echo "<div style='margin-bottom: 15px;'>
                    <button onclick='showAll()' style='padding: 5px 15px; margin-right: 5px; cursor: pointer;'>Show All</button>
                    <button onclick='showFound()' style='padding: 5px 15px; margin-right: 5px; cursor: pointer; background: #d4edda; border: 1px solid #28a745;'>Show Found Only</button>
                    <button onclick='showNotFound()' style='padding: 5px 15px; cursor: pointer; background: #f8d7da; border: 1px solid #dc3545;'>Show Not Found Only</button>
                </div>";
                
                echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;'>";
                echo "<table style='border-collapse: collapse; width: 100%; font-size: 13px;'>";
                echo "<thead style='position: sticky; top: 0; background: #f8f9fa; border-bottom: 2px solid #dee2e6;'>";
                echo "<tr>";
                echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;'>Row</th>";
                echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;'>CSV Name</th>";
                echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;'>Identifier</th>";
                echo "<th style='padding: 10px; text-align: right; border-bottom: 2px solid #dee2e6;'>Current Balance</th>";
                echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;'>Status</th>";
                echo "<th style='padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;'>DB Name</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                
                foreach ($studentList as $student) {
                    $rowClass = $student['found'] ? 'student-found' : 'student-not-found';
                    $bgColor = $student['found'] ? '#f0f9f4' : '#fef5f5';
                    $statusIcon = $student['found'] ? '✓' : '✗';
                    $statusColor = $student['found'] ? '#28a745' : '#dc3545';
                    $statusText = $student['found'] ? 'Found' : 'NOT FOUND';
                    
                    // Format balance with comma separator
                    $balance = isset($student['current_balance']) && !empty($student['current_balance']) 
                        ? number_format((float)$student['current_balance'], 0) 
                        : '-';
                    
                    echo "<tr class='{$rowClass}' style='background: {$bgColor}; border-bottom: 1px solid #e9ecef;'>";
                    echo "<td style='padding: 8px;'>{$student['row']}</td>";
                    echo "<td style='padding: 8px;'>" . htmlspecialchars($student['name']) . "</td>";
                    echo "<td style='padding: 8px; font-family: monospace;'>" . htmlspecialchars($student['identifier']) . "</td>";
                    echo "<td style='padding: 8px; text-align: right; font-family: monospace; font-weight: bold;'>" . htmlspecialchars($balance) . "</td>";
                    echo "<td style='padding: 8px; color: {$statusColor}; font-weight: bold;'>{$statusIcon} {$statusText}</td>";
                    echo "<td style='padding: 8px;'>" . ($student['db_name'] ? htmlspecialchars($student['db_name']) : '<span style="color: #999;">-</span>') . "</td>";
                    echo "</tr>";
                }
                
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                
                // Add JavaScript for filtering
                echo "<script>
                    function showAll() {
                        document.querySelectorAll('.student-found, .student-not-found').forEach(row => row.style.display = '');
                    }
                    function showFound() {
                        document.querySelectorAll('.student-found').forEach(row => row.style.display = '');
                        document.querySelectorAll('.student-not-found').forEach(row => row.style.display = 'none');
                    }
                    function showNotFound() {
                        document.querySelectorAll('.student-found').forEach(row => row.style.display = 'none');
                        document.querySelectorAll('.student-not-found').forEach(row => row.style.display = '');
                    }
                </script>";
            }

            // Show errors
            if (!empty($validation['errors'])) {
                echo "<h4 style='color: #dc3545;'>Errors (" . count($validation['errors']) . ")</h4>";
                echo "<ul style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px 15px 15px 35px; margin: 15px 0;'>";
                foreach ($validation['errors'] as $error) {
                    echo "<li style='margin: 5px 0;'>" . htmlspecialchars($error) . "</li>";
                }
                echo "</ul>";
            }

            // Show warnings
            if (!empty($validation['warnings'])) {
                echo "<h4 style='color: #ff6b35;'>Warnings (" . count($validation['warnings']) . ")</h4>";
                echo "<ul style='background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px 15px 15px 35px; margin: 15px 0;'>";
                foreach ($validation['warnings'] as $warning) {
                    echo "<li style='margin: 5px 0;'>" . htmlspecialchars($warning) . "</li>";
                }
                echo "</ul>";
            }

            if ($validation['valid']) {
                $importUrl = url("fees-data-import-do-import-optimized?id={$import->id}");
                echo "<div style='margin-top: 25px;'>";
                echo "<a href='{$importUrl}' class='btn btn-primary' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>";
                echo "Proceed with Import</a>";
                echo " <a href='javascript:history.back()' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Go Back</a>";
                echo "</div>";
            } else {
                echo "<div style='margin-top: 25px;'>";
                echo "<a href='javascript:history.back()' style='padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Go Back</a>";
                echo "</div>";
            }

            echo "</div>";
        } catch (\Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;'>";
            echo "<strong>Validation Error:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</div>";
            Log::error('Fees import validation failed', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process Fees Data Import (Optimized)
     */
    public function doImportOptimized()
    {
        $u = Admin::user();
        if ($u == null) {
            return "You are not logged in";
        }

        $import = FeesDataImport::find(request('id'));
        if ($import == null) {
            return "Fees Data Import not found";
        }

        if ($import->enterprise_id != $u->enterprise_id) {
            return "Access denied. This import belongs to a different enterprise.";
        }

        // Set execution limits for large imports (increased for massive Excel files)
        set_time_limit(-1);
        ini_set('memory_limit', '2048M'); // 2GB for very large files
        ini_set('max_execution_time', '0');

        // Disable output buffering and send headers to keep connection alive
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        while (ob_get_level()) { @ob_end_clean(); }
        
        // Send headers to prevent buffering
        header('Content-Type: text/html; charset=utf-8');
        header('X-Accel-Buffering: no'); // Disable nginx buffering
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Processing Import</title></head><body>";
        echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 900px;'>";
        echo "<h2>Processing Import: " . htmlspecialchars($import->title) . "</h2>";
        echo "<p>Please wait while the import is being processed...</p>";
        echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<div id='progress-info'>Starting import...</div>";
        echo "</div>";
        echo str_repeat(' ', 4096); // Fill buffer to force output
        flush();

        try {
            // Use CSV service for much better performance (10-100x faster!)
            $service = new FeesImportServiceCSV();
            $result = $service->processImport($import, $u);

            if ($result['success']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<strong>✓ Import Completed Successfully!</strong>";
                echo "</div>";

                echo "<h4>Import Summary</h4>";
                echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;'>";
                echo htmlspecialchars($result['message']);
                echo "</pre>";

                if (!empty($result['stats'])) {
                    echo "<h4>Detailed Statistics</h4>";
                    echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0; border: 1px solid #ddd;'>";
                    echo "<tr style='background: #f8f9fa;'><th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Metric</th><th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Count</th></tr>";
                    foreach ($result['stats'] as $key => $value) {
                        $displayKey = ucwords(str_replace('_', ' ', $key));
                        if ($key == 'errors') continue; // Skip errors array
                        echo "<tr style='border-bottom: 1px solid #ddd;'>";
                        echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$displayKey}</td>";
                        echo "<td style='padding: 8px; text-align: right; border: 1px solid #ddd;'><strong>" . htmlspecialchars($value) . "</strong></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }

                $recordsUrl = url("fees-data-import-records?fees_data_import_id={$import->id}");
                echo "<div style='margin-top: 25px;'>";
                echo "<a href='{$recordsUrl}' class='btn btn-success' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>";
                echo "View Import Records</a>";
                echo " <a href='" . url('fees-data-import') . "' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Back to Imports</a>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<strong>✗ Import Failed</strong><br>";
                echo htmlspecialchars($result['message']);
                echo "</div>";

                echo "<div style='margin-top: 25px;'>";
                echo "<a href='javascript:history.back()' style='padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Go Back</a>";
                echo "</div>";
            }
        } catch (\Exception $e) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<strong>Processing Error:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</div>";

            Log::error('Fees import processing failed', [
                'import_id' => $import->id,
                'user_id' => $u->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        echo "</div></body></html>";
        flush();
    }

    /**
     * Retry Failed Records
     */
    public function retry()
    {
        // Set limits for retry operations
        set_time_limit(-1);
        ini_set('memory_limit', '2048M'); // 2GB for very large files
        ini_set('max_execution_time', '0');

        $u = Admin::user();
        if ($u == null) {
            return "You are not logged in";
        }

        $import = FeesDataImport::find(request('id'));
        if ($import == null) {
            return "Fees Data Import not found";
        }

        if ($import->enterprise_id != $u->enterprise_id) {
            return "Access denied. This import belongs to a different enterprise.";
        }

        echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 900px;'>";
        echo "<h2>Retrying Failed Records</h2>";
        echo "<h3>Import: " . htmlspecialchars($import->title) . "</h3>";
        echo "<p>Attempting to retry failed records...</p>";

        try {
            $service = new FeesImportServiceOptimized();
            $result = $service->retryFailedRecords($import);

            if ($result['success']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<strong>✓ Retry Completed</strong><br>";
                echo htmlspecialchars($result['message']);
                echo "</div>";

                if (!empty($result['stats'])) {
                    echo "<h4>Retry Statistics</h4>";
                    echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0;'>";
                    foreach ($result['stats'] as $key => $value) {
                        $key = ucwords(str_replace('_', ' ', $key));
                        echo "<tr style='border-bottom: 1px solid #ddd;'>";
                        echo "<td style='padding: 8px; font-weight: bold;'>{$key}:</td>";
                        echo "<td style='padding: 8px;'>" . htmlspecialchars($value) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<strong>✗ Retry Failed</strong><br>";
                echo htmlspecialchars($result['message']);
                echo "</div>";
            }

            $recordsUrl = url("fees-data-import-records?fees_data_import_id={$import->id}");
            echo "<div style='margin-top: 25px;'>";
            echo "<a href='{$recordsUrl}' class='btn btn-primary' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>";
            echo "View All Records</a>";
            echo " <a href='" . url('fees-data-import') . "' style='margin-left: 10px; padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; display: inline-block;'>Back to Imports</a>";
            echo "</div>";
        } catch (\Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;'>";
            echo "<strong>Retry Error:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</div>";

            Log::error('Fees import retry failed', [
                'import_id' => $import->id,
                'user_id' => $u->id,
                'error' => $e->getMessage()
            ]);
        }

        echo "</div>";
    }

    /**
     * Duplicate Fees Data Import
     */
    public function duplicate()
    {
        $u = Admin::user();
        if ($u == null) {
            admin_error('Authentication Required', 'You are not logged in');
            return redirect('auth/login');
        }

        $import = FeesDataImport::find(request('id'));
        if ($import == null) {
            admin_error('Not Found', 'Fees Data Import not found');
            return redirect('fees-data-import');
        }

        if ($import->enterprise_id != $u->enterprise_id) {
            admin_error('Access Denied', 'This import belongs to a different enterprise.');
            return redirect('fees-data-import');
        }

        try {
            // Create a duplicate with reset status
            $duplicate = $import->replicate();

            // Reset fields that should not be copied
            $duplicate->status = 'Pending';
            $duplicate->batch_identifier = null;
            $duplicate->file_hash = null;
            $duplicate->total_rows = 0;
            $duplicate->processed_rows = 0;
            $duplicate->success_count = 0;
            $duplicate->failed_count = 0;
            $duplicate->skipped_count = 0;
            $duplicate->started_at = null;
            $duplicate->completed_at = null;
            $duplicate->summary = null;
            $duplicate->validation_errors = null;
            $duplicate->is_locked = false;
            $duplicate->locked_by_id = null;
            $duplicate->locked_at = null;

            // Update metadata
            $duplicate->created_by_id = $u->id;
            $duplicate->title = $import->title . ' (Copy)';
            $duplicate->created_at = now();
            $duplicate->updated_at = now();

            $duplicate->save();

            admin_success('Success', 'Import duplicated successfully! You can now modify settings and process this import.');
            return redirect('fees-data-import/' . $duplicate->id . '/edit');
        } catch (\Exception $e) {
            Log::error('Failed to duplicate fees import', [
                'import_id' => $import->id,
                'user_id' => $u->id,
                'error' => $e->getMessage()
            ]);

            admin_error('Duplication Failed', 'Failed to duplicate import: ' . $e->getMessage());
            return redirect('fees-data-import');
        }
    }
}
