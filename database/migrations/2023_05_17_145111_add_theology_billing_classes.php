<?php

use App\Models\TheologyClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTheologyBillingClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('academic_class_fees', function (Blueprint $table) {
            $table->string('type')->default('Secular')->nullable();
            $table->foreignIdFor(TheologyClass::class)->nullable();
            $table->string('cycle')->default('Termly')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('academic_class_fees', function (Blueprint $table) {
            //
        });
    }
}
