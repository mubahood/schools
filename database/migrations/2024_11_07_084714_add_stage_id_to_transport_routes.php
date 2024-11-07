<?php

use App\Models\TransportStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStageIdToTransportRoutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->foreignIdFor(TransportStage::class, 'stage_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            //
        });
    }
}
