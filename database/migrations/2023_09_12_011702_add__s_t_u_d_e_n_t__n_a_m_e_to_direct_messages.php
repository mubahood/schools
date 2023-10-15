<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSTUDENTNAMEToDirectMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->text('STUDENT_NAME')->nullable();
            $table->text('PARENT_NAME')->nullable();
            $table->text('STUDENT_CLASS')->nullable();
            $table->text('TEACHER_NAME')->nullable(); 
               
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            //
        });
    }
}
