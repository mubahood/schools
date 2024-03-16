<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCollsToTransportRoutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->decimal('single_trip_fare', 12, 2)->nullable();
            $table->decimal('round_trip_fare', 12, 2)->nullable();
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
