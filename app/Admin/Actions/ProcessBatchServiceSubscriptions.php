<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ProcessBatchServiceSubscriptions extends BatchAction
{
    public $name = 'Process Selected';

    public function handle(Collection $collection)
    {
        set_time_limit(600);

        $created   = 0;
        $skipped   = 0;
        $failed    = 0;
        $done      = 0;
        $busy      = 0;
        $empty     = 0;

        foreach ($collection as $rep) {
            if ($rep->is_processed === 'Yes') {
                $done++;
                continue;
            }

            // Claim first. Without this, two runs process the same list in
            // parallel and each reports the other's inserts as "already
            // subscribed" while the counters get overwritten by whoever ends last.
            if (!$rep->claimForProcessing()) {
                $busy++;
                continue;
            }

            try {
                if (empty($rep->administrators)) {
                    $empty++;
                    $rep->releaseLock();
                    continue;
                }

                $result = $rep->processSubscriptions();
                $rep->finishProcessing($result);

                $created += $result['created'];
                $skipped += $result['skipped'];
                $failed  += $result['failed'];
            } catch (\Throwable $e) {
                // Leave the batch unprocessed and unlocked so it can be retried.
                $rep->releaseLock();
                $failed++;
                Log::error("Batch #{$rep->id} processing aborted: " . $e->getMessage());
            }
        }

        $parts = ["{$created} subscription(s) created"];
        if ($skipped) {
            $parts[] = "{$skipped} already subscribed (skipped)";
        }
        if ($failed) {
            $parts[] = "{$failed} failed";
        }
        if ($done) {
            $parts[] = "{$done} batch(es) already processed";
        }
        if ($busy) {
            $parts[] = "{$busy} batch(es) already running elsewhere";
        }
        if ($empty) {
            $parts[] = "{$empty} batch(es) had no subscribers";
        }

        $msg = implode(', ', $parts) . '.';

        if ($failed > 0) {
            return $this->response()->warning($msg)->refresh();
        }

        return $this->response()->success($msg)->refresh();
    }

    public function dialog()
    {
        $this->confirm(
            'Process all selected batches now? Batches already processed, or already running in another tab, are left alone. '
                . 'Students who already hold the service for that term are skipped, not charged twice.'
        );
    }
}
