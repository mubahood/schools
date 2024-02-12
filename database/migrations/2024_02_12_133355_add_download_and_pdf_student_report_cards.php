<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadAndPdfStudentReportCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_report_cards', function (Blueprint $table) {
            $table->string('parent_can_view')->default('No')->nullable();
            $table->string('is_ready')->default('No')->nullable();
            $table->date('date_gnerated')->nullable();
            $table->text('pdf_url')->nullable();
            $table->text('vatar')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_report_cards', function (Blueprint $table) {
            //
        });
    }
}
