<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchServiceSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_service_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('enterprise_id');
            $table->integer('service_id');
            $table->integer('quantity');
            $table->integer('total');
            $table->integer('due_academic_year_id');
            $table->integer('due_term_id');
            $table->string('link_with')->nullable();
            $table->integer('transport_route_id')->nullable();
            $table->integer('success_count')->nullable();
            $table->integer('fail_count')->nullable();
            $table->integer('total_count')->nullable();
            $table->string('trip_type')->nullable();
            $table->text('administrators')->nullable();
            $table->string('is_processed')->default('No');
            $table->text('processed_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_service_subscriptions');
    }
}
