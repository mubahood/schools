<?php
/**
 * Production cleanup: remove duplicate school_pay transactions and
 * recalculate all account balances.
 *
 * Run via: php fix_production.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

set_time_limit(3600);
ini_set('memory_limit', '512M');

$start = microtime(true);

echo "=== PRODUCTION CLEANUP SCRIPT ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

// ─── STEP 1: Delete duplicate transactions (keep lowest id per receipt number) ───
echo "STEP 1: Deleting duplicate transactions...\n";
$totalDeleted = 0;
$batch = 0;
do {
    $rows = DB::select("
        SELECT t1.id, t1.school_pay_transporter_id, t1.account_id
        FROM transactions t1
        INNER JOIN transactions t2
          ON t1.school_pay_transporter_id = t2.school_pay_transporter_id
          AND t1.id > t2.id
        WHERE t1.school_pay_transporter_id IS NOT NULL
          AND LENGTH(t1.school_pay_transporter_id) >= 3
          AND t1.school_pay_transporter_id NOT IN ('', '-')
        LIMIT 100
    ");
    if (empty($rows)) break;
    $ids = array_column($rows, 'id');
    DB::table('transactions')->whereIn('id', $ids)->delete();
    $totalDeleted += count($ids);
    $batch++;
    echo "  Batch {$batch}: deleted " . count($ids) . " rows (total: {$totalDeleted})\n";
    flush();
} while (count($rows) === 100);
echo "STEP 1 DONE: {$totalDeleted} ghost transactions deleted.\n\n";

// ─── STEP 2: Delete duplicate school_pay_transactions ────────────────────────
echo "STEP 2: Deleting duplicate school_pay_transactions...\n";
$totalDeleted2 = 0;
$batch2 = 0;
do {
    $rows2 = DB::select("
        SELECT s1.id
        FROM school_pay_transactions s1
        INNER JOIN school_pay_transactions s2
          ON s1.school_pay_transporter_id = s2.school_pay_transporter_id
          AND s1.id > s2.id
        WHERE s1.school_pay_transporter_id IS NOT NULL
          AND LENGTH(s1.school_pay_transporter_id) >= 3
          AND s1.school_pay_transporter_id NOT IN ('', '-')
        LIMIT 100
    ");
    if (empty($rows2)) break;
    $ids2 = array_column($rows2, 'id');
    DB::table('school_pay_transactions')->whereIn('id', $ids2)->delete();
    $totalDeleted2 += count($ids2);
    $batch2++;
    echo "  Batch {$batch2}: deleted " . count($ids2) . " rows (total: {$totalDeleted2})\n";
    flush();
} while (count($rows2) === 100);
echo "STEP 2 DONE: {$totalDeleted2} ghost school_pay_transactions deleted.\n\n";

// ─── STEP 3: Nullify invalid school_pay_transporter_id values ────────────────
echo "STEP 3: Nullifying invalid school_pay_transporter_id values...\n";
$nullified = DB::statement("
    UPDATE transactions
    SET school_pay_transporter_id = NULL
    WHERE school_pay_transporter_id IS NULL
       OR school_pay_transporter_id = ''
       OR school_pay_transporter_id = '-'
       OR LENGTH(school_pay_transporter_id) < 3
");
$n1 = DB::select("SELECT ROW_COUNT() as cnt")[0]->cnt ?? 0;
$n2 = DB::statement("
    UPDATE school_pay_transactions
    SET school_pay_transporter_id = NULL
    WHERE school_pay_transporter_id IS NULL
       OR school_pay_transporter_id = ''
       OR school_pay_transporter_id = '-'
       OR LENGTH(school_pay_transporter_id) < 3
");
echo "STEP 3 DONE: rows nullified in transactions and school_pay_transactions.\n\n";

// ─── STEP 4: Change column type if still TEXT ────────────────────────────────
echo "STEP 4: Changing column type to VARCHAR(50) if needed...\n";
$colType = DB::select("SHOW COLUMNS FROM transactions WHERE Field = 'school_pay_transporter_id'")[0]->Type;
echo "  Current type: {$colType}\n";
if (stripos($colType, 'text') !== false) {
    DB::statement("ALTER TABLE transactions MODIFY school_pay_transporter_id VARCHAR(50) NULL DEFAULT NULL");
    echo "  Changed to VARCHAR(50).\n";
} else {
    echo "  Already VARCHAR — skipping.\n";
}
echo "STEP 4 DONE.\n\n";

// ─── STEP 5: Add unique indexes ───────────────────────────────────────────────
echo "STEP 5: Adding unique indexes...\n";

$idx1 = DB::select("SHOW INDEX FROM transactions WHERE Key_name = 'uq_txn_school_pay_id'");
if (empty($idx1)) {
    DB::statement("CREATE UNIQUE INDEX uq_txn_school_pay_id ON transactions (school_pay_transporter_id)");
    echo "  UNIQUE index on transactions: ADDED.\n";
} else {
    echo "  UNIQUE index on transactions: already exists.\n";
}

$idx2 = DB::select("SHOW INDEX FROM school_pay_transactions WHERE Key_name = 'uq_spt_school_pay_id'");
if (empty($idx2)) {
    DB::statement("CREATE UNIQUE INDEX uq_spt_school_pay_id ON school_pay_transactions (school_pay_transporter_id)");
    echo "  UNIQUE index on school_pay_transactions: ADDED.\n";
} else {
    echo "  UNIQUE index on school_pay_transactions: already exists.\n";
}
echo "STEP 5 DONE.\n\n";

// ─── STEP 6: Mark migration as run ───────────────────────────────────────────
echo "STEP 6: Marking migration as completed...\n";
$migName = '2026_06_18_000001_add_unique_school_pay_id_indexes';
$already = DB::table('migrations')->where('migration', $migName)->first();
if (!$already) {
    $maxBatch = DB::table('migrations')->max('batch') ?? 0;
    DB::table('migrations')->insert([
        'migration' => $migName,
        'batch'     => $maxBatch + 1,
    ]);
    echo "  Migration recorded in migrations table.\n";
} else {
    echo "  Migration already recorded.\n";
}
echo "STEP 6 DONE.\n\n";

// ─── STEP 7: Recompute ALL account balances ────────────────────────────────────
echo "STEP 7: Recomputing all account balances...\n";
$accounts = DB::table('accounts')->pluck('id');
$totalAccs = count($accounts);
$done = 0;
foreach ($accounts as $accId) {
    $bal = DB::table('transactions')->where('account_id', $accId)->sum('amount');
    DB::table('accounts')->where('id', $accId)->update(['balance' => $bal]);
    $done++;
    if ($done % 200 === 0) {
        echo "  Processed {$done}/{$totalAccs} accounts...\n";
        flush();
    }
}
echo "STEP 7 DONE: {$totalAccs} account balances recomputed.\n\n";

// ─── STEP 8: Verify final state ───────────────────────────────────────────────
echo "STEP 8: Final verification...\n";
$remainingDups = DB::select("
    SELECT COUNT(*) as cnt FROM (
        SELECT school_pay_transporter_id FROM transactions
        WHERE school_pay_transporter_id IS NOT NULL
        GROUP BY school_pay_transporter_id HAVING COUNT(*) > 1
    ) t
")[0]->cnt;
echo "  Remaining duplicate sp_id groups: {$remainingDups}\n";

$idx1 = DB::select("SHOW INDEX FROM transactions WHERE Key_name = 'uq_txn_school_pay_id'");
$idx2 = DB::select("SHOW INDEX FROM school_pay_transactions WHERE Key_name = 'uq_spt_school_pay_id'");
echo "  UNIQUE index on transactions: " . (count($idx1) > 0 ? 'EXISTS' : 'MISSING') . "\n";
echo "  UNIQUE index on school_pay_transactions: " . (count($idx2) > 0 ? 'EXISTS' : 'MISSING') . "\n";

$colType2 = DB::select("SHOW COLUMNS FROM transactions WHERE Field = 'school_pay_transporter_id'")[0]->Type;
echo "  Column type: {$colType2}\n";

echo "\n=== ALL DONE ===\n";
$elapsed = round(microtime(true) - $start, 1);
echo "Completed at: " . date('Y-m-d H:i:s') . " (took {$elapsed}s)\n";
