<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolPayHooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_pay_hooks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->longText('post_data')->nullable();
            $table->longText('get_data')->nullable();
            $table->longText('method')->nullable();
            $table->longText('server_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_pay_hooks');
    }
}
