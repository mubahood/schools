<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hardens bursary awards.
 *
 * 1. Links a bursary transaction back to the award that raised it. Without this
 *    there was no way to tell a duplicate credit from a legitimate second award
 *    in a later term — the description names the scheme but not the term, and it
 *    goes stale the moment a scheme is renamed.
 *
 * 2. Enforces "a student cannot benefit from the same scheme twice in one term"
 *    in the database. It was only ever a PHP check, and that check called die().
 */
class HardenBursaryBeneficiaries extends Migration
{
    const UNIQUE_INDEX = 'uniq_bb_student_bursary_term';

    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'bursary_beneficiary_id')) {
                $table->unsignedBigInteger('bursary_beneficiary_id')->nullable()->after('service_id');
                $table->index('bursary_beneficiary_id', 'idx_txn_bursary_beneficiary');
            }
        });

        if (Schema::hasTable('deleted_transactions') && !Schema::hasColumn('deleted_transactions', 'bursary_beneficiary_id')) {
            Schema::table('deleted_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('bursary_beneficiary_id')->nullable()->after('service_id');
            });
        }

        if (!$this->hasIndex('bursary_beneficiaries', self::UNIQUE_INDEX) && $this->noDuplicateAwards()) {
            DB::statement(
                'ALTER TABLE bursary_beneficiaries ADD UNIQUE INDEX ' . self::UNIQUE_INDEX
                . ' (administrator_id, bursary_id, due_term_id)'
            );
        }
    }

    public function down()
    {
        if ($this->hasIndex('bursary_beneficiaries', self::UNIQUE_INDEX)) {
            DB::statement('ALTER TABLE bursary_beneficiaries DROP INDEX ' . self::UNIQUE_INDEX);
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'bursary_beneficiary_id')) {
                $table->dropIndex('idx_txn_bursary_beneficiary');
                $table->dropColumn('bursary_beneficiary_id');
            }
        });

        if (Schema::hasTable('deleted_transactions') && Schema::hasColumn('deleted_transactions', 'bursary_beneficiary_id')) {
            Schema::table('deleted_transactions', function (Blueprint $table) {
                $table->dropColumn('bursary_beneficiary_id');
            });
        }
    }

    /** The index is skipped rather than forced if the data would not accept it. */
    private function noDuplicateAwards()
    {
        $dups = DB::select("SELECT COUNT(*) AS c FROM (
            SELECT 1 FROM bursary_beneficiaries
            GROUP BY administrator_id, bursary_id, due_term_id HAVING COUNT(*) > 1) x");

        return (int) $dups[0]->c === 0;
    }

    private function hasIndex($table, $index)
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])) > 0;
    }
}
