<?php

namespace App\Admin\Actions;

use App\Models\BatchServiceSubscription;
use App\Models\ServiceSubscription;
use App\Models\User;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class ProcessBatchServiceSubscriptions extends BatchAction
{
    public $name = 'Process Selected';

    public function handle(Collection $collection)
    {
        set_time_limit(180);

        $totalSuccess = 0;
        $totalFail    = 0;
        $skipped      = 0;
        $errors       = [];

        foreach ($collection as $rep) {
            if ($rep->is_processed === 'Yes') {
                $skipped++;
                continue;
            }

            $administrators = $rep->administrators;
            if (empty($administrators)) {
                $skipped++;
                continue;
            }

            $inventoryMode = $rep->to_be_managed_by_inventory ?? 'No';
            $batchItems    = ($inventoryMode === 'Yes') ? $rep->batchItems()->get() : collect();
            $quantity      = max(1, (int) $rep->quantity);

            $success   = 0;
            $fail      = 0;
            $failText  = '';

            foreach ($administrators as $adminId) {
                $user = User::find($adminId);

                if (!$user) {
                    $fail++;
                    $failText .= "User #{$adminId} not found\n";
                    continue;
                }

                $existing = ServiceSubscription::where([
                    'service_id'       => $rep->service_id,
                    'administrator_id' => $user->id,
                    'due_term_id'      => $rep->due_term_id,
                ])->first();

                if ($existing) {
                    $fail++;
                    $failText .= "Already subscribed: {$user->name}\n";
                    continue;
                }

                $sub                             = new ServiceSubscription();
                $sub->service_id                 = $rep->service_id;
                $sub->enterprise_id              = $rep->enterprise_id;
                $sub->administrator_id           = $user->id;
                $sub->quantity                   = $quantity;
                $sub->due_term_id                = $rep->due_term_id;
                $sub->due_academic_year_id       = $rep->due_academic_year_id;
                $sub->link_with                  = $rep->link_with;
                $sub->transport_route_id         = $rep->transport_route_id;
                $sub->trip_type                  = $rep->trip_type;
                $sub->to_be_managed_by_inventory = $inventoryMode;
                $sub->is_service_offered         = 'No';
                $sub->is_completed               = 'No';

                try {
                    $sub->save();

                    if ($inventoryMode === 'Yes' && $batchItems->count() > 0) {
                        foreach ($batchItems as $batchItem) {
                            if (empty($batchItem->stock_item_category_id)) continue;
                            \App\Models\ServiceItemToBeOffered::firstOrCreate(
                                [
                                    'service_subscription_id' => $sub->id,
                                    'stock_item_category_id'  => $batchItem->stock_item_category_id,
                                ],
                                [
                                    'quantity'           => max(1, (int) ($batchItem->quantity ?? 1)),
                                    'is_service_offered' => 'No',
                                    'user_id'            => $user->id,
                                    'enterprise_id'      => $rep->enterprise_id,
                                ]
                            );
                        }
                    }

                    $success++;
                } catch (\Throwable $e) {
                    $fail++;
                    $failText .= "Error for {$user->name}: {$e->getMessage()}\n";
                }
            }

            $rep->is_processed    = 'Yes';
            $rep->success_count   = $success;
            $rep->fail_count      = $fail;
            $rep->total_count     = $success + $fail;
            $rep->processed_notes = $failText ?: null;
            $rep->save();

            $totalSuccess += $success;
            $totalFail    += $fail;

            if ($failText) {
                $errors[] = "Batch #{$rep->id}: {$failText}";
            }
        }

        $msg = "Processed: {$totalSuccess} subscriptions created, {$totalFail} skipped/failed";
        if ($skipped) {
            $msg .= ", {$skipped} batches already done";
        }

        if ($totalFail > 0 && !empty($errors)) {
            return $this->response()->warning($msg)->refresh();
        }

        return $this->response()->success($msg)->refresh();
    }

    public function dialog()
    {
        $this->confirm('Process all selected batches now? Already-processed batches will be skipped.');
    }
}
