<?php

use App\Models\Enterprise;
use App\Models\Room;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slots', function (Blueprint $table) {
            // $table->id();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Room::class);
            $table->foreignIdFor(Term::class);
            $table->text('slotName')->nullable();
            $table->text('studentName')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slots');
    }
}
