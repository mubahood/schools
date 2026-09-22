<?php

namespace App\Console\Commands;

use App\Services\Finance\FinanceService;
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
            $acc = FinanceService::unclassifiedAccount((int) $o->enterprise_id);
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
            $acc = FinanceService::unclassifiedAccount((int) $o->enterprise_id);
            DB::table('financial_records as f')
                ->leftJoin('account_parents as ap', 'ap.id', '=', 'f.parent_account_id')
                ->where('f.enterprise_id', $o->enterprise_id)
                ->whereNull('f.deleted_at')->whereNull('ap.id')
                ->update(['f.parent_account_id' => $acc->account_parent_id]);
        }
        if ($voteOrphans->isEmpty()) {
            $this->line('   none');
        }

        // ── 4. transactions whose account was deleted ───────────────────
        $this->newLine();
        $this->info('4. Transactions whose account no longer exists');
        $txOrphans = $scope(DB::table('transactions as t'))
            ->leftJoin('accounts as a', 'a.id', '=', 't.account_id')
            ->whereNull('a.id')
            ->select('t.enterprise_id', DB::raw('COUNT(*) n'), DB::raw('SUM(t.amount) total'))
            ->groupBy('t.enterprise_id')->get();

        foreach ($txOrphans as $o) {
            $this->line(sprintf('   school %-4s %6s rows   net UGX %s', $o->enterprise_id,
                number_format($o->n), number_format($o->total)));
            if (!$apply) {
                continue;
            }
            $acc = FinanceService::unallocatedAccount((int) $o->enterprise_id);
            DB::table('transactions as t')
                ->leftJoin('accounts as a', 'a.id', '=', 't.account_id')
                ->where('t.enterprise_id', $o->enterprise_id)->whereNull('a.id')
                ->update(['t.account_id' => $acc->id]);
        }
        if ($txOrphans->isEmpty()) {
            $this->line('   none');
        } else {
            $this->line('   <fg=gray>moved to "Unallocated (deleted accounts)" — kept out of student fee totals</>');
        }

        // ── 5. accounts whose vote was deleted ──────────────────────────
        $this->newLine();
        $this->info('5. Accounts whose vote no longer exists');
        $accOrphans = $scope(DB::table('accounts as a'))
            ->leftJoin('account_parents as p', 'p.id', '=', 'a.account_parent_id')
            ->whereNotNull('a.account_parent_id')->whereNull('p.id')
            ->select('a.enterprise_id', DB::raw('COUNT(*) n'))->groupBy('a.enterprise_id')->get();

        foreach ($accOrphans as $o) {
            $this->line(sprintf('   school %-4s %6s accounts', $o->enterprise_id, number_format($o->n)));
            if (!$apply) {
                continue;
            }
            // Votes are a finance-module idea. A student account does not need
            // one, so it is cleared rather than filed under Unclassified.
            DB::table('accounts as a')->leftJoin('account_parents as p', 'p.id', '=', 'a.account_parent_id')
                ->where('a.enterprise_id', $o->enterprise_id)
                ->whereNotNull('a.account_parent_id')->whereNull('p.id')
                ->where('a.type', '<>', 'OTHER_ACCOUNT')
                ->update(['a.account_parent_id' => null]);

            $vote = FinanceService::unclassifiedVote((int) $o->enterprise_id);
            DB::table('accounts as a')->leftJoin('account_parents as p', 'p.id', '=', 'a.account_parent_id')
                ->where('a.enterprise_id', $o->enterprise_id)
                ->whereNotNull('a.account_parent_id')->whereNull('p.id')
                ->update(['a.account_parent_id' => $vote->id]);
        }
        if ($accOrphans->isEmpty()) {
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
        $this->line('   transactions with no account: ' . number_format(
            $scope(DB::table('transactions as t'))->leftJoin('accounts as a', 'a.id', '=', 't.account_id')->whereNull('a.id')->count()));
        $this->line('   accounts with a missing vote: ' . number_format(
            $scope(DB::table('accounts as a'))->leftJoin('account_parents as p', 'p.id', '=', 'a.account_parent_id')
                ->whereNotNull('a.account_parent_id')->whereNull('p.id')->count()));

        $kpi = abs((float) $scope(DB::table('financial_records')->whereNull('deleted_at'))->where('type', 'EXPENDITURE')->sum('amount'));
        $byVote = abs((float) $scope(DB::table('financial_records as fr')->whereNull('fr.deleted_at'))
            ->join('account_parents as ap', 'ap.id', '=', 'fr.parent_account_id')
            ->where('fr.type', 'EXPENDITURE')->sum('fr.amount'));
        $this->line(sprintf('   total expenditure %s vs vote breakdown %s → unexplained %s',
            number_format($kpi), number_format($byVote), number_format($kpi - $byVote)));

        return self::SUCCESS;
    }

}
