<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcessingFieldsToPaymentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add processing fields to credit_purchases table
        if (Schema::hasTable('credit_purchases')) {
            Schema::table('credit_purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('credit_purchases', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable();
                }
            });
        }

        // Add processing fields to service_subscriptions table
        if (Schema::hasTable('service_subscriptions')) {
            Schema::table('service_subscriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('service_subscriptions', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable();
                }
                if (!Schema::hasColumn('service_subscriptions', 'processed_count')) {
                    $table->integer('processed_count')->default(0);
                }
                if (!Schema::hasColumn('service_subscriptions', 'failed_count')) {
                    $table->integer('failed_count')->default(0);
                }
            });
        }

        // Add processing fields to service_subscription_items table
        if (Schema::hasTable('service_subscription_items')) {
            Schema::table('service_subscription_items', function (Blueprint $table) {
                if (!Schema::hasColumn('service_subscription_items', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable();
                }
                if (!Schema::hasColumn('service_subscription_items', 'processed_subscription_id')) {
                    $table->bigInteger('processed_subscription_id')->unsigned()->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove processing fields from credit_purchases
        if (Schema::hasTable('credit_purchases')) {
            Schema::table('credit_purchases', function (Blueprint $table) {
                if (Schema::hasColumn('credit_purchases', 'processed_at')) {
                    $table->dropColumn('processed_at');
                }
            });
        }

        // Remove processing fields from service_subscriptions
        if (Schema::hasTable('service_subscriptions')) {
            Schema::table('service_subscriptions', function (Blueprint $table) {
                $columns = ['processed_at', 'processed_count', 'failed_count'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('service_subscriptions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Remove processing fields from service_subscription_items
        if (Schema::hasTable('service_subscription_items')) {
            Schema::table('service_subscription_items', function (Blueprint $table) {
                $columns = ['processed_at', 'processed_subscription_id'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('service_subscription_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
