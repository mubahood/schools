<?php

use App\Models\Enterprise;
use App\Models\TheologyClass;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologySubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theology_subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(TheologyClass::class);
            $table->foreignIdFor(Administrator::class, 'subject_teacher'); 
            $table->foreignIdFor(Administrator::class, 'teacher_1'); 
            $table->foreignIdFor(Administrator::class, 'teacher_2'); 
            $table->foreignIdFor(Administrator::class, 'teacher_3'); 
            $table->text('code')->nullable();
            $table->text('details')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theology_subjects');
    }
}
