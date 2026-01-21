<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToBeManagedByInventoryToBatchServiceSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batch_service_subscriptions', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('batch_service_subscriptions', 'to_be_managed_by_inventory')) {
                $table->string('to_be_managed_by_inventory')->nullable()->default('No')->after('is_processed');
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
        Schema::table('batch_service_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('batch_service_subscriptions', 'to_be_managed_by_inventory')) {
                $table->dropColumn('to_be_managed_by_inventory');
            }
        });
    }
}
