<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegNumToStudentHasSemeters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_has_semeters', function (Blueprint $table) {
            $table->string('registration_number')->nullable();
            $table->string('schoolpay_code')->nullable();
            $table->string('pegpay_code')->nullable();
            $table->string('is_processed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_has_semeters', function (Blueprint $table) {
            //
        });
    }
}
