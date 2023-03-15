<?php

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\SecondarySubject;
use App\Models\SecondaryTermlyReportCard;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecondaryReportCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secondary_report_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(AcademicYear::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->foreignIdFor(SecondaryTermlyReportCard::class)->nullable();
            $table->foreignIdFor(Administrator::class)->nullable();
            $table->foreignIdFor(AcademicClass::class)->nullable();
            $table->text('class_teacher_comment')->nullable();
            $table->text('head_teacher_comment')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secondary_report_cards');
    }
}
