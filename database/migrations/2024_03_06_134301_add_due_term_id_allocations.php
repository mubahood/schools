<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDueTermIdAllocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_slot_allocations', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Term::class, 'due_term_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_slot_allocations', function (Blueprint $table) {
            //
        });
    }
}
