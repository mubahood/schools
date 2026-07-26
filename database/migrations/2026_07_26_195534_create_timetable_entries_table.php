<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimetableEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('academic_class_id');
            $table->unsignedBigInteger('academic_class_sctream_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('timetable_room_id')->nullable();
            $table->tinyInteger('day_of_week');          // 1=Mon … 6=Sat
            $table->time('start_time');
            $table->smallInteger('duration_minutes')->default(40);
            $table->string('color', 20)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->index(['enterprise_id', 'day_of_week', 'academic_class_id', 'start_time'], 'tt_class_conflict');
            $table->index(['enterprise_id', 'day_of_week', 'teacher_id', 'start_time'], 'tt_teacher_conflict');
            $table->index(['enterprise_id', 'academic_year_id', 'term_id'], 'tt_year_term');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timetable_entries');
    }
}
