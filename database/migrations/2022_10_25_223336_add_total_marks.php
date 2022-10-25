<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalMarks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termly_report_cards', function (Blueprint $table) {
            $table->float('total_marks')->default(0)->nullable();
            $table->float('total_aggregates')->default(0)->nullable();
            $table->integer('position')->default(0)->nullable();
            $table->text('class_teacher_comment')->nullable();
            $table->text('head_teacher_comment')->nullable();
            $table->boolean('class_teacher_commented')->default(0)->nullable();
            $table->boolean('head_teacher_commented')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('termly_report_cards', function (Blueprint $table) {
            //
        });
    }
}
