<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->string('has_personal_info')->nullable()->default('No');
            $table->string('has_educational_info')->nullable()->default('No');
            $table->string('has_account_info')->nullable()->default('No');
            $table->string('diploma_school_name')->nullable()->default('No');
            $table->string('diploma_year_graduated')->nullable()->default('No');
            $table->string('certificate_school_name')->nullable()->default('No');
            $table->string('certificate_year_graduated')->nullable()->default('No');
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            //
        });
    }
}
