<?php

use App\Models\Building;
use App\Models\Enterprise;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\StudentT;

class Slot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //a
        //Schema::drop('slots');
        Schema::create('room_slots', function (Blueprint $table) {
            $table->timestamps();
            $table->id();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Building::class);
            $table->foreignIdFor(Room::class);
            $table->foreignIdFor(User::class, 'current_student_id')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable()->default('Vacant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
