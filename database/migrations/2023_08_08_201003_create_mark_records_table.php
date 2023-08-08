<?php

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Enterprise;
use App\Models\MainCourse;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TermlyReportCard;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mark_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(TermlyReportCard::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->foreignIdFor(Administrator::class)->nullable();
            $table->foreignIdFor(AcademicClass::class)->nullable();
            $table->foreignIdFor(AcademicClassSctream::class)->nullable();
            $table->foreignIdFor(MainCourse::class)->nullable();
            $table->foreignIdFor(Subject::class)->nullable();
            $table->integer('bot_score')->default(null)->nullable();
            $table->integer('mot_score')->default(null)->nullable();
            $table->integer('eot_score')->default(null)->nullable();
            $table->string('bot_is_submitted')->default('No')->nullable();
            $table->string('mot_is_submitted')->default('No')->nullable();
            $table->string('eot_is_submitted')->default('No')->nullable();
            $table->string('bot_missed')->default('Yes')->nullable();
            $table->string('mot_missed')->default('Yes')->nullable();
            $table->string('eot_missed')->default('Yes')->nullable();
            $table->string('initials')->default(null)->nullable();
            $table->text('remarks')->default(null)->nullable();
        });
    }
    /* 	
 
*/
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mark_records');
    }
}
