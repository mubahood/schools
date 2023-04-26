<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologyStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theology_streams', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name')->nullable();  
            $table->foreignIdFor(Enterprise::class)->nullable();  

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theology_streams');
    }
}
