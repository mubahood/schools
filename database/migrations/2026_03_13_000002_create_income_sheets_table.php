<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeSheetsTable extends Migration
{
    public function up()
    {
        Schema::create('income_sheets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('enterprise_id');
            $table->foreignId('term_id');
            $table->string('title')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('type')->default('DAY_AND_BOARDING');
            $table->string('status')->default('Not Generated');
        });
    }

    public function down()
    {
        Schema::dropIfExists('income_sheets');
    }
}
