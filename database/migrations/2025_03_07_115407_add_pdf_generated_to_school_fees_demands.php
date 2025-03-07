<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfGeneratedToSchoolFeesDemands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_fees_demands', function (Blueprint $table) {
            $table->string('pdf_generated')->default('No')->nullable();
            $table->string('file_type')->nullable();
            $table->text('file_link')->nullable(); 
            
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
