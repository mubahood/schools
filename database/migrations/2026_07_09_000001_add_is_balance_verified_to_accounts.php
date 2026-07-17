<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBalanceVerifiedToAccounts extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->tinyInteger('is_balance_verified')->default(0)->after('balance');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('is_balance_verified');
        });
    }
}
