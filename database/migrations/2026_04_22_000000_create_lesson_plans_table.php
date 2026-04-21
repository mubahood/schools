<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('enterprise_id')->index();
            $table->unsignedInteger('term_id')->nullable()->index();
            $table->unsignedInteger('academic_class_id')->nullable()->index();
            $table->unsignedInteger('subject_id')->nullable()->index();
            $table->unsignedInteger('teacher_id')->nullable()->index();

            $table->string('template_type', 30)->default('upper')->index();
            $table->date('plan_date')->nullable()->index();
            $table->string('time_text', 120)->nullable();
            $table->unsignedInteger('no_of_pupils')->nullable();

            $table->string('theme', 255)->nullable();
            $table->string('topic', 255)->nullable();
            $table->string('sub_topic', 255)->nullable();
            $table->string('sub_theme', 255)->nullable();
            $table->string('aspect', 255)->nullable();
            $table->string('language_skill', 255)->nullable();
            $table->string('learning_area', 255)->nullable();
            $table->text('learning_outcome')->nullable();

            $table->text('subject_competences')->nullable();
            $table->text('language_competences')->nullable();
            $table->text('competences')->nullable();
            $table->text('methods_techniques')->nullable();
            $table->text('content')->nullable();
            $table->text('skills_values')->nullable();
            $table->text('developmental_activities')->nullable();
            $table->text('teaching_activities')->nullable();
            $table->text('learning_aids')->nullable();
            $table->text('references')->nullable();

            $table->json('lesson_procedure')->nullable();

            $table->text('self_strengths')->nullable();
            $table->text('self_areas_improvement')->nullable();
            $table->text('self_strategies')->nullable();

            $table->string('status', 20)->default('Draft')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};
