<?php

use App\Models\AcademicClass;
use App\Models\Enterprise;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchemWorkItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schem_work_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Term::class);
            $table->foreignIdFor(Subject::class);
            $table->foreignIdFor(User::class, 'teacher_id');
            $table->foreignIdFor(User::class, 'supervisor_id');
            $table->string('teacher_status')->default('pending');
            $table->text('teacher_comment')->nullable();
            $table->string('supervisor_status')->default('pending');
            $table->text('supervisor_comment')->nullable();
            $table->string('status')->default('pending');
            $table->integer('week')->default(1)->nullable();
            $table->integer('period')->default(1)->nullable();
            $table->text('topic')->nullable();
            $table->text('competence')->nullable();
            $table->text('methods')->nullable();
            $table->text('skills')->nullable();
            $table->text('suggested_activity')->nullable();
            $table->text('instructional_material')->nullable();
            $table->text('references')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schem_work_items');
    }
}
