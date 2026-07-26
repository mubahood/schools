<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSplitTransactionItemsTable extends Migration
{
    public function up()
    {
        Schema::create('split_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('split_transaction_id');
            $table->unsignedBigInteger('to_administrator_id');
            $table->bigInteger('amount')->default(0);
            $table->unsignedBigInteger('to_transaction_id')->nullable();
            $table->timestamps();

            $table->foreign('split_transaction_id')
                ->references('id')->on('split_transactions')->onDelete('cascade');
            $table->index('split_transaction_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('split_transaction_items');
    }
}
