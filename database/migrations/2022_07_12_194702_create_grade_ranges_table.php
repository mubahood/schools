<?php

use App\Models\Enterprise;
use App\Models\GradingScale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeRangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grade_ranges', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(GradingScale::class);
            $table->foreignIdFor(Enterprise::class);
            $table->text('name')->nullable();
            $table->integer('min_mark')->default(0);
            $table->integer('max_mark')->default(0);
            $table->integer('aggregates')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grade_ranges');
    }
}
