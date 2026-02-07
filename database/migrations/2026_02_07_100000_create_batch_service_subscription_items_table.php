<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchServiceSubscriptionItemsTable extends Migration
{
    public function up()
    {
        Schema::create('batch_service_subscription_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_service_subscription_id');
            $table->unsignedBigInteger('stock_item_category_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->index('batch_service_subscription_id', 'idx_batch_sub_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('batch_service_subscription_items');
    }
}
