<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentHasSemetersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_has_semeters', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class, 'enterprise_id');
            $table->foreignIdFor(User::class, 'student_id');
            $table->foreignIdFor(Term::class, 'term_id');
            $table->foreignIdFor(AcademicYear::class, 'academic_year_id');
            $table->integer('year_name')->nullable();
            $table->integer('semester_name')->nullable();
            $table->string('update_fees_balance')->nullable()->default('No');
            $table->integer('set_fees_balance_amount')->nullable()->default(0);
            $table->string('enrolled_by_id')->nullable();
            $table->text('services')->nullable();
            $table->string('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_has_semeters');
    }
}
