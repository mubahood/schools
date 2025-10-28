<?php

namespace App\Services;

use App\Models\CreditPurchase;
use App\Models\ServiceSubscription;
use App\Models\Transaction;
use App\Models\WalletRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Payment Processing Service
 * 
 * Handles automated, safe, and error-free processing of payments
 * with no room for double processing or mistakes.
 */
class PaymentProcessingService
{
    /**
     * Process credit purchase after payment approval
     * 
     * @param CreditPurchase $creditPurchase
     * @return array
     */
    public static function processCreditPurchase(CreditPurchase $creditPurchase)
    {
        // Use database transaction to ensure atomicity
        return DB::transaction(function () use ($creditPurchase) {
            try {
                // Step 1: Validate payment is approved
                if ($creditPurchase->payment_status !== 'Paid') {
                    return [
                        'success' => false,
                        'message' => 'Payment not yet approved. Current status: ' . $creditPurchase->payment_status,
                        'processed' => false
                    ];
                }

                // Step 2: Check if already deposited (prevent double processing)
                if ($creditPurchase->deposit_status === 'Diposited') {
                    return [
                        'success' => false,
                        'message' => 'Credit already deposited. Cannot process twice.',
                        'processed' => false,
                        'already_processed' => true
                    ];
                }

                // Step 3: Verify amount is valid
                if ($creditPurchase->amount <= 0) {
                    throw new Exception('Invalid amount: ' . $creditPurchase->amount);
                }

                // Step 4: Verify enterprise exists
                if (!$creditPurchase->enterprise_id || $creditPurchase->enterprise_id <= 0) {
                    throw new Exception('Invalid enterprise ID');
                }

                // Step 5: Check for duplicate wallet records (extra safety)
                $existingWalletRecord = WalletRecord::where([
                    'enterprise_id' => $creditPurchase->enterprise_id,
                    'details' => 'Purchased credit UGX ' . number_format($creditPurchase->amount) . ' , ref: ' . $creditPurchase->id
                ])->first();

                if ($existingWalletRecord) {
                    // Mark as deposited to prevent future attempts
                    $creditPurchase->deposit_status = 'Diposited';
                    $creditPurchase->save();

                    return [
                        'success' => false,
                        'message' => 'Wallet record already exists. Marking as deposited.',
                        'processed' => false,
                        'already_processed' => true
                    ];
                }

                // Step 6: Create wallet record
                $wallet_rec = new WalletRecord();
                $wallet_rec->enterprise_id = $creditPurchase->enterprise_id;
                $wallet_rec->amount = $creditPurchase->amount;
                $wallet_rec->details = 'Purchased credit UGX ' . number_format($creditPurchase->amount) . ' , ref: ' . $creditPurchase->id;
                $wallet_rec->save();

                // Step 7: Mark as deposited
                $creditPurchase->deposit_status = 'Diposited';
                if (property_exists($creditPurchase, 'processed_at')) {
                    $creditPurchase->processed_at = now();
                }
                $creditPurchase->save();

                // Step 8: Log successful processing
                Log::info('Credit purchase processed successfully', [
                    'credit_purchase_id' => $creditPurchase->id,
                    'enterprise_id' => $creditPurchase->enterprise_id,
                    'amount' => $creditPurchase->amount,
                    'wallet_record_id' => $wallet_rec->id
                ]);

                return [
                    'success' => true,
                    'message' => 'Credit deposited successfully',
                    'processed' => true,
                    'wallet_record_id' => $wallet_rec->id,
                    'amount' => $creditPurchase->amount
                ];
            } catch (Exception $e) {
                // Log error
                Log::error('Credit purchase processing failed', [
                    'credit_purchase_id' => $creditPurchase->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e; // Re-throw to trigger DB rollback
            }
        });
    }

    /**
     * Process service subscription items after payment
     * 
     * @param ServiceSubscription $subscription
     * @return array
     */
    public static function processServiceSubscription(ServiceSubscription $subscription)
    {
        return DB::transaction(function () use ($subscription) {
            try {
                // Step 1: Check if already processed (prevent double processing)
                if ($subscription->is_processed === 'Yes') {
                    return [
                        'success' => false,
                        'message' => 'Subscription already processed',
                        'processed' => false,
                        'already_processed' => true
                    ];
                }

                // Step 2: If no items, mark as processed
                if ($subscription->items->isEmpty()) {
                    $subscription->is_processed = 'Yes';
                    if (property_exists($subscription, 'processed_at')) {
                        $subscription->processed_at = now();
                    }
                    $subscription->save();

                    return [
                        'success' => true,
                        'message' => 'No items to process. Marked as processed.',
                        'processed' => true,
                        'items_count' => 0
                    ];
                }

                $processedCount = 0;
                $failedCount = 0;
                $errors = [];

                // Step 3: Process each item
                foreach ($subscription->items as $key => $item) {
                    try {
                        // Skip already processed items
                        if ($item->is_processed === 'Yes') {
                            continue;
                        }

                        // Validate service exists
                        $service = \App\Models\Service::find($item->service_id);
                        if (!$service) {
                            $errors[] = "Service not found for item {$item->id}";
                            $failedCount++;
                            continue;
                        }

                        // Create new subscription
                        $newSub = new ServiceSubscription();
                        $newSub->enterprise_id = $subscription->enterprise_id;
                        $newSub->service_id = $item->service_id;
                        $newSub->administrator_id = $subscription->administrator_id;
                        $newSub->quantity = $item->quantity;
                        $newSub->total = $service->fee * $item->quantity;
                        $newSub->due_academic_year_id = $subscription->due_academic_year_id;
                        $newSub->due_term_id = $subscription->due_term_id;
                        $newSub->is_processed = 'Yes';
                        if (property_exists($newSub, 'processed_at')) {
                            $newSub->processed_at = now();
                        }
                        $newSub->save();

                        // Mark item as processed
                        $item->is_processed = 'Yes';
                        if (property_exists($item, 'processed_at')) {
                            $item->processed_at = now();
                        }
                        if (property_exists($item, 'processed_subscription_id')) {
                            $item->processed_subscription_id = $newSub->id;
                        }
                        $item->save();

                        $processedCount++;

                        // Log successful item processing
                        Log::info('Service subscription item processed', [
                            'parent_subscription_id' => $subscription->id,
                            'item_id' => $item->id,
                            'new_subscription_id' => $newSub->id,
                            'service_id' => $item->service_id
                        ]);
                    } catch (Exception $e) {
                        $errors[] = "Error processing item {$item->id}: " . $e->getMessage();
                        $failedCount++;

                        Log::error('Service subscription item processing failed', [
                            'parent_subscription_id' => $subscription->id,
                            'item_id' => $item->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Step 4: Mark parent subscription as processed
                $subscription->is_processed = 'Yes';
                if (property_exists($subscription, 'processed_at')) {
                    $subscription->processed_at = now();
                }
                if (property_exists($subscription, 'processed_count')) {
                    $subscription->processed_count = $processedCount;
                }
                if (property_exists($subscription, 'failed_count')) {
                    $subscription->failed_count = $failedCount;
                }
                $subscription->save();

                // Step 5: Log final result
                Log::info('Service subscription processing completed', [
                    'subscription_id' => $subscription->id,
                    'processed_count' => $processedCount,
                    'failed_count' => $failedCount
                ]);

                return [
                    'success' => true,
                    'message' => "Processed {$processedCount} items, {$failedCount} failed",
                    'processed' => true,
                    'processed_count' => $processedCount,
                    'failed_count' => $failedCount,
                    'errors' => $errors
                ];
            } catch (Exception $e) {
                Log::error('Service subscription processing failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e; // Trigger DB rollback
            }
        });
    }
}
