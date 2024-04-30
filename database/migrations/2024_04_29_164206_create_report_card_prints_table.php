<?php

use App\Models\AcademicClass;
use App\Models\Enterprise;
use App\Models\TermlyReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyTermlyReportCard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportCardPrintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_card_prints', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->text('title')->nullable();
            $table->string('type')->nullable();
            $table->foreignIdFor(TheologyTermlyReportCard::class)->nullable();
            $table->foreignIdFor(TermlyReportCard::class)->nullable();
            $table->foreignIdFor(AcademicClass::class)->nullable();
            $table->foreignIdFor(TheologyClass::class)->nullable();
            $table->text('download_link')->nullable();
            $table->string('re_generate')->nullable();
            $table->string('theology_tempate')->nullable();
            $table->string('secular_tempate')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_card_prints');
    }
}
