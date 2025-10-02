<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_applications', function (Blueprint $table) {
            $table->id();
            
            // === RELATIONSHIPS (All Nullable, No Cascade) ===
            $table->bigInteger('enterprise_id')->unsigned()->nullable()
                  ->comment('System enterprise ID - for multi-tenant tracking');
            $table->bigInteger('user_id')->unsigned()->nullable()
                  ->comment('Linked user account after acceptance (admin_users.id)');
            
            // === UNIQUE IDENTIFIERS ===
            $table->string('application_number', 50)->unique()->nullable()
                  ->comment('Auto-generated: APP-ENT123-202510-001');
            $table->string('session_token', 100)->unique()->nullable()
                  ->comment('Anonymous session tracking before submission');
            
            // === APPLICATION FLOW ===
            $table->enum('current_step', [
                'landing',
                'school_selection', 
                'bio_data',
                'confirmation',
                'documents',
                'submitted',
                'completed'
            ])->default('landing')->nullable()
              ->comment('Current step in application process');
            
            $table->enum('status', [
                'draft',           // In progress, not submitted
                'submitted',       // Submitted, awaiting review
                'under_review',    // Being reviewed by admin
                'accepted',        // Accepted, user account created
                'rejected',        // Rejected by admin
                'cancelled'        // Cancelled by applicant
            ])->default('draft')->nullable()
              ->comment('Application status');
            
            // === STEP 1: SCHOOL SELECTION ===
            $table->bigInteger('selected_enterprise_id')->unsigned()->nullable()
                  ->comment('School they want to apply to');
            $table->timestamp('enterprise_selected_at')->nullable();
            $table->enum('enterprise_confirmed', ['Yes', 'No'])->default('No')->nullable();
            
            // === STEP 2: BIO DATA - Personal Information ===
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('date_of_birth', 20)->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('nationality', 100)->nullable()->default('Ugandan');
            $table->string('religion', 100)->nullable();
            
            // === STEP 2: BIO DATA - Contact Information ===
            $table->string('email', 150)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('phone_number_2', 20)->nullable();
            
            // === STEP 2: BIO DATA - Address Information ===
            $table->text('home_address')->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('village', 100)->nullable();
            
            // === STEP 2: BIO DATA - Guardian/Parent Information ===
            $table->string('parent_name', 150)->nullable();
            $table->string('parent_phone', 20)->nullable();
            $table->string('parent_email', 150)->nullable();
            $table->string('parent_relationship', 50)->nullable()
                  ->comment('Father, Mother, Guardian, etc.');
            $table->text('parent_address')->nullable();
            
            // === STEP 2: BIO DATA - Previous School Information ===
            $table->string('previous_school', 200)->nullable();
            $table->string('previous_class', 100)->nullable();
            $table->year('year_completed')->nullable();
            
            // === STEP 2: BIO DATA - Application Details ===
            $table->string('applying_for_class', 100)->nullable()
                  ->comment('Class/grade they want to join');
            $table->text('special_needs')->nullable()
                  ->comment('Any special needs or requirements');
            
            // === STEP 3: CONFIRMATION ===
            $table->timestamp('data_confirmed_at')->nullable()
                  ->comment('When applicant confirmed their data');
            $table->timestamp('submitted_at')->nullable()
                  ->comment('When application was submitted');
            
            // === STEP 4: DOCUMENTS ===
            $table->json('uploaded_documents')->nullable()
                  ->comment('Array of uploaded files with paths and metadata');
            $table->enum('documents_complete', ['Yes', 'No'])->default('No')->nullable();
            $table->timestamp('documents_submitted_at')->nullable();
            
            // === SESSION BACKUP (For Recovery) ===
            $table->json('step_data_backup')->nullable()
                  ->comment('Full backup of all step data for recovery');
            
            // === PROGRESS TRACKING ===
            $table->integer('progress_percentage')->default(0)->nullable()
                  ->comment('0-100 based on completed steps');
            $table->timestamp('last_activity_at')->nullable()
                  ->comment('Last time applicant was active');
            
            // === ADMIN REVIEW ===
            $table->bigInteger('reviewed_by')->unsigned()->nullable()
                  ->comment('Admin user who reviewed (admin_users.id)');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable()
                  ->comment('Internal notes from admin');
            $table->text('rejection_reason')->nullable()
                  ->comment('Reason for rejection (shown to applicant)');
            
            // === METADATA ===
            $table->string('ip_address', 50)->nullable()
                  ->comment('IP address of applicant');
            $table->text('user_agent')->nullable()
                  ->comment('Browser user agent');
            $table->timestamp('started_at')->nullable()
                  ->comment('When application was started');
            $table->timestamp('completed_at')->nullable()
                  ->comment('When application was fully completed');
            
            // === TIMESTAMPS ===
            $table->timestamps();
            
            // === INDEXES (No Foreign Keys, Just Indexes for Performance) ===
            $table->index('enterprise_id', 'idx_enterprise');
            $table->index('user_id', 'idx_user');
            $table->index('selected_enterprise_id', 'idx_selected_enterprise');
            $table->index('session_token', 'idx_session_token');
            $table->index('application_number', 'idx_application_number');
            $table->index('status', 'idx_status');
            $table->index('email', 'idx_email');
            $table->index(['enterprise_id', 'status'], 'idx_enterprise_status');
            $table->index(['selected_enterprise_id', 'status'], 'idx_selected_ent_status');
            $table->index('current_step', 'idx_current_step');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_applications');
    }
}
