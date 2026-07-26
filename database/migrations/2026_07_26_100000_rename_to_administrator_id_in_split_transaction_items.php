<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameToAdministratorIdInSplitTransactionItems extends Migration
{
    public function up()
    {
        Schema::table('split_transaction_items', function (Blueprint $table) {
            $table->renameColumn('to_administrator_id', 'to_account_id');
        });
    }

    public function down()
    {
        Schema::table('split_transaction_items', function (Blueprint $table) {
            $table->renameColumn('to_account_id', 'to_administrator_id');
        });
    }
}
