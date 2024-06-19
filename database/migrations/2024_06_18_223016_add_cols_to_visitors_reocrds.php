<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToVisitorsReocrds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visitor_records', function (Blueprint $table) {
            $table->unsignedBigInteger('due_term_id')->nullable();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('status')->nullable()->default('Signed In');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visitor_records', function (Blueprint $table) {
            //
        });
    }
}
