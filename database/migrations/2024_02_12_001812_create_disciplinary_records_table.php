<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisciplinaryRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->id(); 
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class, 'enterprise_id');
            $table->foreignIdFor(User::class, 'administrator_id');
            $table->foreignIdFor(User::class, 'reported_by_id');
            $table->foreignIdFor(AcademicYear::class, 'academic_year_id');
            $table->foreignIdFor(Term::class, 'term_id');
            $table->string('type');
            $table->text('title');
            $table->string('status')->default('Active');
            $table->text('description')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('hm_comment')->nullable();
            $table->text('parent_comment')->nullable();
            $table->text('teacher_comment')->nullable();
            $table->text('student_comment')->nullable();
            $table->text('photo')->nullable();
            $table->text('file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disciplinary_records');
    }
}
