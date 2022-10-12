<?php

use App\Models\Enterprise;
use App\Models\TheologyClass;
use App\Models\TheologyExam;
use App\Models\TheologySubject;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologyMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theology_marks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(TheologyExam::class);
            $table->foreignIdFor(TheologyClass::class);
            $table->foreignIdFor(TheologySubject::class);
            $table->foreignIdFor(Administrator::class, 'student_id');
            $table->foreignIdFor(Administrator::class, 'teacher_id');
            $table->float('score')->default(0);
            $table->text('remarks')->nullable();
            $table->tinyInteger('is_submitted')->default(0);
            $table->tinyInteger('is_missed')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theology_marks');
    }
}
