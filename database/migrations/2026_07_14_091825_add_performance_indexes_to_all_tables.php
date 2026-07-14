<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesToAllTables extends Migration
{
    public function up()
    {
        // mark_records — most queried table (80K rows, was PRIMARY only)
        $this->addIndexSafe('mark_records', 'idx_mr_term_class', ['term_id', 'academic_class_id']);
        $this->addIndexSafe('mark_records', 'idx_mr_trc',        ['termly_report_card_id']);
        $this->addIndexSafe('mark_records', 'idx_mr_student',    ['administrator_id']);
        $this->addIndexSafe('mark_records', 'idx_mr_enterprise', ['enterprise_id']);
        $this->addIndexSafe('mark_records', 'idx_mr_subject',    ['subject_id']);

        // transactions — 54K rows
        $this->addIndexSafe('transactions', 'idx_txn_account',         ['account_id']);
        $this->addIndexSafe('transactions', 'idx_txn_enterprise_date', ['enterprise_id', 'created_at']);

        // accounts — 16K rows
        $this->addIndexSafe('accounts', 'idx_acc_admin',      ['administrator_id']);
        $this->addIndexSafe('accounts', 'idx_acc_enterprise', ['enterprise_id']);

        // student_has_classes — 16K rows
        $this->addIndexSafe('student_has_classes', 'idx_shc_class',      ['academic_class_id']);
        $this->addIndexSafe('student_has_classes', 'idx_shc_admin',      ['administrator_id']);
        $this->addIndexSafe('student_has_classes', 'idx_shc_enterprise', ['enterprise_id']);

        // financial_records — 32K rows
        $this->addIndexSafe('financial_records', 'idx_fr_enterprise', ['enterprise_id']);
        $this->addIndexSafe('financial_records', 'idx_fr_account',    ['account_id']);
        $this->addIndexSafe('financial_records', 'idx_fr_term',       ['term_id']);

        // student_report_cards — 12K rows
        $this->addIndexSafe('student_report_cards', 'idx_src_trc',     ['termly_report_card_id']);
        $this->addIndexSafe('student_report_cards', 'idx_src_student', ['student_id']);
        $this->addIndexSafe('student_report_cards', 'idx_src_term',    ['term_id']);

        // student_report_card_items — 24K rows
        $this->addIndexSafe('student_report_card_items', 'idx_srci_src', ['student_report_card_id']);

        // service_subscriptions — 9K rows
        $this->addIndexSafe('service_subscriptions', 'idx_ss_admin',      ['administrator_id']);
        $this->addIndexSafe('service_subscriptions', 'idx_ss_enterprise', ['enterprise_id']);

        // wallet_records — 14K rows
        $this->addIndexSafe('wallet_records', 'idx_wr_enterprise', ['enterprise_id']);

        // fee_deposit_confirmations — 17K rows
        $this->addIndexSafe('fee_deposit_confirmations', 'idx_fdc_enterprise', ['enterprise_id']);

        // reconcilers — 227K rows, was PRIMARY only
        $this->addIndexSafe('reconcilers', 'idx_rec_enterprise', ['enterprise_id']);

        // theology_mark_records — 55K rows
        $this->addIndexSafe('theology_mark_records', 'idx_tmr_term_class', ['term_id', 'theology_class_id']);
        $this->addIndexSafe('theology_mark_records', 'idx_tmr_student',    ['administrator_id']);
        $this->addIndexSafe('theology_mark_records', 'idx_tmr_enterprise', ['enterprise_id']);
    }

    public function down()
    {
        $indexes = [
            'mark_records'              => ['idx_mr_term_class', 'idx_mr_trc', 'idx_mr_student', 'idx_mr_enterprise', 'idx_mr_subject'],
            'transactions'              => ['idx_txn_account', 'idx_txn_enterprise_date'],
            'accounts'                  => ['idx_acc_admin', 'idx_acc_enterprise'],
            'student_has_classes'       => ['idx_shc_class', 'idx_shc_admin', 'idx_shc_enterprise'],
            'financial_records'         => ['idx_fr_enterprise', 'idx_fr_account', 'idx_fr_term'],
            'student_report_cards'      => ['idx_src_trc', 'idx_src_student', 'idx_src_term'],
            'student_report_card_items' => ['idx_srci_src'],
            'service_subscriptions'     => ['idx_ss_admin', 'idx_ss_enterprise'],
            'wallet_records'            => ['idx_wr_enterprise'],
            'fee_deposit_confirmations' => ['idx_fdc_enterprise'],
            'reconcilers'               => ['idx_rec_enterprise'],
            'theology_mark_records'     => ['idx_tmr_term_class', 'idx_tmr_student', 'idx_tmr_enterprise'],
        ];
        foreach ($indexes as $table => $idxList) {
            foreach ($idxList as $idx) {
                $this->dropIndexSafe($table, $idx);
            }
        }
    }

    private function addIndexSafe(string $table, string $name, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        $existing = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->unique();
        if ($existing->contains($name)) {
            return;
        }
        Schema::table($table, function (Blueprint $t) use ($name, $columns) {
            $t->index($columns, $name);
        });
    }

    private function dropIndexSafe(string $table, string $name): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        $existing = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->unique();
        if (!$existing->contains($name)) {
            return;
        }
        Schema::table($table, function (Blueprint $t) use ($name) {
            $t->dropIndex($name);
        });
    }
}
