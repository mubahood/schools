<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('lesson_plans', 'supervisor_id')) {
                $table->unsignedInteger('supervisor_id')->nullable()->index()->after('teacher_id');
            }

            if (!Schema::hasColumn('lesson_plans', 'submission_comment')) {
                $table->text('submission_comment')->nullable()->after('status');
            }

            if (!Schema::hasColumn('lesson_plans', 'supervisor_comment')) {
                $table->text('supervisor_comment')->nullable()->after('submission_comment');
            }

            if (!Schema::hasColumn('lesson_plans', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('supervisor_comment');
            }

            if (!Schema::hasColumn('lesson_plans', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            }

            if (!Schema::hasColumn('lesson_plans', 'reviewed_by')) {
                $table->unsignedInteger('reviewed_by')->nullable()->index()->after('reviewed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_plans', 'reviewed_by')) {
                $table->dropColumn('reviewed_by');
            }
            if (Schema::hasColumn('lesson_plans', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('lesson_plans', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
            if (Schema::hasColumn('lesson_plans', 'supervisor_comment')) {
                $table->dropColumn('supervisor_comment');
            }
            if (Schema::hasColumn('lesson_plans', 'submission_comment')) {
                $table->dropColumn('submission_comment');
            }
            if (Schema::hasColumn('lesson_plans', 'supervisor_id')) {
                $table->dropColumn('supervisor_id');
            }
        });
    }
};
