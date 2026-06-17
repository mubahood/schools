<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueSchoolPayIdIndexes extends Migration
{
    public function up()
    {
        // Remove any existing duplicate transactions before adding the unique index.
        // For each group of transactions sharing the same school_pay_transporter_id,
        // keep only the one with the lowest id and delete the rest.
        DB::statement("
            DELETE t1 FROM transactions t1
            INNER JOIN transactions t2
              ON  t1.school_pay_transporter_id = t2.school_pay_transporter_id
              AND t1.id > t2.id
            WHERE t1.school_pay_transporter_id IS NOT NULL
              AND t1.school_pay_transporter_id != ''
              AND t1.school_pay_transporter_id != '-'
              AND LENGTH(t1.school_pay_transporter_id) >= 3
        ");

        // Remove duplicate school_pay_transactions records the same way.
        DB::statement("
            DELETE s1 FROM school_pay_transactions s1
            INNER JOIN school_pay_transactions s2
              ON  s1.school_pay_transporter_id = s2.school_pay_transporter_id
              AND s1.id > s2.id
            WHERE s1.school_pay_transporter_id IS NOT NULL
              AND s1.school_pay_transporter_id != ''
              AND LENGTH(s1.school_pay_transporter_id) >= 3
        ");

        // Nullify empty/invalid values — MySQL UNIQUE allows multiple NULLs,
        // so only real receipt numbers (≥ 3 chars) will be enforced as unique.
        DB::statement("UPDATE transactions SET school_pay_transporter_id = NULL
            WHERE school_pay_transporter_id IS NULL
               OR school_pay_transporter_id = ''
               OR school_pay_transporter_id = '-'
               OR LENGTH(school_pay_transporter_id) < 3");

        DB::statement("UPDATE school_pay_transactions SET school_pay_transporter_id = NULL
            WHERE school_pay_transporter_id IS NULL
               OR school_pay_transporter_id = ''
               OR school_pay_transporter_id = '-'
               OR LENGTH(school_pay_transporter_id) < 3");

        // transactions.school_pay_transporter_id is TEXT — must convert to VARCHAR
        // before MySQL will accept a unique index on it.
        DB::statement("ALTER TABLE transactions MODIFY school_pay_transporter_id VARCHAR(50) NULL DEFAULT NULL");

        // Now add a unique index (MySQL excludes NULL from UNIQUE enforcement,
        // so multiple NULL rows are allowed — only non-NULL values must be unique).
        DB::statement("CREATE UNIQUE INDEX uq_txn_school_pay_id ON transactions (school_pay_transporter_id)");

        // school_pay_transactions is already VARCHAR(255) — just add the index.
        DB::statement("CREATE UNIQUE INDEX uq_spt_school_pay_id ON school_pay_transactions (school_pay_transporter_id)");
    }

    public function down()
    {
        DB::statement("DROP INDEX uq_txn_school_pay_id ON transactions");
        DB::statement("ALTER TABLE transactions MODIFY school_pay_transporter_id TEXT NULL DEFAULT NULL");
        DB::statement("DROP INDEX uq_spt_school_pay_id ON school_pay_transactions");
    }
}
