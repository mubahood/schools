<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->text('target')->nullable();
            $table->text('secular_casses')->nullable();
            $table->text('theology_classes')->nullable();
            $table->text('secular_stream_id')->nullable();
            $table->text('theology_stream_id')->nullable();
            $table->integer('total_expected')->nullable()->default(0);
            $table->integer('total_present')->nullable()->default(0);
            $table->integer('total_absent')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) {
            //
        });
    }
}
