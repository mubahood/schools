<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenerateTheologyClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generate_theology_classes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->string('BC')->nullable();
            $table->string('MC')->nullable();
            $table->string('TC')->nullable();
            $table->string('P1')->nullable();
            $table->string('P2')->nullable();
            $table->string('P3')->nullable();
            $table->string('P4')->nullable();
            $table->string('P5')->nullable();
            $table->string('P6')->nullable();
            $table->string('P7')->nullable();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generate_theology_classes');
    }
}
