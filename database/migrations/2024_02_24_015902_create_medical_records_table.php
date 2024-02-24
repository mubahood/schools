<?php

use App\Models\AcademicYear;
use App\Models\Disease;
use App\Models\Enterprise;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(Term::class);
            $table->foreignIdFor(User::class, 'posted_by_id');
            $table->foreignIdFor(User::class, 'patient_id');
            $table->foreignIdFor(Disease::class)->nullable();
            $table->integer('age')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('height')->nullable();
            $table->text('blood_group')->nullable();
            $table->text('blood_pressure')->nullable();
            $table->text('other_diseases')->nullable();
            $table->text('administered_drugs')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('recommended_drugs')->nullable();
            $table->text('specialist_instructions')->nullable();
            $table->text('specialist_remarks')->nullable();
            $table->string('has_disease')->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medical_records');
    }
}
