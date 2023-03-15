<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\SecondarySubject;
use App\Models\SecondaryTermlyReportCard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecondaryTermlyReportCardItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secondary_report_card_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(AcademicYear::class)->nullable();
            $table->foreignIdFor(SecondarySubject::class)->nullable();
            $table->integer('secondary_report_card_id');
            $table->float('average_score')->default(0)->nullable();
            $table->text('generic_skills')->nullable();
            $table->text('remarks')->nullable();
            $table->string('teacher')->nullable();
        });
    }
    /* 
bot_mark	
mot_mark	
eot_mark	
grade_name	
aggregates	
remarks	
initials	
total
    */

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secondary_report_card_items');
    }
}
