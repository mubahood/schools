<?php

use App\Models\AcademicClass;
use App\Models\Enterprise;
use App\Models\TheologyClass;
use App\Models\TheologyExam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologyExamHasClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theology_exam_has_classes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(TheologyExam::class);
            $table->foreignIdFor(TheologyClass::class);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theology_exam_has_classes');
    }
}
