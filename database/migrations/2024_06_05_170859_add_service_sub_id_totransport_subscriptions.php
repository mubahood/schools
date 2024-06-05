<?php

use App\Models\ServiceSubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceSubIdTotransportSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_subscriptions', function (Blueprint $table) {
            $table->foreignIdFor(ServiceSubscription::class, 'service_subscription_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transport_subscriptions', function (Blueprint $table) {
            //
        });
    }
}
