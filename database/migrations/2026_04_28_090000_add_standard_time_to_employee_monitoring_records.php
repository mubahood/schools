<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStandardTimeToEmployeeMonitoringRecords extends Migration
{
    public function up()
    {
        Schema::table('employee_monitoring_records', function (Blueprint $table) {
            // Expected arrival time set by the monitor when recording attendance
            $table->time('standard_time')->nullable()->after('time_in')
                ->comment('Standard/expected time the teacher should enter class');
        });
    }

    public function down()
    {
        Schema::table('employee_monitoring_records', function (Blueprint $table) {
            $table->dropColumn('standard_time');
        });
    }
}
