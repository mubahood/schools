<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParentCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parent_courses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name')->nullable();
            $table->text('short_name')->nullable();
            $table->text('code')->nullable();
            $table->text('type')->default('secondary')->nullable();
            $table->tinyInteger('is_verified')->default(1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parent_courses');
    }
}
