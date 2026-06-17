<?php
/**
 * Minimal raw-PDO cleanup — no Laravel bootstrap needed.
 * Deletes duplicate school_pay transactions, adds unique indexes,
 * and recomputes all account balances.
 */

// ── Connect ──────────────────────────────────────────────────────────────────
$pdo = new PDO(
    'mysql:host=localhost;dbname=schooics_main;charset=utf8mb4',
    'schooics_main',
    'schooics_main',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 120]
);
$pdo->exec("SET wait_timeout=3600");
$pdo->exec("SET interactive_timeout=3600");
$pdo->exec("SET net_read_timeout=600");
$pdo->exec("SET net_write_timeout=600");

$t0 = microtime(true);
$log = [];

function line(string $msg) use (&$log) {
    $log[] = $msg;
    file_put_contents('/home4/schooics/public_html/fix_db_progress.log', $msg . "\n", FILE_APPEND);
}

line("=== PRODUCTION CLEANUP — " . date('Y-m-d H:i:s') . " ===");

// ── STEP 1: Delete duplicate transactions (keep lowest id per receipt) ────────
line("STEP 1: Deleting duplicate transactions in batches...");
$deleted1 = 0;
do {
    // Fetch IDs of ghost rows (higher id, same receipt as a lower id row)
    $stmt = $pdo->query("
        SELECT t1.id FROM transactions t1
        INNER JOIN transactions t2
          ON t1.school_pay_transporter_id = t2.school_pay_transporter_id
          AND t1.id > t2.id
        WHERE t1.school_pay_transporter_id IS NOT NULL
          AND t1.school_pay_transporter_id NOT IN ('','-')
          AND LENGTH(t1.school_pay_transporter_id) >= 3
        LIMIT 200
    ");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($ids)) break;
    $in = implode(',', array_map('intval', $ids));
    $pdo->exec("DELETE FROM transactions WHERE id IN ($in)");
    $deleted1 += count($ids);
    line("  ...deleted batch, running total: $deleted1");
} while (count($ids) === 200);
line("STEP 1 DONE: $deleted1 ghost transactions removed.");

// ── STEP 2: Delete duplicate school_pay_transactions ─────────────────────────
line("STEP 2: Deleting duplicate school_pay_transactions in batches...");
$deleted2 = 0;
do {
    $stmt = $pdo->query("
        SELECT s1.id FROM school_pay_transactions s1
        INNER JOIN school_pay_transactions s2
          ON s1.school_pay_transporter_id = s2.school_pay_transporter_id
          AND s1.id > s2.id
        WHERE s1.school_pay_transporter_id IS NOT NULL
          AND s1.school_pay_transporter_id NOT IN ('','-')
          AND LENGTH(s1.school_pay_transporter_id) >= 3
        LIMIT 200
    ");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($ids)) break;
    $in = implode(',', array_map('intval', $ids));
    $pdo->exec("DELETE FROM school_pay_transactions WHERE id IN ($in)");
    $deleted2 += count($ids);
    line("  ...deleted batch, running total: $deleted2");
} while (count($ids) === 200);
line("STEP 2 DONE: $deleted2 ghost school_pay_transactions removed.");

// ── STEP 3: Nullify invalid sp_ids ────────────────────────────────────────────
line("STEP 3: Nullifying invalid school_pay_transporter_id values...");
$r1 = $pdo->exec("UPDATE transactions SET school_pay_transporter_id = NULL
    WHERE school_pay_transporter_id IS NULL
       OR school_pay_transporter_id IN ('','-')
       OR LENGTH(school_pay_transporter_id) < 3");
$r2 = $pdo->exec("UPDATE school_pay_transactions SET school_pay_transporter_id = NULL
    WHERE school_pay_transporter_id IS NULL
       OR school_pay_transporter_id IN ('','-')
       OR LENGTH(school_pay_transporter_id) < 3");
line("STEP 3 DONE: nullified $r1 rows in transactions, $r2 in school_pay_transactions.");

// ── STEP 4: Change column type TEXT → VARCHAR(50) ─────────────────────────────
line("STEP 4: Changing transactions.school_pay_transporter_id to VARCHAR(50)...");
$colType = $pdo->query("SHOW COLUMNS FROM transactions WHERE Field='school_pay_transporter_id'")->fetchObject()->Type;
if (stripos($colType, 'text') !== false) {
    $pdo->exec("ALTER TABLE transactions MODIFY school_pay_transporter_id VARCHAR(50) NULL DEFAULT NULL");
    line("STEP 4 DONE: column changed to VARCHAR(50).");
} else {
    line("STEP 4 SKIPPED: already $colType.");
}

// ── STEP 5: Add unique indexes ────────────────────────────────────────────────
line("STEP 5: Adding unique indexes...");
$existing1 = $pdo->query("SHOW INDEX FROM transactions WHERE Key_name='uq_txn_school_pay_id'")->fetchAll();
if (empty($existing1)) {
    $pdo->exec("CREATE UNIQUE INDEX uq_txn_school_pay_id ON transactions (school_pay_transporter_id)");
    line("  UNIQUE index on transactions: CREATED.");
} else {
    line("  UNIQUE index on transactions: already exists.");
}
$existing2 = $pdo->query("SHOW INDEX FROM school_pay_transactions WHERE Key_name='uq_spt_school_pay_id'")->fetchAll();
if (empty($existing2)) {
    $pdo->exec("CREATE UNIQUE INDEX uq_spt_school_pay_id ON school_pay_transactions (school_pay_transporter_id)");
    line("  UNIQUE index on school_pay_transactions: CREATED.");
} else {
    line("  UNIQUE index on school_pay_transactions: already exists.");
}
line("STEP 5 DONE.");

// ── STEP 6: Mark migration as run ─────────────────────────────────────────────
line("STEP 6: Recording migration...");
$migName = '2026_06_18_000001_add_unique_school_pay_id_indexes';
$exists  = $pdo->query("SELECT id FROM migrations WHERE migration='$migName'")->fetch();
if (!$exists) {
    $maxBatch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn() ?: 0;
    $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('$migName', " . ((int)$maxBatch + 1) . ")");
    line("  Migration recorded (batch " . ((int)$maxBatch + 1) . ").");
} else {
    line("  Already recorded.");
}
line("STEP 6 DONE.");

// ── STEP 7: Recompute ALL account balances ─────────────────────────────────────
line("STEP 7: Recomputing all account balances...");
$accounts = $pdo->query("SELECT id FROM accounts ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
$total = count($accounts);
$done  = 0;
$upd   = $pdo->prepare("UPDATE accounts SET balance = (SELECT COALESCE(SUM(amount),0) FROM transactions WHERE account_id = ?) WHERE id = ?");
foreach ($accounts as $accId) {
    $upd->execute([(int)$accId, (int)$accId]);
    $done++;
    if ($done % 500 === 0) {
        line("  ...{$done}/{$total} accounts processed.");
    }
}
line("STEP 7 DONE: $total account balances recomputed.");

// ── STEP 8: Final verification ─────────────────────────────────────────────────
line("STEP 8: Final verification...");
$remaining = $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT school_pay_transporter_id FROM transactions
        WHERE school_pay_transporter_id IS NOT NULL
        GROUP BY school_pay_transporter_id HAVING COUNT(*) > 1
    ) t")->fetchColumn();
$colFinal = $pdo->query("SHOW COLUMNS FROM transactions WHERE Field='school_pay_transporter_id'")->fetchObject()->Type;
$idx1ok   = count($pdo->query("SHOW INDEX FROM transactions WHERE Key_name='uq_txn_school_pay_id'")->fetchAll()) > 0;
$idx2ok   = count($pdo->query("SHOW INDEX FROM school_pay_transactions WHERE Key_name='uq_spt_school_pay_id'")->fetchAll()) > 0;

line("  Remaining duplicates: $remaining");
line("  Column type: $colFinal");
line("  txn unique index: " . ($idx1ok ? 'EXISTS' : 'MISSING'));
line("  spt unique index: " . ($idx2ok ? 'EXISTS' : 'MISSING'));

$elapsed = round(microtime(true) - $t0, 1);
line("\n=== COMPLETE in {$elapsed}s at " . date('Y-m-d H:i:s') . " ===");

// Print all output
echo implode("\n", $log) . "\n";
