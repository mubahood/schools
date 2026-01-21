<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemsFieldsToServiceSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add items fields to service_subscriptions table
        Schema::table('service_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('service_subscriptions', 'items_to_be_offered')) {
                $table->text('items_to_be_offered')->nullable()->after('to_be_managed_by_inventory');
            }
            if (!Schema::hasColumn('service_subscriptions', 'items_have_been_offered')) {
                $table->text('items_have_been_offered')->nullable()->after('items_to_be_offered');
            }
        });

        // Add items fields to services table if they don't exist
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'to_be_managed_by_inventory')) {
                $table->string('to_be_managed_by_inventory')->nullable()->default('No');
            }
            if (!Schema::hasColumn('services', 'items_to_be_offered')) {
                $table->text('items_to_be_offered')->nullable();
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
            if (Schema::hasColumn('service_subscriptions', 'items_to_be_offered')) {
                $table->dropColumn('items_to_be_offered');
            }
            if (Schema::hasColumn('service_subscriptions', 'items_have_been_offered')) {
                $table->dropColumn('items_have_been_offered');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'to_be_managed_by_inventory')) {
                $table->dropColumn('to_be_managed_by_inventory');
            }
            if (Schema::hasColumn('services', 'items_to_be_offered')) {
                $table->dropColumn('items_to_be_offered');
            }
        });
    }
}
