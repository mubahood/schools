<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressiveAssessmentSheetsTable extends Migration
{
    public function up()
    {
        Schema::create('progressive_assessment_sheets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('enterprise_id')->nullable();
            $table->bigInteger('progressive_assessment_id')->nullable();
            $table->text('title')->nullable();
            $table->string('type')->default('Class');             // Class | Stream
            $table->bigInteger('academic_class_id')->nullable();
            $table->bigInteger('academic_class_sctream_id')->nullable();
            $table->integer('total_students')->default(0);
            // Cached computation columns
            $table->integer('grade_1')->default(0);
            $table->integer('grade_2')->default(0);
            $table->integer('grade_3')->default(0);
            $table->integer('grade_4')->default(0);
            $table->integer('grade_u')->default(0);
            $table->integer('grade_x')->default(0);
            $table->text('test_stats')->nullable();               // JSON: per-test class stats
            $table->text('subject_stats')->nullable();            // JSON: per-subject per-test
            $table->text('insights')->nullable();                 // JSON: most-improved, at-risk etc.
            $table->string('generated')->default('No');
            $table->text('pdf_link')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progressive_assessment_sheets');
    }
}
