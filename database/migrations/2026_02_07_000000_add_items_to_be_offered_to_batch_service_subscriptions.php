<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemsToBeOfferedToBatchServiceSubscriptions extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('batch_service_subscriptions', 'items_to_be_offered')) {
            Schema::table('batch_service_subscriptions', function (Blueprint $table) {
                $table->text('items_to_be_offered')->nullable()->after('to_be_managed_by_inventory');
            });
        }
    }

    public function down()
    {
        Schema::table('batch_service_subscriptions', function (Blueprint $table) {
            $table->dropColumn('items_to_be_offered');
        });
    }
}
