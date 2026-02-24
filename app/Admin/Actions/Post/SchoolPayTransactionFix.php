<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class SchoolPayTransactionFix extends BatchAction
{
    public $name = 'Fix Descriptions & Dates';

    public function handle(Collection $collection, Request $r)
    {
        $confirm = $r->get('confirm_fix');
        if ($confirm != 'Yes') {
            return $this->response()->error("You must confirm to proceed.")->refresh();
        }

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $fixed = 0;
        $skipped = 0;
        $errors = 0;
        $messages = [];

        foreach ($collection as $model) {
            try {
                $result = $model->fix();
                if ($result['success']) {
                    if (str_contains($result['message'], 'already up to date')) {
                        $skipped++;
                    } else {
                        $fixed++;
                    }
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                $messages[] = "Error on #{$model->id}: " . $e->getMessage();
            }
        }

        $summary = "Fixed: {$fixed}, Already OK: {$skipped}, Errors: {$errors}";
        if (!empty($messages)) {
            $summary .= " | " . implode('; ', array_slice($messages, 0, 5));
        }

        return $this->response()->success($summary)->refresh();
    }

    public function form()
    {
        $this->radio('confirm_fix', __('This will fix the description and created_at date on the linked Transaction records for all selected School Pay transactions. Proceed?'))
            ->options([
                'Yes' => 'Yes, fix them',
                'No' => 'No, cancel',
            ]);
    }
}
