# OnBoardWizard Module - Complete Implementation

## Overview 🎯
The OnBoardWizard module provides comprehensive onboarding tracking for school administrators, ensuring a smooth step-by-step setup process with progress monitoring and video guidance.

## Database Structure 📊

### Table: `on_board_wizards`
```sql
- id (primary key)
- administrator_id (links to admin_users table)
- enterprise_id (links to enterprises table)
- current_step (string, default: 'step1')

-- Progress Tracking (Yes/No fields)
- email_is_verified (default: 'No')
- school_details_added (default: 'No') 
- employees_added (default: 'No')
- classes_approved (default: 'No')
- subjects_added (default: 'No')
- students_added (default: 'No')
- help_videos_watched (default: 'No')
- completed_on_boarding (default: 'No')

-- Video Tracking
- current_video (nullable)
- videos_completed_progress (default: '0')

-- Advanced Features
- completed_steps (JSON array)
- step_data (JSON for temporary storage)
- last_activity_at (timestamp)
- onboarding_status ('in_progress', 'completed', 'paused')
- total_progress_percentage (0-100)
- notes (text, for admin notes)
- preferred_language (default: 'en')
- skip_help_videos (default: 'No')
- started_at (timestamp)
- completed_at (timestamp)
- created_at, updated_at
```

## Model Features 🔧

### Relationships
- **Administrator**: `belongsTo(User::class, 'administrator_id')`
- **Enterprise**: `belongsTo(Enterprise::class, 'enterprise_id')`
- **User Model**: `hasOne(OnBoardWizard::class, 'administrator_id')`
- **Enterprise Model**: `hasOne(OnBoardWizard::class, 'enterprise_id')`

### Key Methods

#### Progress Tracking
```php
// Mark a step as completed
$wizard->markStepCompleted('email_verification');

// Check if step is completed
$wizard->isStepCompleted('school_details');

// Get next step to complete
$nextStep = $wizard->getNextStep();

// Update overall progress percentage
$wizard->updateProgressPercentage();
```

#### Onboarding Management
```php
// Start onboarding process
$wizard->startOnboarding();

// Get comprehensive progress summary
$summary = $wizard->getProgressSummary();
```

#### Query Scopes
```php
// Get active onboarding processes
OnBoardWizard::inProgress()->get();

// Get completed onboarding processes
OnBoardWizard::completed()->get();
```

## Automatic Integration 🤖

### Enterprise Creation Hook
When a new school (Enterprise) is created, an OnBoardWizard is automatically created via the Enterprise model's `created` event:

```php
OnBoardWizard::create([
    'administrator_id' => $enterprise->administrator_id,
    'enterprise_id' => $enterprise->id,
    'current_step' => 'email_verification',
    'onboarding_status' => 'in_progress',
    'started_at' => now(),
    'last_activity_at' => now(),
    'preferred_language' => 'en',
    'total_progress_percentage' => 0,
]);
```

## Onboarding Steps Flow 📋

1. **Email Verification** (`email_is_verified`)
2. **School Details** (`school_details_added`)
3. **Add Employees** (`employees_added`)
4. **Setup Classes** (`classes_approved`)
5. **Add Subjects** (`subjects_added`)
6. **Add Students** (`students_added`)
7. **Help Videos** (`help_videos_watched`)

## Usage Examples 💡

### Basic Usage
```php
// Get user's onboarding wizard
$user = User::find(1);
$wizard = $user->onboardingWizard;

// Check progress
if ($wizard->isStepCompleted('school_details')) {
    echo "School details are completed!";
}

// Mark step complete and move forward
$wizard->markStepCompleted('employees_added');
$wizard->email_is_verified = 'Yes';
$wizard->save();
```

### Progress Monitoring
```php
// Get comprehensive progress
$progress = $wizard->getProgressSummary();
/*
Returns:
{
  "current_step": "employees",
  "progress_percentage": 42,
  "status": "in_progress", 
  "steps_completed": ["email_verification", "school_details"],
  "next_step": "employees",
  "started_at": "2025-09-17T09:00:00.000000Z",
  "last_activity": "2025-09-17T09:15:30.000000Z"
}
*/
```

### Enterprise Integration
```php
// Get enterprise onboarding status
$enterprise = Enterprise::find(1);
$wizard = $enterprise->onboardingWizard;

if ($wizard->completed_on_boarding === 'Yes') {
    echo "School setup is complete!";
} else {
    echo "Progress: " . $wizard->total_progress_percentage . "%";
}
```

## Features Highlights ✨

1. **Automatic Creation**: OnBoardWizard automatically created when school is registered
2. **Progress Tracking**: Real-time percentage calculation and step monitoring
3. **Video Integration**: Track help video progress and current video
4. **Flexible Step Data**: JSON storage for temporary step-specific information
5. **Activity Monitoring**: Last activity timestamps for engagement tracking
6. **Language Support**: Preferred language tracking for localization
7. **Skip Options**: Allow users to skip help videos if desired
8. **Completion Timestamps**: Track when onboarding started and completed
9. **Status Management**: Different onboarding states (in_progress, completed, paused)
10. **Error Handling**: Graceful error handling during creation

## Next Steps 🚀

The OnBoardWizard module is ready for:
- Integration with the existing onboarding flow
- Step-by-step UI implementation
- Video tracking functionality
- Progress dashboard creation
- Email verification integration
- Completion certification system

This foundation provides everything needed for a comprehensive, user-friendly onboarding experience! 🎉