<?php

namespace App\Console\Commands;

use App\Models\CreditPurchase;
use App\Models\Enterprise;
use App\Models\WalletRecord;
use App\Services\PaymentProcessingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repairs the damage from the CreditPurchase::boot() typo:
 *  - purchases stuck at payment_status='Diposited' (a state that never existed)
 *  - paid purchases whose wallet credit was never written
 *  - purchases credited more than once
 *  - stale enterprises.wallet_balance caches
 * Dry-run by default.
 */
class RepairCreditPurchases extends Command
{
    protected $signature = 'billing:repair-credit-purchases {--apply : Write the repairs}';
    protected $description = 'Fix stranded / duplicated SMS credit purchases and recompute wallet caches';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $this->info($apply ? 'APPLYING repairs.' : 'DRY RUN — pass --apply to write.');

        DB::beginTransaction();
        try {
            // 1. Normalise the nonsense state written by the old hook.
            $bad = CreditPurchase::where('payment_status', 'Diposited')->get();
            $this->line("  purchases with payment_status='Diposited': {$bad->count()} -> set to Paid");
            foreach ($bad as $p) {
                DB::table('credit_purchases')->where('id', $p->id)->update(['payment_status' => 'Paid']);
            }

            // 2. Duplicate credits: keep the first wallet record per ref, remove extras.
            $dups = DB::select("SELECT SUBSTRING_INDEX(details,'ref: ',-1) ref, COUNT(*) c
                FROM wallet_records WHERE details LIKE 'Purchased credit%' GROUP BY ref HAVING c > 1");
            $removed = 0;
            foreach ($dups as $d) {
                $ids = DB::table('wallet_records')->where('details', 'like', 'Purchased credit%')
                    ->where('details', 'like', '%ref: ' . $d->ref)->orderBy('id')->pluck('id')->slice(1)->all();
                foreach ($ids as $id) {
                    // WalletRecord forbids delete via the model; this is a deliberate repair.
                    DB::table('wallet_records')->where('id', $id)->delete();
                    $removed++;
                }
            }
            $this->line("  duplicate wallet credits removed: {$removed}");

            // 3. Paid but never credited -> credit once via the idempotent service.
            $stuck = CreditPurchase::where('payment_status', 'Paid')->where(function ($q) {
                $q->whereNull('deposit_status')->orWhere('deposit_status', '<>', 'Diposited');
            })->get();
            $credited = 0;
            foreach ($stuck as $p) {
                $exists = WalletRecord::where('enterprise_id', $p->enterprise_id)
                    ->where('details', 'like', '%ref: ' . $p->id)->exists();
                if ($exists) {
                    DB::table('credit_purchases')->where('id', $p->id)->update(['deposit_status' => 'Diposited']);
                    continue;
                }
                $r = PaymentProcessingService::processCreditPurchase($p->fresh());
                if ($r['processed'] ?? false) {
                    $credited++;
                    $this->line(sprintf("    credited purchase #%d -> enterprise %d, UGX %s", $p->id, $p->enterprise_id, number_format($p->amount)));
                }
            }
            $this->line("  stranded purchases credited: {$credited}");

            // 4. Recompute every wallet cache from the ledger.
            $fixed = 0;
            foreach (Enterprise::all() as $e) {
                $sum = (int) DB::table('wallet_records')->where('enterprise_id', $e->id)->sum('amount');
                if ($sum !== (int) $e->wallet_balance) {
                    DB::table('enterprises')->where('id', $e->id)->update(['wallet_balance' => $sum]);
                    $fixed++;
                }
            }
            $this->line("  wallet caches corrected: {$fixed}");

            if ($apply) {
                DB::commit();
                $this->info('Repairs written.');
            } else {
                DB::rollBack();
                $this->info('Rolled back (dry run).');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed, nothing written: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
