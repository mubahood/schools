<?php

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\NurseryTermlyReportCard;
use App\Models\Term;
use App\Models\TermlyReportCard;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNurseryStudentReportCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nursery_student_report_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(Term::class);
            $table->foreignIdFor(Administrator::class,'student_id');
            $table->foreignIdFor(AcademicClass::class); 
            $table->foreignIdFor(NurseryTermlyReportCard::class); 
            $table->text('class_teacher_comment')->nullable();
            $table->text('head_teacher_comment')->nullable();
            $table->boolean('class_teacher_commented')->default(0)->nullable();
            $table->boolean('head_teacher_commented')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nursery_student_report_cards');
    }
}
