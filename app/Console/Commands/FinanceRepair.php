<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountParent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repairs the three ways the ledger drifted before the module was hardened.
 * Dry by default: nothing is written unless --apply is passed.
 *
 * The amount on a record is treated as the truth throughout. Quantity and unit
 * price are adjusted to agree with it, never the other way round, because the
 * amount is what was actually spent.
 */
class FinanceRepair extends Command
{
    protected $signature = 'finance:repair {--apply : write the changes} {--enterprise= : limit to one school}';

    protected $description = 'Reconcile quantity x unit price with amount, and rehome records whose account or vote was deleted';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $only = $this->option('enterprise');
        $this->line($apply ? '<comment>APPLYING CHANGES</comment>' : '<info>DRY RUN — nothing will be written (use --apply)</info>');
        $this->newLine();

        $scope = fn ($q) => $only ? $q->where('enterprise_id', $only) : $q;

        // ── 1. amount <> quantity x unit_price ──────────────────────────
        $this->info('1. Lines whose quantity x unit price does not equal the amount');
        $bad = $scope(DB::table('financial_records')->whereNull('deleted_at'))
            ->whereRaw('ABS(amount) <> ABS(quantity * unit_price)')
            ->select('id', 'amount', 'quantity', 'unit_price')->get();

        $exact = 0; $collapsed = 0;
        foreach ($bad as $r) {
            $abs = abs((int) $r->amount);
            $qty = max(1, (int) $r->quantity);
            // Keep the quantity when the amount divides by it cleanly, so a
            // genuine "7 items at X" keeps reading as 7 items.
            if ($qty > 1 && $abs % $qty === 0) {
                $set = ['quantity' => $qty, 'unit_price' => intdiv($abs, $qty)];
                $exact++;
            } else {
                $set = ['quantity' => 1, 'unit_price' => $abs];
                $collapsed++;
            }
            if ($apply) {
                DB::table('financial_records')->where('id', $r->id)->update($set);
            }
        }
        $this->line(sprintf('   %s lines: %s kept their quantity, %s collapsed to 1 x amount',
            number_format($bad->count()), number_format($exact), number_format($collapsed)));
        $this->line('   <fg=gray>the amount is never changed, so not a shilling moves</>');

        // ── 2. records pointing at a deleted account ────────────────────
        $this->newLine();
        $this->info('2. Lines whose account no longer exists');
        $orphans = $scope(DB::table('financial_records as f')->whereNull('f.deleted_at'))
            ->leftJoin('accounts as a', 'a.id', '=', 'f.account_id')
            ->whereNull('a.id')
            ->select('f.enterprise_id', DB::raw('COUNT(*) n'), DB::raw('SUM(ABS(f.amount)) total'))
            ->groupBy('f.enterprise_id')->get();

        foreach ($orphans as $o) {
            $this->line(sprintf('   school %-4s %6s lines   UGX %s', $o->enterprise_id,
                number_format($o->n), number_format($o->total)));
            if (!$apply) {
                continue;
            }
            $acc = $this->unclassifiedAccount((int) $o->enterprise_id);
            DB::table('financial_records as f')
                ->leftJoin('accounts as a', 'a.id', '=', 'f.account_id')
                ->where('f.enterprise_id', $o->enterprise_id)
                ->whereNull('f.deleted_at')->whereNull('a.id')
                ->update(['f.account_id' => $acc->id, 'f.parent_account_id' => $acc->account_parent_id]);
        }
        if ($orphans->isEmpty()) {
            $this->line('   none');
        } else {
            $this->line('   <fg=gray>rehomed to an "Unclassified" account so reports stop dropping them</>');
        }

        // ── 3. records pointing at a deleted vote ───────────────────────
        $this->newLine();
        $this->info('3. Lines whose vote no longer exists');
        $voteOrphans = $scope(DB::table('financial_records as f')->whereNull('f.deleted_at'))
            ->leftJoin('account_parents as ap', 'ap.id', '=', 'f.parent_account_id')
            ->whereNull('ap.id')
            ->select('f.enterprise_id', DB::raw('COUNT(*) n'), DB::raw('SUM(ABS(f.amount)) total'))
            ->groupBy('f.enterprise_id')->get();

        foreach ($voteOrphans as $o) {
            $this->line(sprintf('   school %-4s %6s lines   UGX %s', $o->enterprise_id,
                number_format($o->n), number_format($o->total)));
            if (!$apply) {
                continue;
            }
            $acc = $this->unclassifiedAccount((int) $o->enterprise_id);
            DB::table('financial_records as f')
                ->leftJoin('account_parents as ap', 'ap.id', '=', 'f.parent_account_id')
                ->where('f.enterprise_id', $o->enterprise_id)
                ->whereNull('f.deleted_at')->whereNull('ap.id')
                ->update(['f.parent_account_id' => $acc->account_parent_id]);
        }
        if ($voteOrphans->isEmpty()) {
            $this->line('   none');
        }

        // ── verification ────────────────────────────────────────────────
        $this->newLine();
        $this->info('After:');
        $stillBad = $scope(DB::table('financial_records')->whereNull('deleted_at'))
            ->whereRaw('ABS(amount) <> ABS(quantity * unit_price)')->count();
        $stillOrphan = $scope(DB::table('financial_records as f')->whereNull('f.deleted_at'))
            ->leftJoin('accounts as a', 'a.id', '=', 'f.account_id')->whereNull('a.id')->count();
        $stillVote = $scope(DB::table('financial_records as f')->whereNull('f.deleted_at'))
            ->leftJoin('account_parents as ap', 'ap.id', '=', 'f.parent_account_id')->whereNull('ap.id')->count();
        $this->line('   quantity x price mismatches : ' . number_format($stillBad));
        $this->line('   lines with a missing account: ' . number_format($stillOrphan));
        $this->line('   lines with a missing vote   : ' . number_format($stillVote));

        $kpi = abs((float) $scope(DB::table('financial_records')->whereNull('deleted_at'))->where('type', 'EXPENDITURE')->sum('amount'));
        $byVote = abs((float) $scope(DB::table('financial_records as fr')->whereNull('fr.deleted_at'))
            ->join('account_parents as ap', 'ap.id', '=', 'fr.parent_account_id')
            ->where('fr.type', 'EXPENDITURE')->sum('fr.amount'));
        $this->line(sprintf('   total expenditure %s vs vote breakdown %s → unexplained %s',
            number_format($kpi), number_format($byVote), number_format($kpi - $byVote)));

        return self::SUCCESS;
    }

    /** One per school, created on demand, never shown as a choice when spending. */
    private function unclassifiedAccount(int $eid): Account
    {
        // AccountParent declares no $fillable, so it is built explicitly.
        $parent = AccountParent::where('enterprise_id', $eid)->where('name', 'Unclassified')->first();
        if (!$parent) {
            $parent = new AccountParent();
            $parent->enterprise_id = $eid;
            $parent->name = 'Unclassified';
            $parent->description = 'Holds records whose original vote was deleted. Reassign them to a real vote.';
            $parent->save();
        }
        $acc = Account::where('enterprise_id', $eid)->where('name', 'Unclassified')->first();
        if (!$acc) {
            $acc = new Account();
            $acc->enterprise_id = $eid;
            $acc->administrator_id = optional(\App\Models\Enterprise::find($eid))->administrator_id ?: 1;
            $acc->name = 'Unclassified';
            $acc->type = 'OTHER_ACCOUNT';
            $acc->is_system = 1;
            $acc->status = 1;
            $acc->is_balance_verified = 0;
            $acc->balance = 0;
            $acc->description = 'Holds records whose original account was deleted. Reassign them to a real account.';
            $acc->account_parent_id = $parent->id;
            $acc->save();
        } elseif (!$acc->account_parent_id) {
            $acc->account_parent_id = $parent->id;
            $acc->is_system = 1;
            $acc->save();
        }

        return $acc;
    }
}
