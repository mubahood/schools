<?php

use App\Models\Enterprise;
use App\Models\StudentReportCard;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentReportCardItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_report_card_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Subject::class);
            $table->foreignIdFor(StudentReportCard::class);

            $table->tinyInteger('did_bot')->default(0);
            $table->tinyInteger('did_mot')->default(0);
            $table->tinyInteger('did_eot')->default(0);

            $table->integer('bot_mark')->default(0);
            $table->integer('mot_mark')->default(0);
            $table->integer('eot_mark')->default(0);

            $table->text('grade_name')->nullable();
            $table->integer('aggregates')->default(0);
            $table->text('remarks')->nullable();
            $table->text('initials')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_report_card_items');
    }
}
