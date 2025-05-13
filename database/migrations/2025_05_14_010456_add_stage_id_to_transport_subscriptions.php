<?php

use App\Models\TransportStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStageIdToTransportSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_subscriptions', function (Blueprint $table) {
            $table->foreignIdFor(TransportStage::class, 'transport_stage_id')->nullable();
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
