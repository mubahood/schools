<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\GradingScale;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologyTermlyReportCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theology_termly_report_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(GradingScale::class)->nullable();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(AcademicYear::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->boolean('has_beginning_term')->nullable();
            $table->boolean('has_mid_term')->nullable();
            $table->boolean('has_end_term')->nullable();
            $table->text('report_title')->nullable();
        });
    }






    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theology_termly_report_cards');
    }
}
