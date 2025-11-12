<?php

namespace App\Admin\Controllers;

use App\Models\FeesDataImportRecord;
use App\Models\FeesDataImport;
use App\Models\User;
use App\Models\Account;
use App\Models\Service;
use App\Services\FeesImportServiceOptimized;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use App\Models\Utils;
use Illuminate\Support\Facades\DB;

class FeesDataImportRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Import Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FeesDataImportRecord());
        $user = Admin::user();
        
        // Only show records for current enterprise
        $grid->model()->where('enterprise_id', $user->enterprise_id)
            ->with(['import', 'user', 'account'])
            ->orderBy('created_at', 'desc');

        // Quick Search
        $grid->quickSearch('id', 'reg_number', 'school_pay', 'status', 'summary')
            ->placeholder('Search by ID, Reg Number, Pay Code, Status or Summary');

        // Advanced Filters
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            
            // Status filter
            $filter->equal('status', 'Status')->select([
                'Pending' => 'Pending',
                'Processing' => 'Processing',
                'Completed' => 'Completed',
                'Failed' => 'Failed',
                'Skipped' => 'Skipped',
            ]);
            
            // Import batch filter
            $filter->equal('fees_data_import_id', 'Import Batch')->select(function() {
                return FeesDataImport::where('enterprise_id', Admin::user()->enterprise_id)
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->pluck('batch_identifier', 'id');
            });
            
            // Date range filter
            $filter->between('created_at', 'Created Date')->datetime();
            $filter->between('processed_at', 'Processed Date')->datetime();
            
            // User filter (student)
            $filter->where(function ($query) {
                $user = User::where('id', $this->input)->first();
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            }, 'Student Name', 'user_id')->select(function() {
                return User::where('enterprise_id', Admin::user()->enterprise_id)
                    ->where('user_type', 'student')
                    ->orderBy('name')
                    ->limit(100)
                    ->pluck('name', 'id');
            });
            
            // Retry count filter
            $filter->gt('retry_count', 'Retried');
        });

        // Columns
        $grid->column('id', 'ID')->sortable();
        
        $grid->column('fees_data_import_id', 'Batch')->display(function($importId) {
            if ($this->import) {
                $batch = substr($this->import->batch_identifier ?? 'N/A', 0, 12) . '...';
                return "<a href='" . admin_url('fees-data-imports/' . $importId) . "' target='_blank' title='{$this->import->batch_identifier}'>" . 
                       "<span class='label label-info'>{$batch}</span></a>";
            }
            return "<span class='label label-default'>{$importId}</span>";
        });
        
        $grid->column('index', 'Row#')->sortable()->label('info');
        
        $grid->column('user_id', 'Student')->display(function ($userId) {
            if ($this->user) {
                $name = $this->user->name;
                $regNo = $this->reg_number ?? '';
                return "<a href='" . admin_url('students/' . $userId) . "' target='_blank'>" . 
                       "<strong>" . e($name) . "</strong><br>" .
                       "<small class='text-muted'>" . e($regNo) . "</small></a>";
            }
            return "<span class='text-muted'>" . e($this->reg_number ?? 'N/A') . "</span>";
        });
        
        $grid->column('school_pay', 'Pay Code')->sortable()->label('default');
        
        $grid->column('account_id', 'Account')->display(function($accountId) {
            if ($this->account) {
                $balance = number_format($this->account->balance ?? 0, 0);
                return "<span title='Current Account Balance'><strong>UGX {$balance}</strong></span>";
            }
            return '<span class="text-muted">N/A</span>';
        });
        
        $grid->column('previous_fees_term_balance', 'Prev Balance')->display(function($amount) {
            if ($amount > 0) {
                return "<span class='text-danger'>UGX " . number_format($amount, 0) . "</span>";
            } elseif ($amount < 0) {
                return "<span class='text-success'>UGX " . number_format(abs($amount), 0) . " CR</span>";
            }
            return "<span class='text-muted'>UGX 0</span>";
        })->sortable();
        
        $grid->column('updated_balance', 'Updated Balance')->display(function($amount) {
            if (!is_null($amount)) {
                if ($amount > 0) {
                    return "<span class='text-danger'>UGX " . number_format($amount, 0) . "</span>";
                } elseif ($amount < 0) {
                    return "<span class='text-success'>UGX " . number_format(abs($amount), 0) . " CR</span>";
                }
                return "<span class='text-muted'>UGX 0</span>";
            }
            return "<span class='text-muted'>-</span>";
        })->sortable()->hide();
        
        $grid->column('total_amount', 'Total Amount')->display(function($amount) {
            return "<strong>UGX " . number_format($amount ?? 0, 0) . "</strong>";
        })->sortable()->totalRow(function ($amount) {
            return "<strong style='color: #3c8dbc;'>UGX " . number_format($amount, 0) . "</strong>";
        });
        
        $grid->column('status', 'Status')->display(function($status) {
            $badges = [
                'Pending' => 'default',
                'Processing' => 'info',
                'Completed' => 'success',
                'Failed' => 'danger',
                'Skipped' => 'warning',
            ];
            $badge = $badges[$status] ?? 'default';
            
            $retryInfo = '';
            if ($this->retry_count > 0) {
                $retryInfo = " <small>({$this->retry_count} retries)</small>";
            }
            
            return "<span class='label label-{$badge}'>{$status}</span>{$retryInfo}";
        })->sortable();
        
        $grid->column('summary', 'Summary')->display(function($summary) {
            if (empty($summary)) return '<span class="text-muted">-</span>';
            
            // Parse summary for service counts
            if (preg_match('/(\d+)\s+service/i', $summary, $matches)) {
                $count = $matches[1];
                return "<span title='{$summary}'><i class='fa fa-list'></i> {$count} services</span>";
            }
            
            return "<span title='{$summary}'>" . e(substr($summary, 0, 50)) . "...</span>";
        })->hide();
        
        $grid->column('error_message', 'Error')->display(function($error) {
            if (empty($error)) return '<span class="text-muted">-</span>';
            
            return "<span class='text-danger' title='" . e($error) . "'>" . 
                   "<i class='fa fa-exclamation-circle'></i> " . 
                   e(substr($error, 0, 50)) . "...</span>";
        });
        
        $grid->column('processed_at', 'Processed')->display(function($date) {
            if ($date) {
                return Utils::my_date_3($date);
            }
            return '<span class="text-muted">-</span>';
        })->sortable()->hide();
        
        $grid->column('services_data', 'Services')->display(function ($json) {
            if (empty($json)) return '<span class="text-muted">-</span>';
            
            $services = json_decode($json, true);
            if (!is_array($services) || empty($services)) {
                return '<span class="text-muted">No services</span>';
            }
            
            $html = '<ul style="margin:0;padding-left:15px;">';
            foreach ($services as $service) {
                $name = $service['service_name'] ?? 'Unknown';
                $amount = number_format($service['service_amount'] ?? 0, 0);
                $html .= "<li><strong>{$name}</strong>: UGX {$amount}</li>";
            }
            $html .= '</ul>';
            
            return $html;
        })->hide();

        // Batch Actions for Failed Records
        $grid->batchActions(function ($batch) {
            $batch->add(new \App\Admin\Actions\BatchRetryFailedRecords());
        });

        // Export
        $grid->exporter(new \App\Admin\Exporters\FeesDataImportRecordsExporter());

        // Disable creation
        $grid->disableCreateButton();

        // Only allow view
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            
            // Add retry button for failed records
            if ($actions->row->status === 'Failed' && $actions->row->retry_count < 3) {
                $actions->add(new \App\Admin\Actions\Row\RetryFailedRecord());
            }
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
        $show = new Show(FeesDataImportRecord::with(['import', 'user', 'account'])->findOrFail($id));

        $show->panel()->style('info')->title('Import Record Details');

        // Basic Information
        $show->divider('Basic Information');
        $show->field('id', 'Record ID');
        $show->field('fees_data_import_id', 'Import Batch')->as(function($importId) {
            if ($this->import) {
                return "<a href='" . admin_url('fees-data-imports/' . $importId) . "'>" . 
                       $this->import->batch_identifier . "</a>";
            }
            return $importId;
        })->unescape();
        $show->field('index', 'Row Number in File');
        $show->field('row_hash', 'Row Hash (Duplicate Detection)');
        
        // Student Information
        $show->divider('Student Information');
        $show->field('identify_by', 'Identified By')->using([
            'reg_number' => 'Registration Number',
            'school_pay' => 'School Pay Code',
        ]);
        $show->field('reg_number', 'Registration Number');
        $show->field('user_id', 'Student')->as(function($userId) {
            if ($this->user) {
                return "<a href='" . admin_url('students/' . $userId) . "'>" . 
                       $this->user->name . " (ID: {$userId})</a>";
            }
            return 'Not found';
        })->unescape();
        $show->field('school_pay', 'School Pay Code');
        $show->field('account_id', 'Account')->as(function($accountId) {
            if ($this->account) {
                $balance = number_format($this->account->balance ?? 0, 0);
                return "Account ID: {$accountId}<br>Current Balance: <strong>UGX {$balance}</strong>";
            }
            return 'Not found';
        })->unescape();
        
        // Financial Information
        $show->divider('Financial Information');
        $show->field('previous_fees_term_balance', 'Previous Term Balance')->as(function($amount) {
            if ($amount > 0) {
                return "<span style='color: red;'>UGX " . number_format($amount, 2) . " (Debt)</span>";
            } elseif ($amount < 0) {
                return "<span style='color: green;'>UGX " . number_format(abs($amount), 2) . " (Credit)</span>";
            }
            return "UGX 0.00";
        })->unescape();
        $show->field('updated_balance', 'Updated Balance')->as(function($amount) {
            if (!is_null($amount)) {
                if ($amount > 0) {
                    return "<span style='color: red;'>UGX " . number_format($amount, 2) . "</span>";
                } elseif ($amount < 0) {
                    return "<span style='color: green;'>UGX " . number_format(abs($amount), 2) . " (Credit)</span>";
                }
                return "UGX 0.00";
            }
            return 'Not calculated';
        })->unescape();
        $show->field('current_balance', 'Current Balance at Import')->as(function($amount) {
            if ($amount > 0) {
                return "<span style='color: red;'>UGX " . number_format($amount, 2) . "</span>";
            } elseif ($amount < 0) {
                return "<span style='color: green;'>UGX " . number_format(abs($amount), 2) . " (Credit)</span>";
            }
            return "UGX 0.00";
        })->unescape();
        $show->field('total_amount', 'Total Services Amount')->as(function($amount) {
            return "<strong>UGX " . number_format($amount ?? 0, 2) . "</strong>";
        })->unescape();
        
        // Services Details
        $show->divider('Services Details');
        $show->field('services_data', 'Services Applied')->as(function ($json) {
            if (empty($json)) return '<em>No services</em>';
            
            $services = json_decode($json, true);
            if (!is_array($services) || empty($services)) {
                return '<em>No services</em>';
            }
            
            $html = '<table class="table table-bordered table-striped">';
            $html .= '<thead><tr><th>Service Name</th><th>Amount</th><th>Status</th></tr></thead>';
            $html .= '<tbody>';
            
            foreach ($services as $service) {
                $name = $service['service_name'] ?? 'Unknown';
                $amount = number_format($service['service_amount'] ?? 0, 2);
                $status = $service['status'] ?? 'Applied';
                $html .= "<tr><td>{$name}</td><td>UGX {$amount}</td><td>{$status}</td></tr>";
            }
            
            $html .= '</tbody></table>';
            return $html;
        })->unescape();
        
        // Processing Status
        $show->divider('Processing Status');
        $show->field('status', 'Status')->as(function ($status) {
            $badges = [
                'Pending' => 'default',
                'Processing' => 'info',
                'Completed' => 'success',
                'Failed' => 'danger',
                'Skipped' => 'warning',
            ];
            $badge = $badges[$status] ?? 'default';
            return "<span class='label label-{$badge}' style='font-size:14px;'>{$status}</span>";
        })->unescape();
        $show->field('retry_count', 'Retry Attempts')->as(function($count) {
            if ($count > 0) {
                return "<span class='label label-warning'>{$count} retries</span>";
            }
            return "<span class='text-muted'>No retries</span>";
        })->unescape();
        $show->field('transaction_hash', 'Transaction Hash');
        $show->field('summary', 'Processing Summary')->unescape();
        $show->field('error_message', 'Error Message')->as(function($error) {
            if (empty($error)) return '<em class="text-muted">No errors</em>';
            return "<div class='alert alert-danger'><i class='fa fa-exclamation-circle'></i> {$error}</div>";
        })->unescape();
        
        // Timestamps
        $show->divider('Timestamps');
        $show->field('created_at', 'Created At')->as(function($date) {
            return Utils::my_date_3($date);
        });
        $show->field('processed_at', 'Processed At')->as(function($date) {
            return $date ? Utils::my_date_3($date) : '<em class="text-muted">Not processed yet</em>';
        })->unescape();
        $show->field('updated_at', 'Last Updated')->as(function($date) {
            return Utils::my_date_3($date);
        });
        
        // Raw Data (collapsed by default)
        $show->divider('Raw Data (Technical)');
        $show->field('data', 'Raw Row Data')->as(function ($json) {
            if (empty($json)) return '<em>No data</em>';
            return "<pre style='max-height:300px;overflow:auto;background:#f5f5f5;padding:10px;'>" . 
                   e(json_encode(json_decode($json), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . 
                   "</pre>";
        })->unescape();

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FeesDataImportRecord());

        // Records are generated by import process—no manual creation or editing allowed
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->disableReset();
        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();
        
        // Add informational message
        $form->html('<div class="alert alert-info">
            <i class="fa fa-info-circle"></i> 
            <strong>Note:</strong> Import records are automatically created during the import process and cannot be manually created or edited. 
            Use the <a href="' . admin_url('fees-data-imports') . '">Fees Data Imports</a> page to manage imports.
        </div>');

        // Only display fields (read-only)
        $form->display('id', 'Record ID');
        $form->display('fees_data_import_id', 'Import Batch ID');
        $form->display('index', 'Row Number');
        $form->display('identify_by', 'Identified By');
        $form->display('reg_number', 'Registration Number');
        $form->display('school_pay', 'School Pay Code');
        $form->display('user_id', 'Student User ID');
        $form->display('account_id', 'Account ID');
        $form->display('previous_fees_term_balance', 'Previous Term Balance');
        $form->display('updated_balance', 'Updated Balance');
        $form->display('current_balance', 'Current Balance');
        $form->display('total_amount', 'Total Amount');
        $form->display('status', 'Status');
        $form->display('retry_count', 'Retry Count');
        $form->display('summary', 'Summary');
        $form->display('error_message', 'Error Message');
        $form->display('row_hash', 'Row Hash');
        $form->display('transaction_hash', 'Transaction Hash');
        $form->display('processed_at', 'Processed At');
        $form->display('created_at', 'Created At');
        $form->display('updated_at', 'Updated At');

        return $form;
    }
}
