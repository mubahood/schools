<?php

use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentDataImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_data_imports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(User::class, 'created_by_id')->nullable();
            $table->text('title')->nullable();
            $table->string('identify_by')->nullable();
            $table->string('school_pay_column')->nullable();
            $table->string('reg_number_column')->nullable();
            $table->string('class_column')->nullable();
            $table->string('status')->default('Pending'); // pending, processing, completed, failed
            $table->text('summary')->nullable();
            $table->text('file_path')->nullable();
            $table->string('name_column')->nullable();
            $table->string('gender_column')->nullable();
            $table->string('dob_column')->nullable();
            $table->string('phone_column')->nullable();
            $table->string('email_column')->nullable();
            $table->string('address_column')->nullable();
            $table->string('parent_name_column')->nullable();
            $table->string('parent_phone_column')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_data_imports');
    }
}
