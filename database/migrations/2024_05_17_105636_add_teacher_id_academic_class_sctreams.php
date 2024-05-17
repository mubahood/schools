<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeacherIdAcademicClassSctreams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('academic_class_sctreams', function (Blueprint $table) {
            $table->foreignIdFor(User::class, 'teacher_id')->nullable();
        });
        Schema::table('theology_streams', function (Blueprint $table) {
            $table->foreignIdFor(User::class, 'teacher_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acadteacherc_class_sctreams', function (Blueprint $table) {
            //
        });
    }
}
