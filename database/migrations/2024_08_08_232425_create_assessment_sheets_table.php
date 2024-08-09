<?php

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Enterprise;
use App\Models\Exam;
use App\Models\Term;
use App\Models\TermlyReportCard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_sheets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Term::class);
            $table->text('title')->nullable();
            $table->string('type')->nullable();
            $table->foreignIdFor(AcademicClassSctream::class)->nullable();
            $table->foreignIdFor(AcademicClass::class)->nullable();
            $table->foreignIdFor(TermlyReportCard::class)->nullable();
            $table->integer('total_students')->nullable();
            $table->integer('first_grades')->nullable();
            $table->integer('second_grades')->nullable();
            $table->integer('third_grades')->nullable();
            $table->integer('fourth_grades')->nullable();
            $table->integer('x_grades')->nullable();
            $table->text('name_of_teacher')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_sheets');
    }
}
