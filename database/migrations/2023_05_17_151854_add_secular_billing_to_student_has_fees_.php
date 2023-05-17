<?php

use App\Models\AcademicClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecularBillingToStudentHasFees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_has_fees', function (Blueprint $table) {
            $table->foreignIdFor(AcademicClass::class)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_has_fees', function (Blueprint $table) {
            //
        });
    }
}
