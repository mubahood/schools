<?php

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\MainCourse;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(AcademicClass::class);
            $table->foreignIdFor(MainCourse::class);
            $table->foreignIdFor(Term::class);
            $table->text('class_type')->nullable();
            $table->text('theme')->nullable();
            $table->text('topic')->nullable();
            $table->text('description')->nullable();
            $table->integer('max_score')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities');
    }
}
