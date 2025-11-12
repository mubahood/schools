<?php

namespace App\Admin\Actions;

use App\Models\FeesDataImportRecord;
use App\Services\FeesImportServiceOptimized;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class BatchRetryFailedRecords extends BatchAction
{
    public $name = 'Retry Failed Records';

    public function handle(Collection $collection)
    {
        try {
            $service = new FeesImportServiceOptimized();
            
            // Filter only failed records that can be retried
            $retryableRecords = $collection->filter(function ($record) {
                return $record->status === 'Failed' && $record->retry_count < 3;
            });
            
            if ($retryableRecords->isEmpty()) {
                return $this->response()->error('No retryable records found. Records must be Failed and have less than 3 retry attempts.')->refresh();
            }
            
            $successCount = 0;
            $failedCount = 0;
            $skippedCount = 0;
            
            foreach ($retryableRecords as $record) {
                $result = $service->retrySingleRecord($record);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    // Check if it was skipped or failed
                    if (strpos($result['message'], 'Maximum retry') !== false) {
                        $skippedCount++;
                    } else {
                        $failedCount++;
                    }
                }
            }
            
            // Update import statistics if all records belong to same import
            if ($retryableRecords->isNotEmpty()) {
                $firstRecord = $retryableRecords->first();
                if ($firstRecord && $firstRecord->import) {
                    $import = $firstRecord->import;
                    $import->updateProgress();
                }
            }
            
            return $this->response()->success(
                "Retry completed: {$successCount} succeeded, {$failedCount} failed, {$skippedCount} skipped."
            )->refresh();
            
        } catch (\Exception $e) {
            Log::error('Batch retry action failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->response()->error('Retry failed: ' . $e->getMessage())->refresh();
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to retry the selected failed records?', '', [
            'confirmButtonText' => 'Yes, Retry',
            'confirmButtonColor' => '#d9534f',
        ]);
    }
}
