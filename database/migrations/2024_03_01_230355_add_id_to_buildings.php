<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdToBuildings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('buildings');
        Schema::create('buildings', function (Blueprint $table) {
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->uuid('id')->primary();
            $table->text('name')->nullable();
            $table->text('details')->nullable();
            $table->text('photo')->nullable();
            $table->integer('total_rooms')->nullable()->default(0);
            $table->integer('total_slots')->nullable()->default(0);
            $table->integer('total_slots_occupied')->nullable()->default(0);
            $table->integer('total_slots_vacant')->nullable()->default(0);
            $table->integer('total_slots_occupied_percent')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buildings', function (Blueprint $table) {
            //
        });
    }
}
