<?php

use App\Models\Enterprise;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->text('pdf_path')->nullable();
            $table->integer('total_students')->default(0)->nullable();
            $table->integer('expected_fees')->default(0)->nullable();
            $table->integer('fees_collected_manual_entry')->default(0)->nullable();
            $table->integer('fees_collected_schoolpay')->default(0)->nullable();
            $table->integer('fees_collected_total')->default(0)->nullable();
            $table->integer('fees_collected_other')->default(0)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_reports');
    }
}
