<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnBoardWizardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('on_board_wizards', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys - relationships
            $table->unsignedBigInteger('administrator_id');
            $table->unsignedBigInteger('enterprise_id');
            
            // Onboarding progress tracking
            $table->string('current_step')->nullable()->default('step1');
            
            // Step completion tracking - Yes/No fields
            $table->string('email_is_verified')->nullable()->default('No');
            $table->string('school_details_added')->nullable()->default('No');
            $table->string('employees_added')->nullable()->default('No');
            $table->string('classes_approved')->nullable()->default('No');
            $table->string('subjects_added')->nullable()->default('No');
            $table->string('students_added')->nullable()->default('No');
            $table->string('help_videos_watched')->nullable()->default('No');
            $table->string('completed_on_boarding')->nullable()->default('No');
            
            // Video tracking
            $table->string('current_video')->nullable();
            $table->string('videos_completed_progress')->nullable()->default('0');
            
            // Additional useful fields for smooth onboarding
            $table->json('completed_steps')->nullable(); // Array of completed step names
            $table->json('step_data')->nullable(); // Store step-specific data temporarily
            $table->timestamp('last_activity_at')->nullable();
            $table->string('onboarding_status')->default('in_progress'); // in_progress, completed, paused
            $table->integer('total_progress_percentage')->default(0); // 0-100
            $table->text('notes')->nullable(); // Admin or system notes
            $table->string('preferred_language')->nullable()->default('en');
            $table->string('skip_help_videos')->nullable()->default('No');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('on_board_wizards');
    }
}
