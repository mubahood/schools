<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(\App\Models\Enterprise::class);
            $table->string('target_type')->nullable();
            $table->text('classes')->nullable();
            $table->text('file_link')->nullable();
            $table->text('template')->nullable();
            $table->string('do_generate_pdf')->default('No')->nullable();
            $table->string('pdf_generated')->default('No')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_exports');
    }
}
