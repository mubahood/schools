<?php

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Service;
use App\Models\Subject;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class)->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->foreignIdFor(Administrator::class)->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->foreignIdFor(AcademicYear::class)->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->foreignIdFor(Term::class)->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->foreignIdFor(AcademicClass::class)->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->foreignIdFor(Subject::class)->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->foreignIdFor(Service::class)->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->tinyInteger('is_present')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participants');
    }
}
