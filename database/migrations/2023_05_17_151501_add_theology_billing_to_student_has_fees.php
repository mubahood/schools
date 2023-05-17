<?php

use App\Models\TheologyClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTheologyBillingToStudentHasFees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_has_fees', function (Blueprint $table) {
            $table->foreignIdFor(TheologyClass::class)->nullable(); 
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
