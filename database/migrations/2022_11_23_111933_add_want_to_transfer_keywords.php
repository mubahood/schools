<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWantToTransferKeywords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->boolean('want_to_transfer')->default(null)->nullable();
            $table->text('transfer_keyword')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_categories', function (Blueprint $table) {
            //
        });
    }
}
