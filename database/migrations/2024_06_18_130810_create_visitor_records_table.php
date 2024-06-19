<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitorRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visitor_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->unsignedBigInteger('visitor_id');
            $table->unsignedBigInteger('purpose_staff_id')->nullable();
            $table->unsignedBigInteger('purpose_student_id')->nullable();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('organization')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('nin')->nullable();
            $table->date('check_in_time')->nullable();
            $table->date('check_out_time')->nullable();
            $table->string('purpose')->nullable();
            $table->text('purpose_description')->nullable();
            $table->text('purpose_office')->nullable();
            $table->text('purpose_other')->nullable();
            $table->text('signature_src')->nullable();
            $table->text('signature_path')->nullable();
            $table->string('lacal_id')->nullable();
            $table->string('has_car')->nullable();
            $table->string('car_reg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visitor_records');
    }
}
