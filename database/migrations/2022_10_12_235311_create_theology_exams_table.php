<?php

use App\Models\Enterprise;
use App\Models\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologyExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theology_exams', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 

            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Term::class);
            $table->text('type')->nullable();
            $table->text('name')->nullable();
            $table->integer('max_mark')->nullable();

        });
    }
 
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theology_exams');
    }
}
