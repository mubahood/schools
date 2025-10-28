<?php

use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->text('title')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignIdFor(User::class, 'teacher_1_on_duty_id')->nullable();
            $table->foreignIdFor(User::class, 'teacher_2_on_duty_id')->nullable();
            $table->foreignIdFor(User::class, 'head_of_week_id')->nullable();
            $table->integer('total_days')->default(0);
            $table->integer('total_boys_present')->default(0);
            $table->integer('total_girls_present')->default(0);
            $table->text('top_absentees')->nullable();
            $table->text('top_punctuals')->nullable();
            $table->text('remarks')->nullable();
            $table->string('type')->nullable();
            $table->string('pdf_processed')->nullable();
            $table->text('pdf_path')->nullable();
            $table->string('target_audience_type')->nullable();
            $table->text('target_audience_data')->nullable();
            $table->longText('attendance_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session_reports');
    }
}
