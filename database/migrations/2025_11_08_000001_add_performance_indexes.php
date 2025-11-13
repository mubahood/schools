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
        // Direct Messages - frequently queried fields
        Schema::table('direct_messages', function (Blueprint $table) {
            // $table->index('status', 'idx_direct_messages_status');
            // $table->index('enterprise_id', 'idx_direct_messages_enterprise');
            // $table->index(['enterprise_id', 'status'], 'idx_direct_messages_ent_status');
            // $table->index('created_at', 'idx_direct_messages_created');
        });

        // Participants - attendance queries (type is TEXT, so limit index length)
        Schema::table('participants', function (Blueprint $table) {
            // Skip type column as it's TEXT field
            // $table->index(['enterprise_id', 'created_at'], 'idx_participants_attendance');
            // $table->index(['session_id', 'is_present'], 'idx_participants_session');
            // $table->index('administrator_id', 'idx_participants_admin');
        });

        // Session Reports - reporting queries
        Schema::table('session_reports', function (Blueprint $table) {
            // $table->index(['enterprise_id', 'type', 'start_date'], 'idx_session_reports_query');
            // $table->index('pdf_processed', 'idx_session_reports_pdf');
        });

        // Admin Users - student queries
        Schema::table('admin_users', function (Blueprint $table) {
            // $table->index(['enterprise_id', 'user_type', 'status'], 'idx_users_query');
            // $table->index('current_class_id', 'idx_users_class');
            // $table->index(['sex', 'status'], 'idx_users_gender_status');
        });

        // Sessions - attendance sessions
        Schema::table('sessions', function (Blueprint $table) {
            // $table->index(['enterprise_id', 'type', 'created_at'], 'idx_sessions_query');
            // $table->index(['academic_year_id', 'term_id'], 'idx_sessions_academic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->dropIndex('idx_direct_messages_status');
            $table->dropIndex('idx_direct_messages_enterprise');
            $table->dropIndex('idx_direct_messages_ent_status');
            $table->dropIndex('idx_direct_messages_created');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('idx_participants_attendance');
            $table->dropIndex('idx_participants_session');
            $table->dropIndex('idx_participants_admin');
        });

        Schema::table('session_reports', function (Blueprint $table) {
            $table->dropIndex('idx_session_reports_query');
            $table->dropIndex('idx_session_reports_pdf');
        });

        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropIndex('idx_users_query');
            $table->dropIndex('idx_users_class');
            $table->dropIndex('idx_users_gender_status');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_query');
            $table->dropIndex('idx_sessions_academic');
        });
    }
};
