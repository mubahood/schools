<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSplitTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('split_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id');
            $table->unsignedBigInteger('original_transaction_id');
            $table->bigInteger('original_amount')->default(0);
            $table->bigInteger('original_remaining_amount')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('status')->default('Draft'); // Draft | Applied
            $table->timestamps();

            $table->index('enterprise_id');
            $table->index('original_transaction_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('split_transactions');
    }
}
