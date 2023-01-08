<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDueTermToBursaryBeneficiaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bursary_beneficiaries', function (Blueprint $table) {
            //
        });
    } 

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bursary_beneficiaries', function (Blueprint $table) {
            $table->bigInteger('due_academic_year_id')->default(0)->nullable();
            $table->bigInteger('due_term_id')->default(0)->nullable(); 
        });
    }
}
