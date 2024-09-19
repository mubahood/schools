<?php

use App\Models\Enterprise;
use App\Models\Service;
use App\Models\ServiceSubscription;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceSubscriptionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_subscription_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(ServiceSubscription::class, 'service_subscription_id')->nullable();
            $table->string('is_processed')->default('No')->nullable();
            $table->foreignIdFor(Enterprise::class, 'enterprise_id')->nullable();
            $table->foreignIdFor(Service::class, 'service_id')->nullable();
            $table->foreignIdFor(Administrator::class, 'administrator_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('total')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_subscription_items');
    }
}
