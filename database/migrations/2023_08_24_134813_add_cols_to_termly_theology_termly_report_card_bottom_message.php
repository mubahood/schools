<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToTermlyTheologyTermlyReportCardBottomMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('theology_termly_report_cards', function (Blueprint $table) {
            $table->string('generate_class_teacher_comment')->default('No')->nullable();
            $table->string('generate_head_teacher_comment')->default('No')->nullable();
            $table->string('generate_positions')->default('No')->nullable();
            $table->string('display_positions')->default('No')->nullable();
            $table->text('bottom_message')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('theology_termly_report_cards', function (Blueprint $table) {
            //
        });
    }
}
