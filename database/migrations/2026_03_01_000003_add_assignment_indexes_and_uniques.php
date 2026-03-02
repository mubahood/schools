<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAssignmentIndexesAndUniques extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('assignment_submissions')) {
            return;
        }

        $duplicates = DB::table('assignment_submissions')
            ->select('assignment_id', 'student_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('assignment_id')
            ->whereNotNull('student_id')
            ->groupBy('assignment_id', 'student_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('assignment_submissions')
                ->where('assignment_id', $duplicate->assignment_id)
                ->where('student_id', $duplicate->student_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->unique(['assignment_id', 'student_id'], 'assignment_submissions_assignment_student_unique');
            $table->index(['assignment_id', 'status'], 'assignment_submissions_assignment_status_index');
            $table->index(['student_id', 'status'], 'assignment_submissions_student_status_index');
        });

        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->index(['enterprise_id', 'status'], 'assignments_enterprise_status_index');
                $table->index(['academic_class_id', 'stream_id'], 'assignments_class_stream_index');
                $table->index(['due_date'], 'assignments_due_date_index');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('assignment_submissions')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropUnique('assignment_submissions_assignment_student_unique');
                $table->dropIndex('assignment_submissions_assignment_status_index');
                $table->dropIndex('assignment_submissions_student_status_index');
            });
        }

        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropIndex('assignments_enterprise_status_index');
                $table->dropIndex('assignments_class_stream_index');
                $table->dropIndex('assignments_due_date_index');
            });
        }
    }
}
