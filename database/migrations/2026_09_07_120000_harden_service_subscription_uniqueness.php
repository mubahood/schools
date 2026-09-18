<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hardens batch service subscription processing.
 *
 * 1. Adds a processing lock to batch_service_subscriptions so the same batch can
 *    never be processed by two concurrent requests. Before this, `is_processed`
 *    was only flipped to 'Yes' AFTER the loop finished, so a second click on the
 *    "Process" button started a parallel run over the same students. One run
 *    created the subscription, the other reported it as "already subscribed".
 *
 * 2. Indexes fee_deposit_confirmations(fee_id, administrator_id) — the billing
 *    idempotency lookup ran as a full table scan on every subscription created.
 *
 * 3. De-duplicates and then uniquely indexes
 *    service_subscriptions(service_id, administrator_id, due_term_id) so a race
 *    can never write the same subscription twice, whatever the calling path.
 */
class HardenServiceSubscriptionUniqueness extends Migration
{
    const UNIQUE_INDEX = 'uniq_ss_service_admin_term';
    const BACKUP_TABLE = 'service_subscriptions_duplicates_backup';

    public function up()
    {
        // ── 1. Processing lock on the batch table ────────────────────────────
        Schema::table('batch_service_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('batch_service_subscriptions', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('is_processed');
            }
            if (!Schema::hasColumn('batch_service_subscriptions', 'locked_by_id')) {
                $table->unsignedBigInteger('locked_by_id')->nullable()->after('locked_at');
            }
            // Students who already held the service are a no-op, not a failure.
            // They get their own counter so fail_count means "needs attention".
            if (!Schema::hasColumn('batch_service_subscriptions', 'skipped_count')) {
                $table->integer('skipped_count')->nullable()->after('fail_count');
            }
        });

        // ── 2. Billing idempotency lookup index ──────────────────────────────
        if (Schema::hasTable('fee_deposit_confirmations') && !$this->hasIndex('fee_deposit_confirmations', 'idx_fdc_fee_admin')) {
            DB::statement('ALTER TABLE fee_deposit_confirmations ADD INDEX idx_fdc_fee_admin (fee_id, administrator_id)');
        }

        // ── 3. De-duplicate, then enforce uniqueness ─────────────────────────
        if ($this->hasIndex('service_subscriptions', self::UNIQUE_INDEX)) {
            return; // Already hardened.
        }

        $this->backupAndRemoveDuplicates();

        DB::statement(
            'ALTER TABLE service_subscriptions ADD UNIQUE INDEX ' . self::UNIQUE_INDEX
            . ' (service_id, administrator_id, due_term_id)'
        );
    }

    public function down()
    {
        if ($this->hasIndex('service_subscriptions', self::UNIQUE_INDEX)) {
            DB::statement('ALTER TABLE service_subscriptions DROP INDEX ' . self::UNIQUE_INDEX);
        }

        if ($this->hasIndex('fee_deposit_confirmations', 'idx_fdc_fee_admin')) {
            DB::statement('ALTER TABLE fee_deposit_confirmations DROP INDEX idx_fdc_fee_admin');
        }

        Schema::table('batch_service_subscriptions', function (Blueprint $table) {
            foreach (['locked_at', 'locked_by_id', 'skipped_count'] as $column) {
                if (Schema::hasColumn('batch_service_subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Copies every redundant subscription row into a backup table, then removes
     * it with a raw delete. A raw delete is deliberate: the Eloquent `deleting`
     * hook posts a reversing credit transaction, and these duplicates are years
     * old — reversing them now would silently move real account balances. The
     * financial history is left exactly as it stands; only the redundant
     * subscription row is withdrawn so the unique index can be created.
     */
    private function backupAndRemoveDuplicates()
    {
        $duplicates = DB::table('service_subscriptions')
            ->select('service_id', 'administrator_id', 'due_term_id', DB::raw('COUNT(*) AS occurrences'))
            ->groupBy('service_id', 'administrator_id', 'due_term_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            return;
        }

        if (!Schema::hasTable(self::BACKUP_TABLE)) {
            DB::statement('CREATE TABLE ' . self::BACKUP_TABLE . ' LIKE service_subscriptions');
        }

        foreach ($duplicates as $duplicate) {
            // Keep the earliest row — it owns the transaction that was actually posted.
            $redundantIds = DB::table('service_subscriptions')
                ->where('service_id', $duplicate->service_id)
                ->where('administrator_id', $duplicate->administrator_id)
                ->where('due_term_id', $duplicate->due_term_id)
                ->orderBy('id')
                ->pluck('id')
                ->slice(1)
                ->values()
                ->all();

            if (empty($redundantIds)) {
                continue;
            }

            DB::statement(
                'INSERT IGNORE INTO ' . self::BACKUP_TABLE
                . ' SELECT * FROM service_subscriptions WHERE id IN (' . implode(',', $redundantIds) . ')'
            );

            DB::table('service_subscriptions')->whereIn('id', $redundantIds)->delete();
        }
    }

    private function hasIndex($table, $index)
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])) > 0;
    }
}
