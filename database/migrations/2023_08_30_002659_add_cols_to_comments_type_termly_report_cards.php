<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToCommentsTypeTermlyReportCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_report_cards', function (Blueprint $table) {
            $table->text('sports_comment')->nullable();
            $table->text('mentor_comment')->nullable();
            $table->text('nurse_comment')->nullable();
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
