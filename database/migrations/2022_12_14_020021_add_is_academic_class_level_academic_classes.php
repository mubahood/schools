<?php

use App\Models\AcademicClassLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAcademicClassLevelAcademicClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('academic_classes', function (Blueprint $table) {
            $table->foreignIdFor(AcademicClassLevel::class);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('academic_classes', function (Blueprint $table) {
            //
        });
    }
}
