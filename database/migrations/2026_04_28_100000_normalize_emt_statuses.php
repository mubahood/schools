<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class NormalizeEmtStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::getSchemaBuilder()->hasTable('employee_monitoring_records')) {
            DB::table('employee_monitoring_records')->where('status', 'Draft')->update(['status' => 'Pending']);
            DB::table('employee_monitoring_records')->where('status', 'Submitted')->update(['status' => 'Pending']);
            DB::table('employee_monitoring_records')->where('status', 'Reviewed')->update(['status' => 'Completed']);
            DB::table('employee_monitoring_records')->whereNull('status')->update(['status' => 'Pending']);

            DB::statement("ALTER TABLE employee_monitoring_records MODIFY status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }

        if (DB::getSchemaBuilder()->hasTable('employee_monitoring_report_records')) {
            DB::table('employee_monitoring_report_records')->where('status', 'Failed')->update(['status' => 'Skipped']);
            DB::table('employee_monitoring_report_records')->whereNull('status')->update(['status' => 'Pending']);

            DB::statement("ALTER TABLE employee_monitoring_report_records MODIFY status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Keep normalized statuses to avoid data inconsistency.
    }
}
