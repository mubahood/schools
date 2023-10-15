<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('visitors_name')->nullable();
            $table->string('visitors_address')->nullable();
            $table->string('visitors_phone_number')->nullable();
            $table->string('reason')->nullable();
            $table->string('who_to_see')->nullable();
            $table->string('relationship')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->integer('students_id')->nullable();
            $table->integer('employee_id')->nullable();
            $table->string('others')->nullable();
            $table->string('term')->nullable();
            $table->foreignIdFor(Enterprise::class);
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visitors');
    }
}
