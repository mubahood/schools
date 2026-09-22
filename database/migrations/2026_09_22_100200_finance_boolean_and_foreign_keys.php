<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two things the ledger was missing once its data was clean enough to take them.
 *
 * 1. is_credit was a varchar holding 'Yes' / 'No'. Any typo or casing slip read
 *    as "not on credit", which silently skipped raising a creditor.
 * 2. No foreign keys at all. The application now rehomes dependents before a
 *    category is deleted; these constraints are the backstop for anything that
 *    reaches the database another way.
 */
return new class extends Migration
{
    public function up()
    {
        // ── 1. is_credit becomes a real boolean ─────────────────────────
        $col = collect(DB::select('DESCRIBE financial_records'))->firstWhere('Field', 'is_credit');
        if ($col && stripos($col->Type, 'varchar') !== false) {
            DB::statement("ALTER TABLE financial_records ADD COLUMN is_credit_bool TINYINT(1) NOT NULL DEFAULT 0");
            DB::statement("UPDATE financial_records SET is_credit_bool = CASE
                WHEN LOWER(TRIM(COALESCE(is_credit,''))) IN ('yes','1','true','y') THEN 1 ELSE 0 END");
            DB::statement('ALTER TABLE financial_records DROP COLUMN is_credit');
            DB::statement('ALTER TABLE financial_records CHANGE is_credit_bool is_credit TINYINT(1) NOT NULL DEFAULT 0');
        }

        // ── 2. align types so foreign keys can be created ───────────────
        // A foreign key needs both sides to be the same type, and these drifted.
        DB::statement('ALTER TABLE financial_records MODIFY parent_account_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE financial_records MODIFY created_by_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE financial_records MODIFY supplier_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE creditor_records MODIFY created_by_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE creditor_records MODIFY supplier_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE creditor_payments MODIFY created_by_id INT UNSIGNED NULL');
        // accounts.account_parent_id was signed while account_parents.id is not;
        // MySQL refuses a foreign key across that difference.
        DB::statement('ALTER TABLE accounts MODIFY account_parent_id BIGINT UNSIGNED NULL');

        // A zero is not a reference; it is a missing one.
        DB::statement('UPDATE financial_records SET supplier_id = NULL WHERE supplier_id = 0');
        DB::statement('UPDATE financial_records SET created_by_id = NULL WHERE created_by_id = 0');
        DB::statement('UPDATE creditor_records SET supplier_id = NULL WHERE supplier_id = 0');
        DB::statement('UPDATE creditor_records SET created_by_id = NULL WHERE created_by_id = 0');
        DB::statement('UPDATE creditor_payments SET created_by_id = NULL WHERE created_by_id = 0');
        DB::statement('UPDATE accounts SET account_parent_id = NULL WHERE account_parent_id = 0');

        // ── 3. the constraints ──────────────────────────────────────────
        // RESTRICT on the categories: the app rehomes dependents first, so a
        // legitimate delete still succeeds and a careless one is refused.
        $fks = [
            ['financial_records', 'fk_fr_enterprise', 'enterprise_id', 'enterprises', 'RESTRICT'],
            ['financial_records', 'fk_fr_account', 'account_id', 'accounts', 'RESTRICT'],
            ['financial_records', 'fk_fr_term', 'term_id', 'terms', 'RESTRICT'],
            ['financial_records', 'fk_fr_vote', 'parent_account_id', 'account_parents', 'SET NULL'],
            ['financial_records', 'fk_fr_supplier', 'supplier_id', 'admin_users', 'SET NULL'],
            ['financial_records', 'fk_fr_creator', 'created_by_id', 'admin_users', 'SET NULL'],
            ['creditor_records', 'fk_cr_record', 'financial_record_id', 'financial_records', 'CASCADE'],
            ['creditor_records', 'fk_cr_enterprise', 'enterprise_id', 'enterprises', 'RESTRICT'],
            ['creditor_records', 'fk_cr_supplier', 'supplier_id', 'admin_users', 'SET NULL'],
            ['creditor_payments', 'fk_cp_creditor', 'creditor_record_id', 'creditor_records', 'CASCADE'],
            ['creditor_payments', 'fk_cp_enterprise', 'enterprise_id', 'enterprises', 'RESTRICT'],
            ['accounts', 'fk_acc_vote', 'account_parent_id', 'account_parents', 'SET NULL'],
        ];
        foreach ($fks as [$table, $name, $column, $refTable, $onDelete]) {
            if ($this->hasForeignKey($table, $name)) {
                continue;
            }
            try {
                DB::statement("ALTER TABLE `$table` ADD CONSTRAINT `$name` FOREIGN KEY (`$column`)
                    REFERENCES `$refTable` (`id`) ON DELETE $onDelete ON UPDATE CASCADE");
            } catch (\Throwable $e) {
                // Never let a constraint block the deploy; report and move on.
                \Illuminate\Support\Facades\Log::warning("Finance FK $name not created", ['error' => $e->getMessage()]);
            }
        }
    }

    public function down()
    {
        foreach ([['financial_records', ['fk_fr_enterprise', 'fk_fr_account', 'fk_fr_term', 'fk_fr_vote', 'fk_fr_supplier', 'fk_fr_creator']],
                  ['creditor_records', ['fk_cr_record', 'fk_cr_enterprise', 'fk_cr_supplier']],
                  ['creditor_payments', ['fk_cp_creditor', 'fk_cp_enterprise']],
                  ['accounts', ['fk_acc_vote']]] as [$table, $names]) {
            foreach ($names as $n) {
                if ($this->hasForeignKey($table, $n)) {
                    DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `$n`");
                }
            }
        }
        DB::statement("ALTER TABLE financial_records MODIFY is_credit VARCHAR(3) NOT NULL DEFAULT 'No'");
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $name)
            ->exists();
    }
};
