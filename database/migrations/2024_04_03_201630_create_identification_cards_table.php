<?php

use App\Models\Enterprise;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdentificationCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('identification_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->string('target_type');
            $table->text('classes')->nullable();
            $table->text('users')->nullable();
            $table->text('file_link')->nullable();
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
        Schema::dropIfExists('identification_cards');
    }
}
