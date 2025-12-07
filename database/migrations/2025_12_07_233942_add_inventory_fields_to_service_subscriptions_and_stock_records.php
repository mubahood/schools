<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInventoryFieldsToServiceSubscriptionsAndStockRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add new fields to service_subscriptions table
        Schema::table('service_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('service_subscriptions', 'to_be_managed_by_inventory')) {
                $table->string('to_be_managed_by_inventory')->nullable()->default('No')->after('is_processed');
            }
            if (!Schema::hasColumn('service_subscriptions', 'is_service_offered')) {
                $table->string('is_service_offered')->nullable()->default('No')->after('to_be_managed_by_inventory');
            }
            if (!Schema::hasColumn('service_subscriptions', 'is_completed')) {
                $table->string('is_completed')->nullable()->default('No')->after('is_service_offered');
            }
            if (!Schema::hasColumn('service_subscriptions', 'stock_record_id')) {
                $table->bigInteger('stock_record_id')->unsigned()->nullable()->after('is_completed');
            }
            if (!Schema::hasColumn('service_subscriptions', 'inventory_provided_date')) {
                $table->date('inventory_provided_date')->nullable()->after('stock_record_id');
            }
            if (!Schema::hasColumn('service_subscriptions', 'inventory_provided_by_id')) {
                $table->bigInteger('inventory_provided_by_id')->unsigned()->nullable()->after('inventory_provided_date');
            }
        });

        // Add new field to stock_records table
        Schema::table('stock_records', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_records', 'service_subscription_id')) {
                $table->bigInteger('service_subscription_id')->unsigned()->nullable()->after('id');
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
            $table->dropColumn([
                'to_be_managed_by_inventory',
                'is_service_offered',
                'is_completed',
                'stock_record_id',
                'inventory_provided_date',
                'inventory_provided_by_id'
            ]);
        });

        Schema::table('stock_records', function (Blueprint $table) {
            $table->dropColumn('service_subscription_id');
        });
    }
}
