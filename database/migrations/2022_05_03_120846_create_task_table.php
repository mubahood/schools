<?php

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->foreignIdFor(Administrator::class,'assigned_to')->default(1);
            $table->foreignIdFor(Administrator::class,'assigned_by')->default(1);
            $table->integer('submision_status')->default(0);
            $table->text('body')->nullable();
            $table->text('review_comment')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('submit_before')->nullable();
            $table->integer('review_status')->default(0);
            $table->integer('value')->default(1);
            $table->integer('category_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task');
    }
}
