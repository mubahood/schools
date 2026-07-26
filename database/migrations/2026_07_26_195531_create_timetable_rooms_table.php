<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimetableRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timetable_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id');
            $table->char('building_id', 36)->nullable();
            $table->string('name');
            $table->unsignedInteger('capacity')->default(0);
            $table->string('room_type')->default('Classroom');
            $table->text('description')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->index('enterprise_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timetable_rooms');
    }
}
