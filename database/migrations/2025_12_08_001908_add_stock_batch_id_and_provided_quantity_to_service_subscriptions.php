<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockBatchIdAndProvidedQuantityToServiceSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_subscriptions', function (Blueprint $table) {
            // Add stock_batch_id field to record which batch was used
            if (!Schema::hasColumn('service_subscriptions', 'stock_batch_id')) {
                $table->bigInteger('stock_batch_id')->unsigned()->nullable()->after('stock_record_id');
            }
            
            // Add provided_quantity field to record how much was actually provided
            if (!Schema::hasColumn('service_subscriptions', 'provided_quantity')) {
                $table->decimal('provided_quantity', 10, 2)->nullable()->after('stock_batch_id')
                    ->comment('Actual quantity of inventory provided to student');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('service_subscriptions', 'provided_quantity')) {
                $table->dropColumn('provided_quantity');
            }
            if (Schema::hasColumn('service_subscriptions', 'stock_batch_id')) {
                $table->dropColumn('stock_batch_id');
            }
        });
    }
}
