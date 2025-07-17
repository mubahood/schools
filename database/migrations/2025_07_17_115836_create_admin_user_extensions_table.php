<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_extensions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('student_sourced_by_agent')->default('No')->nullable();
            $table->unsignedBigInteger('student_sourced_by_agent_id')->nullable();
            $table->integer('student_sourced_by_agent_commission')->nullable();
            $table->string('student_sourced_by_agent_commission_paid')->default('No')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_extensions');
    }
}
