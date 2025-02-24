<?php

use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolFeesDemandsTable extends Migration
{
    /**
     * Run the migrations.
     *      
     * @return void
     */
    public function up()
    {
        Schema::create('school_fees_demands', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->text('message_1')->nullable();
            $table->text('message_2')->nullable();
            $table->text('message_3')->nullable();
            $table->text('message_4')->nullable();
            $table->text('message_5')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_fees_demands');
    }
}
