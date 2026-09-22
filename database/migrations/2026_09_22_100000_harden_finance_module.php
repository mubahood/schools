<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the finance module safe to trust: nothing is destroyed outright,
 * every change is attributable, and the queries the pages actually run are
 * indexed.
 *
 * Schema only. The repair of existing rows lives in `finance:repair`, so it
 * can be rehearsed with a dry run and re-run if it is interrupted.
 */
return new class extends Migration
{
    public function up()
    {
        // ── Deletions become recoverable ────────────────────────────────
        foreach (['financial_records', 'creditor_records', 'creditor_payments'] as $t) {
            if (!Schema::hasColumn($t, 'deleted_at')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->softDeletes()->index();
                });
            }
        }

        // ── unit_price must hold what amount holds ──────────────────────
        // int(11) tops out at 2,147,483,647; amount is bigint. A single line
        // over ~2.1bn would have silently wrapped.
        DB::statement('ALTER TABLE financial_records MODIFY unit_price BIGINT NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE financial_records MODIFY quantity BIGINT NOT NULL DEFAULT 1');

        // ── Index what the pages actually filter on ─────────────────────
        // Every list query is (enterprise, type, term) and every report is
        // (enterprise, type, date). Neither had an index; `type` had none at all.
        $existing = collect(DB::select('SHOW INDEX FROM financial_records'))->pluck('Key_name')->unique()->all();
        if (!in_array('idx_fr_ent_type_term', $existing, true)) {
            DB::statement('CREATE INDEX idx_fr_ent_type_term ON financial_records (enterprise_id, type, term_id)');
        }
        if (!in_array('idx_fr_ent_type_date', $existing, true)) {
            DB::statement('CREATE INDEX idx_fr_ent_type_date ON financial_records (enterprise_id, type, payment_date)');
        }
        if (!in_array('idx_fr_parent_account', $existing, true)) {
            DB::statement('CREATE INDEX idx_fr_parent_account ON financial_records (parent_account_id)');
        }

        $credIdx = collect(DB::select('SHOW INDEX FROM creditor_records'))->pluck('Key_name')->unique()->all();
        if (!in_array('idx_cr_ent_status', $credIdx, true)) {
            DB::statement('CREATE INDEX idx_cr_ent_status ON creditor_records (enterprise_id, status)');
        }
        if (!in_array('idx_cr_fin_record', $credIdx, true)) {
            DB::statement('CREATE INDEX idx_cr_fin_record ON creditor_records (financial_record_id)');
        }
        $payIdx = collect(DB::select('SHOW INDEX FROM creditor_payments'))->pluck('Key_name')->unique()->all();
        if (!in_array('idx_cp_record', $payIdx, true)) {
            DB::statement('CREATE INDEX idx_cp_record ON creditor_payments (creditor_record_id)');
        }

        // ── Who changed what, and what it looked like before ────────────
        if (!Schema::hasTable('finance_audits')) {
            Schema::create('finance_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('enterprise_id')->index();
                $table->string('subject_type', 40);              // financial_record | creditor_record | creditor_payment
                $table->unsignedBigInteger('subject_id')->index();
                $table->string('action', 20);                    // created | updated | deleted | restored | paid
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('actor_name', 120)->nullable();
                $table->bigInteger('amount_before')->nullable();
                $table->bigInteger('amount_after')->nullable();
                $table->text('changes')->nullable();             // json diff
                $table->string('ip', 45)->nullable();
                $table->timestamps();
                $table->index(['enterprise_id', 'subject_type', 'created_at'], 'idx_fa_ent_type_time');
            });
        }

        // ── A home for records whose account or vote was deleted ─────────
        // Reports inner-joined these away, so money vanished from breakdowns
        // while staying in the totals. They now have somewhere real to sit.
        if (!Schema::hasColumn('accounts', 'is_system')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('type');
            });
        }
    }

    public function down()
    {
        foreach (['financial_records', 'creditor_records', 'creditor_payments'] as $t) {
            if (Schema::hasColumn($t, 'deleted_at')) {
                Schema::table($t, fn (Blueprint $table) => $table->dropSoftDeletes());
            }
        }
        Schema::dropIfExists('finance_audits');
        if (Schema::hasColumn('accounts', 'is_system')) {
            Schema::table('accounts', fn (Blueprint $table) => $table->dropColumn('is_system'));
        }
    }
};
