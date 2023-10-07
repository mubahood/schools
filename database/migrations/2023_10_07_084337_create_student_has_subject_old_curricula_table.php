<?php

use App\Models\Enterprise;
use App\Models\StudentHasClass;
use App\Models\Subject;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentHasSubjectOldCurriculaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_has_subject_old_curricula', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(StudentHasClass::class);
            $table->foreignIdFor(Subject::class);
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(Enterprise::class);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_has_subject_old_curricula');
    }
}
