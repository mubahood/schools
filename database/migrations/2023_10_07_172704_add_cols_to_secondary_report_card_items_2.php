<?php

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToSecondaryReportCardItems2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('secondary_report_card_items', function (Blueprint $table) {
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(AcademicClass::class);
            $table->foreignIdFor(AcademicClassSctream::class)->nullable();
            $table->foreignIdFor(Term::class);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('secondary_report_card_items', function (Blueprint $table) {
            //
        });
    }
}
