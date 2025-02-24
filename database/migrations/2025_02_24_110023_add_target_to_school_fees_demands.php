<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetToSchoolFeesDemands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_fees_demands', function (Blueprint $table) {
            $table->string('has_specific_students')->default('No');
            $table->text('target_students')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_fees_demands', function (Blueprint $table) {
            //
        });
    }
}
