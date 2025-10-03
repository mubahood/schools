<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\StudentApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentApplicationController extends Controller
{
    /**
     * Display the landing/introduction page
     */
    public function landing()
    {
        // Get a sample enterprise for branding (first one that accepts applications)
        $enterprise = Enterprise::where('accepts_online_applications', 'Yes')->first();
        
        if (!$enterprise) {
            // Try to get any enterprise for branding
            $enterprise = Enterprise::first();
        }
        
        if (!$enterprise) {
            // If no enterprise found at all, show error message
            return view('student-application.landing', [
                'schoolName' => config('app.name'),
                'schoolMotto' => '',
                'schoolLogo' => null,
                'schoolPrimary' => '#3c8dbc',
                'schoolSecondary' => '#f39c12',
                'schoolPhone' => null,
                'schoolEmail' => null,
                'schoolAddress' => null,
                'acceptsApplications' => false,
                'customMessage' => 'No schools are currently set up in the system. Please contact the administrator.',
                'applicationInstructions' => null,
                'requiredDocuments' => [],
                'applicationFee' => 0,
                'applicationDeadline' => null,
            ]);
        }
        
        // Check if this enterprise accepts applications
        $acceptsApplications = false;
        try {
            $acceptsApplications = $enterprise->acceptsApplications();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error checking acceptsApplications: ' . $e->getMessage());
        }
        
        // Get required documents
        $requiredDocuments = [];
        try {
            if (!empty($enterprise->required_application_documents)) {
                $docs = $enterprise->required_application_documents;
                if (is_string($docs)) {
                    $docs = json_decode($docs, true);
                }
                $requiredDocuments = is_array($docs) ? $docs : [];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error parsing required documents: ' . $e->getMessage());
        }
        
        return view('student-application.landing', [
            'schoolName' => $enterprise->name ?? config('app.name'),
            'schoolMotto' => $enterprise->motto ?? '',
            'schoolLogo' => $enterprise->logo ? asset('storage/' . $enterprise->logo) : null,
            'schoolPrimary' => $enterprise->color ?? '#3c8dbc',
            'schoolSecondary' => $enterprise->sec_color ?? '#f39c12',
            'schoolPhone' => $enterprise->phone_number ?? null,
            'schoolEmail' => $enterprise->email ?? null,
            'schoolAddress' => $enterprise->address ?? null,
            'acceptsApplications' => $acceptsApplications,
            'customMessage' => $enterprise->application_status_message ?? null,
            'applicationInstructions' => $enterprise->application_instructions ?? null,
            'requiredDocuments' => $requiredDocuments,
            'applicationFee' => $enterprise->application_fee ?? 0,
            'applicationDeadline' => $enterprise->application_deadline ? \Carbon\Carbon::parse($enterprise->application_deadline) : null,
        ]);
    }
    
    /**
     * Start a new application - create session and redirect to school selection
     */
    public function start(Request $request)
    {
        // Create new application record
        $application = new StudentApplication();
        $application->session_token = StudentApplication::generateSessionToken();
        $application->current_step = 'school_selection';
        $application->status = 'draft';
        $application->progress_percentage = 0;
        $application->ip_address = $request->ip();
        $application->user_agent = $request->userAgent();
        $application->started_at = now();
        $application->last_activity_at = now();
        $application->save();
        
        // Store session token
        session(['student_application_token' => $application->session_token]);
        session(['student_application' => $application->toArray()]);
        
        return redirect()
            ->route('apply.school-selection')
            ->with('success', 'Welcome! Let\'s start your application.');
    }
    
    /**
     * Display school selection page (Step 1)
     */
    public function schoolSelection(Request $request)
    {
        $application = $request->attributes->get('application');
        
        // Get all enterprises that accept applications
        $schools = Enterprise::where('accepts_online_applications', 'Yes')
                            ->get()
                            ->filter(function($school) {
                                return $school->acceptsApplications();
                            });
        
        return view('student-application.school-selection', [
            'application' => $application,
            'schools' => $schools
        ]);
    }
    
    /**
     * Save school selection (AJAX)
     */
    public function saveSchoolSelection(Request $request)
    {
        $application = $request->attributes->get('application');
        
        $validator = Validator::make($request->all(), [
            'enterprise_id' => 'required|exists:enterprises,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a valid school.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $enterprise = Enterprise::find($request->enterprise_id);
        
        // Verify school accepts applications
        if (!$enterprise->acceptsApplications()) {
            return response()->json([
                'success' => false,
                'message' => 'This school is not currently accepting applications.'
            ], 422);
        }
        
        // Update application
        $application->selected_enterprise_id = $request->enterprise_id;
        $application->enterprise_selected_at = now();
        $application->save();
        
        // Backup to session
        $application->backupStepData('school_selection', [
            'enterprise_id' => $request->enterprise_id,
            'enterprise_name' => $enterprise->name
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'School selected successfully!',
            'school' => [
                'id' => $enterprise->id,
                'name' => $enterprise->name,
                'logo' => $enterprise->logo,
                'color' => $enterprise->color,
                'sec_color' => $enterprise->sec_color
            ]
        ]);
    }
    
    /**
     * Confirm school selection and move to next step
     */
    public function confirmSchool(Request $request)
    {
        $application = $request->attributes->get('application');
        
        if (empty($application->selected_enterprise_id)) {
            return redirect()
                ->route('apply.school-selection')
                ->with('error', 'Please select a school first.');
        }
        
        // Confirm selection
        $application->enterprise_confirmed = 'Yes';
        $application->moveToNextStep('bio_data');
        
        return redirect()
            ->route('apply.bio-data')
            ->with('success', 'Great! Now let\'s get your information.');
    }
    
    /**
     * Display bio data form (Step 2)
     */
    public function bioDataForm(Request $request)
    {
        $application = $request->attributes->get('application');
        $school = $application->selectedEnterprise;
        
        return view('student-application.bio-data-form', [
            'application' => $application,
            'school' => $school
        ]);
    }
    
    /**
     * Save bio data
     */
    public function saveBioData(Request $request)
    {
        $application = $request->attributes->get('application');
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,Male,Female',
            'nationality' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'email' => 'required|email|max:150',
            'phone_number' => 'required|string|max:20',
            'phone_number_2' => 'nullable|string|max:20',
            'home_address' => 'required|string',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'parent_name' => 'required|string|max:150',
            'parent_phone' => 'required|string|max:20',
            'parent_email' => 'nullable|email|max:150',
            'parent_relationship' => 'required|string|max:50',
            'parent_address' => 'nullable|string',
            'previous_school' => 'nullable|string|max:200',
            'previous_class' => 'nullable|string|max:100',
            'year_completed' => 'nullable|integer|min:1900|max:' . date('Y'),
            'applying_for_class' => 'required|string|max:100',
            'special_needs' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx'
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }
        
        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            
            // Limit to 20 files
            if (count($files) > 20) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Maximum of 20 attachments allowed.');
            }
            
            // Create directory for this application if not exists
            $applicationDir = 'applications/' . ($application->application_number ?: 'temp_' . $application->id);
            
            foreach ($files as $file) {
                try {
                    // Generate unique filename
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $storedName = time() . '_' . uniqid() . '.' . $extension;
                    
                    // Store file
                    $path = $file->storeAs($applicationDir, $storedName, 'public');
                    
                    // Save file metadata
                    $attachments[] = [
                        'name' => $originalName,
                        'stored_name' => $storedName,
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('File upload error: ' . $e->getMessage());
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Error uploading file: ' . $originalName);
                }
            }
            
            // Merge with existing attachments if any
            $existingAttachments = $application->attachments ?? [];
            $attachments = array_merge($existingAttachments, $attachments);
        }
        
        // Save attachments to application
        if (!empty($attachments)) {
            $application->attachments = $attachments;
        }
        
        // Check if email already exists for this school
        $existingApplication = StudentApplication::where('email', $request->email)
                                                 ->where('selected_enterprise_id', $application->selected_enterprise_id)
                                                 ->where('id', '!=', $application->id)
                                                 ->whereIn('status', ['submitted', 'under_review', 'accepted'])
                                                 ->first();
        
        if ($existingApplication) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An application with this email already exists for this school.');
        }
        
        // Update application with all bio data
        $application->fill($request->only([
            'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
            'nationality', 'religion', 'email', 'phone_number', 'phone_number_2',
            'home_address', 'district', 'city', 'village',
            'parent_name', 'parent_phone', 'parent_email', 'parent_relationship',
            'parent_address', 'previous_school', 'previous_class', 'year_completed',
            'applying_for_class', 'special_needs'
        ]));
        
        $application->moveToNextStep('confirmation');
        
        // Backup to session
        $application->backupStepData('bio_data', $request->all());
        
        return redirect()
            ->route('apply.confirmation')
            ->with('success', 'Information saved! Please review and confirm.');
    }
    
    /**
     * Display confirmation page (Step 3)
     */
    public function confirmationForm(Request $request)
    {
        $application = $request->attributes->get('application');
        $school = $application->selectedEnterprise;
        
        return view('student-application.confirmation', [
            'application' => $application,
            'school' => $school
        ]);
    }
    
    /**
     * Submit the application
     */
    public function submitApplication(Request $request)
    {
        $application = $request->attributes->get('application');
        
        $validator = Validator::make($request->all(), [
            'confirm' => 'required|accepted'
        ], [
            'confirm.accepted' => 'You must confirm that all information is correct.'
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->with('error', 'Please confirm your information before submitting.');
        }
        
        // Mark data as confirmed
        $application->data_confirmed_at = now();
        
        // Check if documents are required
        if ($application->requiresDocuments()) {
            // Move to documents step
            $application->moveToNextStep('documents');
            
            return redirect()
                ->route('apply.documents')
                ->with('success', 'Application confirmed! Please upload required documents.');
        } else {
            // No documents required, submit directly
            $application->submit();
            
            // Clear session
            session()->forget('student_application_token');
            session()->forget('student_application');
            
            return redirect()
                ->route('apply.success', ['applicationNumber' => $application->application_number])
                ->with('success', 'Application submitted successfully!');
        }
    }
    
    /**
     * Display documents upload page (Step 4)
     */
    public function documentsForm(Request $request)
    {
        $application = $request->attributes->get('application');
        $school = $application->selectedEnterprise;
        $requiredDocuments = $application->getRequiredDocuments();
        $uploadedDocuments = $application->uploaded_documents ?? [];
        
        return view('student-application.documents', [
            'application' => $application,
            'school' => $school,
            'requiredDocuments' => $requiredDocuments,
            'uploadedDocuments' => $uploadedDocuments
        ]);
    }
    
    /**
     * Upload a document (AJAX)
     */
    public function uploadDocument(Request $request)
    {
        $application = $request->attributes->get('application');
        
        $validator = Validator::make($request->all(), [
            'document_id' => 'required|integer',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png'
        ], [
            'file.max' => 'File size must not exceed 5MB.',
            'file.mimes' => 'Only PDF, JPG, JPEG, and PNG files are allowed.'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Get the file
            $file = $request->file('file');
            $documentId = $request->document_id;
            
            // Verify document ID is valid
            $requiredDocs = $application->getRequiredDocuments();
            $docInfo = collect($requiredDocs)->firstWhere('id', $documentId);
            
            if (!$docInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document type.'
                ], 422);
            }
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = Str::slug($docInfo['name']) . '_' . time() . '_' . Str::random(8) . '.' . $extension;
            
            // Store file
            $path = $file->storeAs(
                'applications/' . $application->application_number,
                $filename,
                'public'
            );
            
            // Add to uploaded documents array
            $uploadedDocuments = $application->uploaded_documents ?? [];
            
            // Remove existing upload for this document type
            $uploadedDocuments = array_values(array_filter($uploadedDocuments, function($doc) use ($documentId) {
                return $doc['document_id'] != $documentId;
            }));
            
            // Add new upload
            $uploadedDocuments[] = [
                'document_id' => $documentId,
                'document_name' => $docInfo['name'],
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $filename,
                'file_path' => $path,
                'file_size_bytes' => $file->getSize(),
                'file_size_formatted' => $this->formatBytes($file->getSize()),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()->toDateTimeString()
            ];
            
            $application->uploaded_documents = $uploadedDocuments;
            $application->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully!',
                'document' => end($uploadedDocuments)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete an uploaded document
     */
    public function deleteDocument(Request $request, $documentId)
    {
        $application = $request->attributes->get('application');
        
        $uploadedDocuments = $application->uploaded_documents ?? [];
        
        // Find and remove the document
        $documentToDelete = null;
        $newDocuments = [];
        
        foreach ($uploadedDocuments as $doc) {
            if ($doc['document_id'] == $documentId) {
                $documentToDelete = $doc;
            } else {
                $newDocuments[] = $doc;
            }
        }
        
        if (!$documentToDelete) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.'
            ], 404);
        }
        
        try {
            // Delete file from storage
            if (isset($documentToDelete['file_path'])) {
                Storage::disk('public')->delete($documentToDelete['file_path']);
            }
            
            // Update application
            $application->uploaded_documents = $newDocuments;
            $application->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Complete documents step and submit application
     */
    public function completeDocuments(Request $request)
    {
        $application = $request->attributes->get('application');
        
        // Check if all required documents are uploaded
        if (!$application->hasAllRequiredDocuments()) {
            return redirect()
                ->back()
                ->with('error', 'Please upload all required documents before submitting.');
        }
        
        // Mark documents as complete
        $application->documents_complete = 'Yes';
        $application->documents_submitted_at = now();
        
        // Submit the application
        $application->submit();
        
        // Clear session
        session()->forget('student_application_token');
        session()->forget('student_application');
        
        return redirect()
            ->route('apply.success', ['applicationNumber' => $application->application_number])
            ->with('success', 'Application and documents submitted successfully!');
    }
    
    /**
     * Display success page
     */
    public function success($applicationNumber)
    {
        $application = StudentApplication::where('application_number', $applicationNumber)
                                        ->first();
        
        if (!$application) {
            return redirect()
                ->route('apply.landing')
                ->with('error', 'Application not found.');
        }
        
        $school = $application->selectedEnterprise;
        
        return view('student-application.success', [
            'application' => $application,
            'school' => $school
        ]);
    }
    
    /**
     * Display status check form (handles both GET and POST)
     */
    public function statusForm(Request $request)
    {
        // If POST request with search data, check status
        if ($request->isMethod('post') && $request->has('search')) {
            return $this->checkStatus($request);
        }
        
        // Otherwise show the form
        return view('student-application.status-check');
    }
    
    /**
     * Check application status
     */
    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please enter an application number or email address.');
        }
        
        $search = $request->search;
        
        // Check if search is an email or application number
        $application = null;
        
        if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
            // Search by email
            $application = StudentApplication::where('email', $search)
                                            ->whereNotNull('submitted_at')
                                            ->orderBy('id', 'desc')
                                            ->first();
        } else {
            // Search by application number
            $application = StudentApplication::where('application_number', $search)
                                            ->first();
        }
        
        if (!$application) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Application not found. Please check your application number or email address.');
        }
        
        $school = $application->selectedEnterprise;
        
        return view('student-application.status-check', [
            'application' => $application,
            'school' => $school,
            'showStatus' => true
        ]);
    }
    
    /**
     * Save session data (AJAX auto-save)
     */
    public function saveSession(Request $request)
    {
        $application = $request->attributes->get('application');
        
        // Save any temporary data to step_data_backup
        if ($request->has('step_data')) {
            $stepName = $request->input('step_name', 'auto_save');
            $stepData = $request->input('step_data', []);
            
            $application->backupStepData($stepName, $stepData);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Data saved successfully.',
            'timestamp' => now()->toDateTimeString()
        ]);
    }
    
    /**
     * Session heartbeat (AJAX keep-alive)
     */
    public function sessionHeartbeat(Request $request)
    {
        $application = $request->attributes->get('application');
        
        // Update last activity
        $application->last_activity_at = now();
        $application->save();
        
        return response()->json([
            'success' => true,
            'timestamp' => now()->toDateTimeString(),
            'session_active' => true
        ]);
    }
    
    /**
     * Resume an application
     */
    public function resume($sessionToken)
    {
        $application = StudentApplication::where('session_token', $sessionToken)
                                        ->where('status', 'draft')
                                        ->first();
        
        if (!$application) {
            return redirect()
                ->route('apply.landing')
                ->with('error', 'Application session not found or has expired.');
        }
        
        // Restore session
        session(['student_application_token' => $application->session_token]);
        session(['student_application' => $application->toArray()]);
        
        // Redirect to appropriate step
        $step = $application->current_step;
        
        $stepRoutes = [
            'school_selection' => 'apply.school-selection',
            'bio_data' => 'apply.bio-data',
            'confirmation' => 'apply.confirmation',
            'documents' => 'apply.documents'
        ];
        
        $route = $stepRoutes[$step] ?? 'apply.school-selection';
        
        return redirect()
            ->route($route)
            ->with('success', 'Welcome back! You can continue your application.');
    }
    
    /**
     * Helper: Format bytes to human-readable size
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Download Temporary Admission Letter (PDF)
     */
    public function downloadAdmissionLetter($applicationNumber)
    {
        try {
            // Find the application by application number
            $application = StudentApplication::where('application_number', $applicationNumber)
                                            ->whereNotNull('submitted_at')
                                            ->first();
            
            if (!$application) {
                return redirect()
                    ->route('apply.status.form')
                    ->with('error', 'Application not found. Please check your application number.');
            }
            
            // Check if application is accepted
            if ($application->status !== 'accepted') {
                return redirect()
                    ->route('apply.status.form')
                    ->with('error', 'Admission letter is only available for accepted applications. Your current status is: ' . ucwords(str_replace('_', ' ', $application->status)));
            }
            
            // Get the school/enterprise
            $school = $application->selectedEnterprise;
            
            if (!$school) {
                throw new \Exception('School information not found for this application.');
            }
            
            // Prepare logo path
            $logoPath = null;
            if ($school->logo) {
                $logoPath = public_path('storage/' . $school->logo);
                // Check if file exists, if not use a fallback
                if (!file_exists($logoPath)) {
                    $logoPath = public_path('storage/images/default-logo.png');
                }
            }
            
            // Parse required documents from school configuration
            $requiredDocuments = [];
            if ($school->required_application_documents) {
                $docs = json_decode($school->required_application_documents, true);
                if (is_array($docs)) {
                    $requiredDocuments = $docs;
                }
            }
            
            // Prepare fee structure (if available)
            $feeStructure = [];
            
            // Try to get class-specific fees if applying_for_class is set
            if ($application->applying_for_class) {
                try {
                    // Get academic class by name
                    $academicClass = \App\Models\AcademicClass::where('enterprise_id', $school->id)
                                                               ->where('name', $application->applying_for_class)
                                                               ->first();
                    
                    if ($academicClass) {
                        $activeTerm = $school->active_term();
                        
                        if ($activeTerm) {
                            // Get class fees for active term
                            foreach ($academicClass->academic_class_fees as $fee) {
                                if ($fee->due_term_id == $activeTerm->id) {
                                    $feeStructure[] = [
                                        'name' => $fee->name ?? 'School Fee',
                                        'amount' => $fee->amount ?? 0
                                    ];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // If we can't get class fees, continue without them
                    Log::warning('Could not fetch class fees: ' . $e->getMessage());
                }
            }
            
            // If no fees found, add some generic estimated fees
            if (empty($feeStructure)) {
                $feeStructure = [
                    ['name' => 'Tuition Fee', 'amount' => 0],
                    ['name' => 'Registration Fee', 'amount' => 0],
                ];
            }
            
            // Prepare data for the view
            $data = [
                'application' => $application,
                'school' => $school,
                'logoPath' => $logoPath,
                'requiredDocuments' => $requiredDocuments,
                'feeStructure' => $feeStructure,
            ];
            
            // Generate PDF using DomPDF
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadView('student-application.temporary-admission-letter', $data);
            $pdf->setPaper('a4', 'portrait');
            
            // Generate filename
            $fileName = 'Temporary-Admission-Letter-' . $application->application_number . '.pdf';
            
            // Stream the PDF to browser
            return $pdf->stream($fileName);
            
        } catch (\Exception $e) {
            Log::error('Error generating admission letter: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()
                ->route('apply.status.form')
                ->with('error', 'An error occurred while generating your admission letter. Please contact the admissions office. Error: ' . $e->getMessage());
        }
    }
}
