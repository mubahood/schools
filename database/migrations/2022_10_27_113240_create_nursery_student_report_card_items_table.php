<?php

use App\Models\AcademicClass;
use App\Models\Competence;
use App\Models\Enterprise;
use App\Models\NurseryTermlyReportCard;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNurseryStudentReportCardItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nursery_student_report_card_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Competence::class);
            $table->foreignIdFor(NurseryTermlyReportCard::class);
            $table->foreignIdFor(AcademicClass::class);
            $table->foreignIdFor(Administrator::class, 'student_id');
            $table->foreignIdFor(Administrator::class, 'teacher_id');
            $table->string('score')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_submitted')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nursery_student_report_card_items');
    }
}
