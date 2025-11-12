<?php

namespace App\Admin\Actions\Row;

use App\Models\FeesDataImportRecord;
use App\Services\FeesImportServiceOptimized;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RetryFailedRecord extends RowAction
{
    public $name = 'Retry';

    public function handle(Model $model)
    {
        try {
            /** @var FeesDataImportRecord $model */
            if ($model->status !== 'Failed') {
                return $this->response()->error('Only failed records can be retried.')->refresh();
            }

            if ($model->retry_count >= 3) {
                return $this->response()->error('Maximum retry attempts (3) reached for this record.')->refresh();
            }

            $service = new FeesImportServiceOptimized();
            $result = $service->retrySingleRecord($model);

            if ($result['success']) {
                return $this->response()->success($result['message'])->refresh();
            } else {
                return $this->response()->error($result['message'])->refresh();
            }
        } catch (\Exception $e) {
            Log::error('Row retry action failed', [
                'record_id' => $model->id,
                'error' => $e->getMessage(),
            ]);

            return $this->response()->error('Retry failed: ' . $e->getMessage())->refresh();
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to retry this failed record?', '', [
            'confirmButtonText' => 'Yes, Retry',
            'confirmButtonColor' => '#3c8dbc',
        ]);
    }
}
