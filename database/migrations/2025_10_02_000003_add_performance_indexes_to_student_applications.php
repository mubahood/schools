<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesToStudentApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_applications', function (Blueprint $table) {
            // Composite indexes for common queries
            $table->index(['selected_enterprise_id', 'status', 'created_at'], 
                          'idx_ent_status_date');
            $table->index(['email', 'selected_enterprise_id'], 
                          'idx_email_enterprise');
            $table->index(['status', 'submitted_at'], 
                          'idx_status_submitted');
            $table->index(['current_step', 'status'], 
                          'idx_step_status');
            $table->index(['last_activity_at'], 
                          'idx_last_activity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_applications', function (Blueprint $table) {
            $table->dropIndex('idx_ent_status_date');
            $table->dropIndex('idx_email_enterprise');
            $table->dropIndex('idx_status_submitted');
            $table->dropIndex('idx_step_status');
            $table->dropIndex('idx_last_activity');
        });
    }
}
