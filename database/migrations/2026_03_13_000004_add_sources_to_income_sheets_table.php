<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourcesToIncomeSheetsTable extends Migration
{
    public function up()
    {
        Schema::table('income_sheets', function (Blueprint $table) {
            $table->text('sources')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('income_sheets', function (Blueprint $table) {
            $table->dropColumn('sources');
        });
    }
}
