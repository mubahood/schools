<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnBoardWizard extends Model
{
    use HasFactory;

    protected $fillable = [
        'administrator_id',
        'enterprise_id',
        'current_step',
        'email_is_verified',
        'school_details_added',
        'employees_added',
        'classes_approved',
        'subjects_added',
        'students_added',
        'help_videos_watched',
        'completed_on_boarding',
        'current_video',
        'videos_completed_progress',
        'completed_steps',
        'step_data',
        'last_activity_at',
        'onboarding_status',
        'total_progress_percentage',
        'notes',
        'preferred_language',
        'skip_help_videos',
        'started_at',
        'completed_at',
        'verification_token',
        'verification_sent_at',
        'email_verified_at',
    ];

    protected $casts = [
        'completed_steps' => 'array',
        'step_data' => 'array',
        'last_activity_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'verification_sent_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'total_progress_percentage' => 'integer',
        'videos_completed_progress' => 'integer',
    ];

    // === RELATIONSHIPS ===

    /**
     * Get the administrator that owns this onboarding wizard
     */
    public function administrator()
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    /**
     * Get the enterprise associated with this onboarding wizard
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    // === HELPER METHODS ===

    /**
     * Mark a step as completed
     */
    public function markStepCompleted($stepName)
    {
        $completedSteps = $this->completed_steps ?: [];
        
        if (!in_array($stepName, $completedSteps)) {
            $completedSteps[] = $stepName;
            $this->completed_steps = $completedSteps;
        }

        // Update progress percentage
        $this->updateProgressPercentage();
        
        // Update last activity
        $this->last_activity_at = now();
        
        $this->save();
        
        return $this;
    }

    /**
     * Check if a step is completed
     */
    public function isStepCompleted($stepName)
    {
        $completedSteps = $this->completed_steps ?: [];
        return in_array($stepName, $completedSteps);
    }

    /**
     * Get next step based on current progress
     */
    public function getNextStep()
    {
        $steps = [
            'email_verification' => 'Email Verification',
            'school_details' => 'School Details',
            'employees' => 'Add Employees', 
            'classes' => 'Setup Classes',
            'subjects' => 'Add Subjects',
            'students' => 'Add Students',
            'help_videos' => 'Help Videos',
        ];

        foreach ($steps as $stepKey => $stepName) {
            $fieldName = $stepKey === 'email_verification' ? 'email_is_verified' : 
                        ($stepKey === 'school_details' ? 'school_details_added' :
                        ($stepKey === 'employees' ? 'employees_added' :
                        ($stepKey === 'classes' ? 'classes_approved' :
                        ($stepKey === 'subjects' ? 'subjects_added' :
                        ($stepKey === 'students' ? 'students_added' :
                        'help_videos_watched')))));

            if ($this->$fieldName !== 'Yes') {
                return $stepKey;
            }
        }

        return 'completed';
    }

    /**
     * Update overall progress percentage
     */
    public function updateProgressPercentage()
    {
        $totalSteps = 7; // email, school_details, employees, classes, subjects, students, videos
        $completedCount = 0;

        $progressFields = [
            'email_is_verified',
            'school_details_added', 
            'employees_added',
            'classes_approved',
            'subjects_added',
            'students_added',
            'help_videos_watched',
        ];

        foreach ($progressFields as $field) {
            if ($this->$field === 'Yes') {
                $completedCount++;
            }
        }

        $this->total_progress_percentage = round(($completedCount / $totalSteps) * 100);
        
        // Check if fully completed
        if ($this->total_progress_percentage >= 100) {
            $this->completed_on_boarding = 'Yes';
            $this->onboarding_status = 'completed';
            $this->completed_at = now();
        }

        return $this;
    }

    /**
     * Start onboarding process
     */
    public function startOnboarding()
    {
        $this->started_at = now();
        $this->last_activity_at = now();
        $this->onboarding_status = 'in_progress';
        $this->current_step = 'email_verification';
        $this->save();
        
        return $this;
    }

    /**
     * Get onboarding progress summary
     */
    public function getProgressSummary()
    {
        return [
            'current_step' => $this->current_step,
            'progress_percentage' => $this->total_progress_percentage,
            'status' => $this->onboarding_status,
            'steps_completed' => $this->completed_steps ?: [],
            'next_step' => $this->getNextStep(),
            'started_at' => $this->started_at,
            'last_activity' => $this->last_activity_at,
        ];
    }

    /**
     * Scope for active onboarding processes
     */
    public function scopeInProgress($query)
    {
        return $query->where('onboarding_status', 'in_progress');
    }

    /**
     * Scope for completed onboarding processes
     */
    public function scopeCompleted($query)
    {
        return $query->where('onboarding_status', 'completed');
    }
}
