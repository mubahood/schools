<?php

use App\Models\Enterprise;
use App\Models\Term;
use App\Models\TransportRoute;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Administrator::class, 'driver_id')->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->foreignIdFor(TransportRoute::class)->nullable();
            $table->date('date')->nullable();
            $table->string('status')->default('Ongoing')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('start_gps')->nullable();
            $table->string('end_gps')->nullable();
            $table->string('trip_direction')->nullable();
            $table->string('start_mileage')->nullable();
            $table->string('end_mileage')->nullable();
            $table->integer('expected_passengers')->nullable();
            $table->integer('actual_passengers')->nullable();
            $table->integer('absent_passengers')->nullable();
            $table->string('local_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
