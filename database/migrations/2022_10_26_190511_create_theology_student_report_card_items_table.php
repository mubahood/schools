<?php

use App\Models\Enterprise;
use App\Models\Subject;
use App\Models\TheologryStudentReportCard;
use App\Models\TheologySubject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologyStudentReportCardItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theology_student_report_card_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(TheologySubject::class);
            $table->foreignIdFor(TheologryStudentReportCard::class);

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
            $table->integer('total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theology_student_report_card_items');
    }
}
