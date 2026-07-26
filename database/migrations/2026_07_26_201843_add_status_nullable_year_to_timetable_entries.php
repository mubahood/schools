<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusNullableYearToTimetableEntries extends Migration
{
    public function up()
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            // Make year/term optional — timetable is now year-independent
            $table->unsignedBigInteger('academic_year_id')->nullable()->change();
            $table->unsignedBigInteger('term_id')->nullable()->change();
            // Add status
            $table->enum('status', ['draft', 'active', 'disabled'])->default('active')->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->unsignedBigInteger('academic_year_id')->nullable(false)->change();
        });
    }
}
