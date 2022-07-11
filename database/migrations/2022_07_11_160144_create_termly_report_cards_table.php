<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermlyReportCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('termly_report_cards', function (Blueprint $table) {

            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(Term::class);
            
            $table->tinyInteger('has_beginning_term')->default(0);
            $table->tinyInteger('has_mid_term')->default(0);
            $table->tinyInteger('has_end_term')->default(0);
            
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
        Schema::dropIfExists('termly_report_cards');
    }
}
