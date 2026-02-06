<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintToSchemWorkItems extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds indexes for better query performance on scheme work items.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schem_work_items', function (Blueprint $table) {
            // Add composite index for better query performance
            // This helps with filtering and searching, but doesn't enforce uniqueness
            $table->index(['enterprise_id', 'term_id', 'subject_id', 'teacher_id'], 'idx_scheme_work_filter');
            $table->index(['week', 'teacher_status'], 'idx_week_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schem_work_items', function (Blueprint $table) {
            // Drop the indexes
            $table->dropIndex('idx_scheme_work_filter');
            $table->dropIndex('idx_week_status');
        });
    }
}
