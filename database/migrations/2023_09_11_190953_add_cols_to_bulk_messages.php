<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToBulkMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bulk_messages', function (Blueprint $table) {
            $table->string('do_process_messages')->nullable();
            $table->string('processed_successfully')->nullable();
            $table->text('error_messages')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bulk_messages', function (Blueprint $table) {
            //
        });
    }
}
