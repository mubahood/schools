<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdExpiryDatesToEnterprises extends Migration
{
    public function up()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->date('student_id_expiry_date')->nullable()->after('school_pay_last_accepted_date');
            $table->date('employee_id_expiry_date')->nullable()->after('student_id_expiry_date');
        });
    }

    public function down()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->dropColumn(['student_id_expiry_date', 'employee_id_expiry_date']);
        });
    }
}
