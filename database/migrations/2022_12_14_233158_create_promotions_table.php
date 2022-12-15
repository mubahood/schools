<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('from_class');
            $table->bigInteger('to_class');
            $table->text('method');
            $table->bigInteger('student_id')->default(null)->nullable();
            $table->bigInteger('report_card_id')->default(null)->nullable();
            $table->bigInteger('mark')->default(null)->nullable();
            $table->bigInteger('grade')->default(null)->nullable();
            $table->bigInteger('position')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
