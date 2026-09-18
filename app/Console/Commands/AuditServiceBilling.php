<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Integrity audit for service subscriptions and the fees they raise.
 *
 * Every defect this looks for has actually happened in production. Run it after
 * any bulk subscription work, or on a schedule, so a silent divergence is caught
 * while it is still small.
 *
 *   php artisan services:audit-billing
 *   php artisan services:audit-billing --enterprise=19
 *   php artisan services:audit-billing --fix-terms      (metadata only)
 */
class AuditServiceBilling extends Command
{
    protected $signature = 'services:audit-billing
                            {--enterprise= : Limit to one school}
                            {--fix-terms : Re-tag charges that name a term the student never subscribed for}';

    protected $description = 'Audit service subscriptions against the fees they should have raised';

    public function handle()
    {
        $ent    = $this->option('enterprise');
        $issues = 0;

        $this->line('');
        $this->info('Service billing audit' . ($ent ? " — enterprise {$ent}" : ' — all schools'));
        $this->line(str_repeat('=', 72));

        $issues += $this->neverBilled($ent);
        $issues += $this->markerWithoutCharge($ent);
        $issues += $this->misTaggedTerms($ent, (bool) $this->option('fix-terms'));
        $issues += $this->duplicateSubscriptions($ent);
        $issues += $this->duplicateClassFees($ent);
        $issues += $this->duplicateBursaryCredits($ent);
        $issues += $this->unpaidBursaryAwards($ent);
        $issues += $this->stuckBatchLocks($ent);

        $this->line('');
        if ($issues === 0) {
            $this->info('No issues found.');
            return 0;
        }

        $this->warn("{$issues} issue(s) found.");
        return 1;
    }

    /** Subscription with no billing marker: the fee was never raised. */
    private function neverBilled($ent)
    {
        $rows = DB::table('service_subscriptions as ss')
            ->leftJoin('fee_deposit_confirmations as f', function ($j) {
                $j->on('f.fee_id', '=', 'ss.id')->on('f.administrator_id', '=', 'ss.administrator_id');
            })
            ->whereNull('f.id')
            ->when($ent, fn($q) => $q->where('ss.enterprise_id', $ent))
            ->select('ss.id', 'ss.enterprise_id', 'ss.service_id', 'ss.administrator_id', 'ss.due_term_id')
            ->get();

        $this->report('Subscriptions never billed (no fee raised at all)', $rows, function ($r) {
            return "subscription #{$r->id}  school {$r->enterprise_id}  service {$r->service_id}  student {$r->administrator_id}  term {$r->due_term_id}";
        });

        return $rows->count();
    }

    /**
     * Marker present but no charge on the ledger. The marker suppresses re-billing,
     * so these students are silently never charged.
     */
    private function markerWithoutCharge($ent)
    {
        $rows = DB::select("
            SELECT m.administrator_id, m.service_id, m.enterprise_id, m.markers,
                   IFNULL(c.charges,0) AS charges, m.sub_ids
            FROM (SELECT ss.enterprise_id, ss.administrator_id, ss.service_id, COUNT(*) markers,
                         GROUP_CONCAT(ss.id ORDER BY ss.id) sub_ids
                  FROM service_subscriptions ss
                  JOIN fee_deposit_confirmations f
                    ON f.fee_id = ss.id AND f.administrator_id = ss.administrator_id
                  " . ($ent ? "WHERE ss.enterprise_id = " . (int) $ent : "") . "
                  GROUP BY ss.enterprise_id, ss.administrator_id, ss.service_id) m
            LEFT JOIN (SELECT a.administrator_id, t.service_id, COUNT(*) charges
                       FROM transactions t JOIN accounts a ON a.id = t.account_id
                       WHERE t.service_id IS NOT NULL
                       GROUP BY a.administrator_id, t.service_id) c
              ON c.administrator_id = m.administrator_id AND c.service_id = m.service_id
            WHERE m.markers > IFNULL(c.charges,0)
        ");

        $this->report('Marked as billed but no charge on the ledger', collect($rows), function ($r) {
            $missing = $r->markers - $r->charges;
            return "student {$r->administrator_id}  service {$r->service_id}  subscriptions [{$r->sub_ids}]  {$missing} charge(s) missing";
        });

        return count($rows);
    }

    /**
     * A service charge naming a term the student never subscribed for. Caused by
     * billing against the enterprise's *active* term instead of the subscription's
     * own due term. The money is right; the term reports are not.
     *
     * Charges whose subscription was since deleted are excluded — those legitimately
     * have no subscription left to compare against.
     */
    private function misTaggedTerms($ent, $fix)
    {
        $rows = DB::select("
            SELECT t.id AS txn_id, t.term_id AS tagged_term, t.amount, t.service_id,
                   a.administrator_id, ss.enterprise_id,
                   (SELECT GROUP_CONCAT(DISTINCT s3.due_term_id ORDER BY s3.due_term_id)
                      FROM service_subscriptions s3
                     WHERE s3.administrator_id = a.administrator_id AND s3.service_id = t.service_id) AS subscribed_terms
            FROM transactions t
            JOIN accounts a ON a.id = t.account_id
            JOIN (SELECT DISTINCT administrator_id, service_id, enterprise_id FROM service_subscriptions) ss
              ON ss.administrator_id = a.administrator_id AND ss.service_id = t.service_id
            WHERE t.service_id IS NOT NULL
              " . ($ent ? "AND ss.enterprise_id = " . (int) $ent : "") . "
              AND NOT EXISTS (SELECT 1 FROM service_subscriptions s2
                              WHERE s2.administrator_id = a.administrator_id
                                AND s2.service_id = t.service_id
                                AND s2.due_term_id = t.term_id)
        ");

        $this->report('Charges tagged to a term the student never subscribed for', collect($rows), function ($r) {
            return "txn #{$r->txn_id}  student {$r->administrator_id}  service {$r->service_id}  tagged term {$r->tagged_term}  subscribed terms [{$r->subscribed_terms}]";
        });

        if ($fix && count($rows)) {
            $fixed = 0;
            foreach ($rows as $r) {
                // Retag only when the destination is forced: of the terms this
                // student subscribed to for this service, exactly one is still
                // carrying no charge. That term is the only one this orphaned
                // charge can belong to. Anything else is a judgement call and is
                // left for a human.
                $subscribed = array_values(array_filter(
                    array_map('intval', explode(',', (string) $r->subscribed_terms))
                ));

                $unpaid = [];
                foreach ($subscribed as $term) {
                    $has = DB::table('transactions as t')
                        ->join('accounts as a', 'a.id', '=', 't.account_id')
                        ->where('a.administrator_id', $r->administrator_id)
                        ->where('t.service_id', $r->service_id)
                        ->where('t.term_id', $term)
                        ->exists();
                    if (!$has) {
                        $unpaid[] = $term;
                    }
                }

                if (count($unpaid) !== 1) {
                    $this->line("    skipped txn #{$r->txn_id}: candidate terms ["
                        . implode(',', $unpaid) . "] — ambiguous, needs a human");
                    continue;
                }

                $target = $unpaid[0];
                $year   = DB::table('terms')->where('id', $target)->value('academic_year_id');
                DB::table('transactions')->where('id', $r->txn_id)
                    ->update(['term_id' => $target, 'academic_year_id' => $year]);
                $this->line("    txn #{$r->txn_id}: term {$r->tagged_term} -> {$target}");
                $fixed++;
            }
            $this->info("    re-tagged {$fixed} charge(s); amounts and balances unchanged");
        }

        return count($rows);
    }

    /** The unique index makes these impossible now; kept as a tripwire. */
    private function duplicateSubscriptions($ent)
    {
        $rows = DB::select("
            SELECT service_id, administrator_id, due_term_id, COUNT(*) c
            FROM service_subscriptions
            " . ($ent ? "WHERE enterprise_id = " . (int) $ent : "") . "
            GROUP BY service_id, administrator_id, due_term_id HAVING COUNT(*) > 1
        ");

        $this->report('Duplicate subscriptions (same service, student and term)', collect($rows), function ($r) {
            return "service {$r->service_id}  student {$r->administrator_id}  term {$r->due_term_id}  x{$r->c}";
        });

        return count($rows);
    }

    /**
     * Two fee definitions with the same name on one class and term. Each one
     * bills the whole class independently, so a duplicate charges every student
     * a second time. This is what hit 810 BFSS students in Term 3 2026.
     */
    private function duplicateClassFees($ent)
    {
        $rows = DB::select("
            SELECT MIN(acf.enterprise_id) AS enterprise_id,
                   acf.academic_class_id,
                   MIN(ac.name) AS class_name,
                   acf.due_term_id,
                   MIN(acf.name) AS name,
                   MIN(acf.amount) AS amount,
                   COUNT(*) c,
                   GROUP_CONCAT(acf.id ORDER BY acf.id) ids,
                   GROUP_CONCAT(DATE(acf.created_at) ORDER BY acf.id) created
            FROM academic_class_fees acf
            LEFT JOIN academic_classes ac ON ac.id = acf.academic_class_id
            " . ($ent ? "WHERE acf.enterprise_id = " . (int) $ent : "") . "
            GROUP BY acf.academic_class_id, acf.due_term_id, LOWER(TRIM(acf.name))
            HAVING COUNT(*) > 1
        ");

        $this->report('Duplicate class fee definitions (each one bills the class again)', collect($rows), function ($r) {
            return "school {$r->enterprise_id}  {$r->class_name}  term {$r->due_term_id}  \"{$r->name}\" "
                . "UGX " . number_format($r->amount) . "  x{$r->c}  fee ids [{$r->ids}] created {$r->created}";
        });

        return count($rows);
    }

    /**
     * The same bursary fund credited more than once to one account in the same
     * instant — the signature of a scheme paying its fund repeatedly into a single
     * term. A genuine repeat award lands in a different term on a different day.
     */
    private function duplicateBursaryCredits($ent)
    {
        $rows = DB::select("
            SELECT MIN(au.name) AS student, a.administrator_id, t.amount, t.created_at,
                   COUNT(*) c, GROUP_CONCAT(t.id ORDER BY t.id) ids
            FROM transactions t
            JOIN accounts a ON a.id = t.account_id
            JOIN admin_users au ON au.id = a.administrator_id
            WHERE t.description LIKE 'Bursary funds of%'
            " . ($ent ? "AND au.enterprise_id = " . (int) $ent : "") . "
            GROUP BY a.administrator_id, t.amount, t.created_at
            HAVING COUNT(*) > 1
        ");

        $this->report('Duplicate bursary credits (same fund paid more than once at once)', collect($rows), function ($r) {
            return "{$r->student} (#{$r->administrator_id})  UGX " . number_format($r->amount)
                . "  x{$r->c}  at {$r->created_at}  txns [{$r->ids}]";
        });

        return count($rows);
    }

    /**
     * A bursary award with no credit behind it: the student was promised the fund
     * but the account never received it.
     */
    private function unpaidBursaryAwards($ent)
    {
        $rows = DB::select("
            SELECT bb.id, bb.administrator_id, MIN(au.name) AS student, MIN(b.name) AS scheme,
                   MIN(b.fund) AS fund, bb.due_term_id
            FROM bursary_beneficiaries bb
            JOIN bursaries b ON b.id = bb.bursary_id
            JOIN admin_users au ON au.id = bb.administrator_id
            WHERE NOT EXISTS (SELECT 1 FROM transactions t
                              WHERE t.bursary_beneficiary_id = bb.id AND t.amount > 0)
            " . ($ent ? "AND bb.enterprise_id = " . (int) $ent : "") . "
            GROUP BY bb.id, bb.administrator_id, bb.due_term_id
        ");

        $this->report('Bursary awards with no credit posted', collect($rows), function ($r) {
            return "award #{$r->id}  {$r->student}  {$r->scheme}  UGX " . number_format($r->fund) . "  term {$r->due_term_id}";
        });

        return count($rows);
    }

    /** A batch whose run died mid-way and still holds its lock. */
    private function stuckBatchLocks($ent)
    {
        $cutoff = now()->subMinutes(\App\Models\BatchServiceSubscription::LOCK_TIMEOUT_MINUTES);
        $rows   = DB::table('batch_service_subscriptions')
            ->whereNotNull('locked_at')->where('locked_at', '<', $cutoff)
            ->when($ent, fn($q) => $q->where('enterprise_id', $ent))
            ->select('id', 'enterprise_id', 'locked_at')->get();

        $this->report('Batches holding an abandoned processing lock', $rows, function ($r) {
            return "batch #{$r->id}  school {$r->enterprise_id}  locked since {$r->locked_at}";
        });

        return $rows->count();
    }

    private function report($title, $rows, callable $line)
    {
        $count = $rows instanceof \Countable ? count($rows) : $rows->count();
        $this->line('');
        if ($count === 0) {
            $this->line("  <fg=green>OK</>    {$title}");
            return;
        }
        $this->line("  <fg=red>{$count}</>     {$title}");
        foreach ($rows as $i => $row) {
            if ($i >= 20) {
                $this->line('    ... and ' . ($count - 20) . ' more');
                break;
            }
            $this->line('    ' . $line($row));
        }
    }
}
