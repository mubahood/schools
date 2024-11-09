<?php

use App\Models\TheologyClass;
use App\Models\TheologyStream;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetToAssessmentSheets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assessment_sheets', function (Blueprint $table) {
            $table->foreignIdFor(TheologyClass::class)->nullable();
            $table->foreignIdFor(TheologyStream::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assessment_sheets', function (Blueprint $table) {
            //
        });
    }
}
