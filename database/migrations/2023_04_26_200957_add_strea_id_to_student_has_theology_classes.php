<?php

use App\Models\TheologyStream;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStreaIdToStudentHasTheologyClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_has_theology_classes', function (Blueprint $table) {
            $table->foreignIdFor(TheologyStream::class)->nullable();  
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
        Schema::table('student_has_theology_classes', function (Blueprint $table) {
            //
        });
    }
}
