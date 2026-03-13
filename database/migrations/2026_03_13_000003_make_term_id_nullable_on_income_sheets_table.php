<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeTermIdNullableOnIncomeSheetsTable extends Migration
{
    public function up()
    {
        Schema::table('income_sheets', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('income_sheets', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable(false)->change();
        });
    }
}
