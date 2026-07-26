<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvanceOwingToReportFinances extends Migration
{
    public function up()
    {
        Schema::table('report_finances', function (Blueprint $table) {
            $table->decimal('total_fees_advance', 15, 2)->default(0)->after('total_school_fees_balance');
            $table->decimal('total_fees_owing', 15, 2)->default(0)->after('total_fees_advance');
            $table->unsignedInteger('count_students_advance')->default(0)->after('total_fees_owing');
            $table->unsignedInteger('count_students_owing')->default(0)->after('count_students_advance');
        });
    }

    public function down()
    {
        Schema::table('report_finances', function (Blueprint $table) {
            $table->dropColumn(['total_fees_advance', 'total_fees_owing', 'count_students_advance', 'count_students_owing']);
        });
    }
}
