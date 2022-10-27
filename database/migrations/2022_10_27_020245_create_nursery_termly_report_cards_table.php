<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNurseryTermlyReportCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nursery_termly_report_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(AcademicYear::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->text('report_title')->nullable();
            $table->text('general_commnunication')->nullable();
            $table->boolean('do_update')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nursery_termly_report_cards');
    }
}
