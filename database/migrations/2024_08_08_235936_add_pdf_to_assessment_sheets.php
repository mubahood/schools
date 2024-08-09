<?php

use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfToAssessmentSheets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assessment_sheets', function (Blueprint $table) {
            $table->foreignIdFor(Term::class)->nullable()->change();
            $table->string('generated')->default('No')->nullable();
            $table->text('pdf_link')->nullable();
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
