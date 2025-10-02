<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\StudentApplication;

class ApplicationStep
{
    /**
     * Handle an incoming request.
     * Validates that the required previous step is completed before accessing current step
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $requiredStep  The step that must be completed to access the next step
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $requiredStep)
    {
        // Get application from request (set by ApplicationSession middleware)
        $application = $request->attributes->get('application');
        
        if (!$application) {
            return redirect()
                ->route('apply.landing')
                ->with('error', 'Application session not found.');
        }
        
        // Define step order and requirements
        $stepOrder = [
            'landing' => 0,
            'school_selection' => 1,
            'bio_data' => 2,
            'confirmation' => 3,
            'documents' => 4,
            'submitted' => 5
        ];
        
        // Step requirements - what must be completed to access each step
        $stepRequirements = [
            'bio_data' => function($app) {
                // Must have selected a school
                return !empty($app->selected_enterprise_id) && 
                       $app->enterprise_confirmed === 'Yes';
            },
            'confirmation' => function($app) {
                // Must have completed bio data
                return !empty($app->first_name) && 
                       !empty($app->last_name) && 
                       !empty($app->email) &&
                       !empty($app->phone_number) &&
                       !empty($app->applying_for_class);
            },
            'documents' => function($app) {
                // Must have confirmed data
                return !empty($app->data_confirmed_at);
            }
        ];
        
        // Check if the required step is in the requirements
        if (isset($stepRequirements[$requiredStep])) {
            $requirementCheck = $stepRequirements[$requiredStep];
            
            if (!$requirementCheck($application)) {
                // Requirement not met, redirect to appropriate step
                $redirectStep = $this->getAppropriateStep($application);
                
                return redirect()
                    ->route("apply.{$redirectStep}")
                    ->with('warning', 'Please complete the previous step first.');
            }
        }
        
        // Get current step position
        $currentStepPosition = $stepOrder[$application->current_step] ?? 0;
        $requiredStepPosition = $stepOrder[$requiredStep] ?? 0;
        
        // If trying to access a step before completing required step
        if ($currentStepPosition < $requiredStepPosition) {
            $redirectStep = $this->getAppropriateStep($application);
            
            return redirect()
                ->route("apply.{$redirectStep}")
                ->with('warning', 'Please complete the steps in order.');
        }
        
        return $next($request);
    }
    
    /**
     * Determine the appropriate step to redirect to based on application state
     */
    private function getAppropriateStep($application)
    {
        // Check school selection
        if (empty($application->selected_enterprise_id) || $application->enterprise_confirmed !== 'Yes') {
            return 'school-selection';
        }
        
        // Check bio data
        if (empty($application->first_name) || empty($application->email)) {
            return 'bio-data';
        }
        
        // Check confirmation
        if (empty($application->data_confirmed_at)) {
            return 'confirmation';
        }
        
        // Check if documents are required and not uploaded
        if ($application->requiresDocuments() && $application->documents_complete !== 'Yes') {
            return 'documents';
        }
        
        // Default to confirmation if everything else is done
        return 'confirmation';
    }
}
