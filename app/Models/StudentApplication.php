<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentApplication extends Model
{
    use HasFactory;
    
    protected $table = 'student_applications';
    
    protected $fillable = [
        'enterprise_id', 'user_id', 'application_number', 'session_token',
        'current_step', 'status', 'selected_enterprise_id', 
        'enterprise_selected_at', 'enterprise_confirmed',
        'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
        'nationality', 'religion', 'email', 'phone_number', 'phone_number_2',
        'home_address', 'district', 'city', 'village',
        'parent_name', 'parent_phone', 'parent_email', 'parent_relationship',
        'parent_address', 'previous_school', 'previous_class', 'year_completed',
        'applying_for_class', 'special_needs', 'data_confirmed_at',
        'submitted_at', 'uploaded_documents', 'documents_complete',
        'documents_submitted_at', 'step_data_backup', 'progress_percentage',
        'last_activity_at', 'reviewed_by', 'reviewed_at', 'admin_notes',
        'rejection_reason', 'ip_address', 'user_agent', 'started_at',
        'completed_at'
    ];
    
    protected $casts = [
        'uploaded_documents' => 'array',
        'step_data_backup' => 'array',
        'date_of_birth' => 'date',
        'enterprise_selected_at' => 'datetime',
        'data_confirmed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'documents_submitted_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'integer',
    ];
    
    protected $appends = ['full_name', 'status_label', 'step_label'];
    
    // === RELATIONSHIPS (No cascade, just queries) ===
    
    /**
     * Get the enterprise this application belongs to (system tracking)
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }
    
    /**
     * Get the school the student is applying to
     */
    public function selectedEnterprise()
    {
        return $this->belongsTo(Enterprise::class, 'selected_enterprise_id');
    }
    
    /**
     * Get the linked user account (after acceptance)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the admin who reviewed this application
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    
    // === BOOT METHODS ===
    
    public static function boot()
    {
        parent::boot();
        
        self::creating(function ($model) {
            // Generate unique application number
            if (empty($model->application_number)) {
                $model->application_number = self::generateApplicationNumber(
                    $model->selected_enterprise_id
                );
            }
            
            // Set timestamps
            if (empty($model->started_at)) {
                $model->started_at = now();
            }
            $model->last_activity_at = now();
            
            // Capture metadata
            if (empty($model->ip_address)) {
                $model->ip_address = request()->ip();
            }
            if (empty($model->user_agent)) {
                $model->user_agent = request()->userAgent();
            }
        });
        
        self::updating(function ($model) {
            $model->last_activity_at = now();
            
            // Update progress percentage based on current step
            $model->progress_percentage = $model->calculateProgress();
        });
    }
    
    // === HELPER METHODS ===
    
    /**
     * Generate unique application number
     * Format: APP-{YYYY}-{sequence}
     * Example: APP-2025-000001
     * Note: Enterprise info added later when school is selected
     */
    public static function generateApplicationNumber($enterpriseId = null)
    {
        // Use database transaction and locking to prevent race conditions
        return \DB::transaction(function () {
            $year = date('Y');
            $prefix = 'APP-' . $year . '-';
            
            // Get last application number for this year with row locking
            $lastApp = self::where('application_number', 'LIKE', $prefix . '%')
                           ->orderBy('id', 'desc')
                           ->lockForUpdate()
                           ->first();
            
            if ($lastApp) {
                $lastNumber = (int) substr($lastApp->application_number, -6);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        });
    }
    
    /**
     * Generate unique session token
     */
    public static function generateSessionToken()
    {
        return 'SAP-' . Str::random(40) . '-' . time();
    }
    
    /**
     * Calculate progress percentage based on completed steps
     */
    public function calculateProgress()
    {
        $steps = [
            'landing' => 0,
            'school_selection' => 20,
            'bio_data' => 40,
            'confirmation' => 60,
            'documents' => 80,
            'submitted' => 100,
            'completed' => 100
        ];
        
        return $steps[$this->current_step] ?? 0;
    }
    
    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        $names = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        
        return !empty($names) ? implode(' ', $names) : 'N/A';
    }
    
    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled'
        ];
        
        return $labels[$this->status] ?? 'Unknown';
    }
    
    /**
     * Get human-readable step label
     */
    public function getStepLabelAttribute()
    {
        $labels = [
            'landing' => 'Getting Started',
            'school_selection' => 'School Selection',
            'bio_data' => 'Personal Information',
            'confirmation' => 'Review & Confirm',
            'documents' => 'Document Upload',
            'submitted' => 'Submitted',
            'completed' => 'Completed'
        ];
        
        return $labels[$this->current_step] ?? 'Unknown';
    }
    
    /**
     * Check if application can be edited
     */
    public function canEdit()
    {
        return in_array($this->status, ['draft']);
    }
    
    /**
     * Check if application can be reviewed
     */
    public function canReview()
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }
    
    /**
     * Check if documents are required for this application
     */
    public function requiresDocuments()
    {
        if (!$this->selectedEnterprise) {
            return false;
        }
        
        $docs = $this->selectedEnterprise->required_application_documents;
        return !empty($docs) && is_array($docs) && count($docs) > 0;
    }
    
    /**
     * Get required documents from selected enterprise
     */
    public function getRequiredDocuments()
    {
        if (!$this->selectedEnterprise) {
            return [];
        }
        
        $docs = $this->selectedEnterprise->required_application_documents;
        
        if (is_string($docs)) {
            $docs = json_decode($docs, true);
        }
        
        return is_array($docs) ? $docs : [];
    }
    
    /**
     * Check if all required documents are uploaded
     */
    public function hasAllRequiredDocuments()
    {
        $required = collect($this->getRequiredDocuments())
                   ->where('required', true)
                   ->pluck('id')
                   ->toArray();
        
        if (empty($required)) {
            return true; // No required documents
        }
        
        $uploaded = collect($this->uploaded_documents ?? [])
                   ->pluck('document_id')
                   ->toArray();
        
        return count(array_diff($required, $uploaded)) === 0;
    }
    
    /**
     * Save step data to backup
     */
    public function backupStepData($stepName, $data)
    {
        $backup = $this->step_data_backup ?? [];
        $backup[$stepName] = $data;
        $backup[$stepName]['saved_at'] = now()->toDateTimeString();
        
        $this->step_data_backup = $backup;
        $this->save();
        
        return $this;
    }
    
    /**
     * Move to next step
     */
    public function moveToNextStep($nextStep)
    {
        $validSteps = [
            'landing', 'school_selection', 'bio_data', 'confirmation', 
            'documents', 'submitted', 'completed'
        ];
        
        if (in_array($nextStep, $validSteps)) {
            $this->current_step = $nextStep;
            $this->progress_percentage = $this->calculateProgress();
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Submit the application
     */
    public function submit()
    {
        $this->status = 'submitted';
        $this->current_step = 'submitted';
        $this->submitted_at = now();
        $this->progress_percentage = 100;
        $this->save();
        
        return $this;
    }
    
    /**
     * Create user account from application (called on acceptance)
     */
    public function createUserAccount()
    {
        if ($this->user_id) {
            return User::find($this->user_id);
        }
        
        DB::beginTransaction();
        try {
            $user = new User();
            $user->enterprise_id = $this->selected_enterprise_id;
            $user->first_name = $this->first_name;
            $user->last_name = $this->last_name;
            $user->name = $this->full_name;
            $user->email = $this->email;
            $user->username = $this->email;
            $user->phone_number = $this->phone_number;
            $user->phone_number_1 = $this->phone_number;
            $user->phone_number_2 = $this->phone_number_2;
            $user->date_of_birth = $this->date_of_birth;
            $user->sex = $this->gender;
            $user->religion = $this->religion;
            $user->nationality = $this->nationality;
            $user->home_address = $this->home_address;
            $user->current_address = $this->home_address;
            $user->user_type = 'student';
            $user->status = 3; // Applicant status
            $user->password = bcrypt(Str::random(12)); // Random password
            $user->save();
            
            // Link application to user
            $this->user_id = $user->id;
            $this->status = 'accepted';
            $this->completed_at = now();
            $this->save();
            
            DB::commit();
            return $user;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Accept the application
     */
    public function accept($reviewerId = null, $notes = null)
    {
        $this->status = 'accepted';
        $this->reviewed_by = $reviewerId ?? auth()->id();
        $this->reviewed_at = now();
        $this->completed_at = now();
        
        if ($notes) {
            $this->admin_notes = $notes;
        }
        
        $this->save();
        
        // Create user account
        if (!$this->user_id) {
            $this->createUserAccount();
        }
        
        return $this;
    }
    
    /**
     * Reject the application
     */
    public function reject($reason, $reviewerId = null, $notes = null)
    {
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->reviewed_by = $reviewerId ?? auth()->id();
        $this->reviewed_at = now();
        
        if ($notes) {
            $this->admin_notes = $notes;
        }
        
        $this->save();
        
        return $this;
    }
    
    // === SCOPES ===
    
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
    
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }
    
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }
    
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }
    
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
    
    public function scopeForEnterprise($query, $enterpriseId)
    {
        return $query->where('selected_enterprise_id', $enterpriseId);
    }
    
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'under_review']);
    }
}
