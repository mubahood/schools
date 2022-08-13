<?php

use App\Models\AcademicClass;
use App\Models\Enterprise;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentHasClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_has_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicClass::class);
            $table->foreignIdFor(Administrator::class);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_has_classes');
    }
}
