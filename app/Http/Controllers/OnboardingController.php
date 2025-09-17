<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use App\Models\User;
use App\Models\AdminRoleUser;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * Step 1: Introduction to the platform
     */
    public function step1()
    {
        return view('onboarding.step1');
    }

    /**
     * Step 2: Collect user basic information
     */
    public function step2()
    {
        return view('onboarding.step2');
    }

    /**
     * Process Step 2: Validate and store user data in session
     */
    public function processStep2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'phone_number' => 'required|string|unique:admin_users,phone_number_1',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'This email address is already registered.',
            'phone_number.unique' => 'This phone number is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 6 characters long.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Store user data in session
        session(['onboarding_user_data' => $request->only([
            'first_name', 'last_name', 'email', 'phone_number', 'password'
        ])]);

        return response()->json([
            'success' => true,
            'next_step' => url('onboarding/step3')
        ]);
    }

        /**
     * Step 3: Comprehensive school information collection
     */
    public function step3()
    {
        return view('onboarding.step3');
    }

    /**
     * Process Step 3: Validate and store comprehensive enterprise data in session
     */
    public function processStep3(Request $request)
    {
                $validator = Validator::make($request->all(), [
            // Basic Information
            'school_name' => 'required|string|max:255|unique:enterprises,name',
            'school_short_name' => 'required|string|max:50',
            'founded_year' => 'nullable|integer|min:1800|max:2025',
            'school_type' => 'required|in:Primary,Secondary,Advanced,University',
            'has_theology' => 'required|in:Yes,No',
            
            // Contact Information
            'school_email' => 'required|email|max:255|unique:enterprises,email',
            'school_phone' => 'required|string|max:20|unique:enterprises,phone_number',
            'school_address' => 'required|string|max:500',
            
            // Administrative
            'hm_name' => 'nullable|string|max:255',
            'hm_phone' => 'nullable|string|max:20',
            
            // Branding
            'primary_color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ], [
            // Basic Information Messages
            'school_name.required' => 'School name is required.',
            'school_name.unique' => 'A school with this name is already registered.',
            'school_short_name.required' => 'School short name is required.',
            'school_type.required' => 'Please select the school level/type.',
            'school_type.in' => 'Invalid school type selected.',
            'has_theology.required' => 'Please specify if the school offers Religious Studies/Theology.',
            'has_theology.in' => 'Invalid selection for Religious Studies/Theology.',
            
            // Contact Information Messages
            'school_email.required' => 'School email address is required.',
            'school_email.email' => 'Please enter a valid email address.',
            'school_email.unique' => 'This email address is already used by another school.',
            'school_phone.required' => 'School phone number is required.',
            'school_phone.unique' => 'This phone number is already used by another school.',
            'school_address.required' => 'School address is required.',
            
            // Branding Messages
            'primary_color.required' => 'Please select a primary school color.',
            'primary_color.regex' => 'Please select a valid color.',
            
            // Founded Year Messages
            'founded_year.min' => 'Founded year must be after 1800.',
            'founded_year.max' => 'Founded year cannot be in the future.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Auto-generate short name if not provided
        $shortName = $request->school_short_name;
        if (empty($shortName) && $request->school_name) {
            $words = explode(' ', $request->school_name);
            $shortName = strtoupper(substr(implode('', array_map(function($word) {
                return substr($word, 0, 1);
            }, $words)), 0, 5));
        }

        // Auto-generate subdomain if not provided
        $subdomain = '';
        if ($request->school_name) {
            $subdomain = strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($request->school_name)));
            $subdomain = substr($subdomain, 0, 20);
        }

        // Store basic enterprise data in session with defaults for missing fields
        $enterpriseData = $request->only([
            'school_name', 'school_type', 'has_theology',
            'school_email', 'school_phone', 'school_address',
            'hm_name', 'hm_phone', 'primary_color', 'founded_year'
        ]);

        // Add generated/default fields
        $enterpriseData['school_short_name'] = $shortName;
        $enterpriseData['subdomain'] = $subdomain;
        
        // Add default values for fields that will be collected in later steps
        $enterpriseData['secondary_color'] = '#6c757d'; // Default gray
        $enterpriseData['school_pay_status'] = 'No';
        $enterpriseData['has_valid_lisence'] = 'Yes';
        $enterpriseData['school_motto'] = '';
        $enterpriseData['welcome_message'] = '';
        $enterpriseData['details'] = '';

        session(['onboarding_enterprise_data' => $enterpriseData]);

        return response()->json([
            'success' => true,
            'message' => 'School information saved successfully!',
            'next_step' => url('onboarding/step4')
        ]);
    }

    /**
     * Step 4: Review and confirmation
     */
    public function step4()
    {
        $userData = session('onboarding_user_data');
        $enterpriseData = session('onboarding_enterprise_data');

        if (!$userData || !$enterpriseData) {
            return redirect('onboarding/step1')->with('error', 'Session expired. Please start again.');
        }

        return view('onboarding.step4', compact('userData', 'enterpriseData'));
    }

    /**
     * Process Step 4: Create user and enterprise
     */
    public function processStep4(Request $request)
    {
        $userData = session('onboarding_user_data');
        $enterpriseData = session('onboarding_enterprise_data');

        if (!$userData || !$enterpriseData) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please start again.'
            ]);
        }

        DB::beginTransaction();

        try {
            // 1. Create user with default enterprise_id = 1
            $user = new User();
            $user->name = $userData['first_name'] . ' ' . $userData['last_name'];
            $user->first_name = $userData['first_name'];
            $user->last_name = $userData['last_name'];
            $user->email = $userData['email'];
            $user->phone_number_1 = $userData['phone_number'];
            $user->password = Hash::make($userData['password']);
            $user->enterprise_id = 1; // Default enterprise initially
            $user->user_type = 'employee';
            $user->status = 1;
            $user->save();

            // 2. Create comprehensive enterprise
            $enterprise = new Enterprise();
            
            // Basic Information
            $enterprise->name = $enterpriseData['school_name'];
            $enterprise->short_name = $enterpriseData['school_short_name'];
            $enterprise->type = $enterpriseData['school_type'];
            $enterprise->motto = $enterpriseData['school_motto'];
            $enterprise->welcome_message = $enterpriseData['welcome_message'];
            $enterprise->has_theology = $enterpriseData['has_theology'];
            $enterprise->logo = $enterpriseData['logo_path'];
            
            // Contact Information
            $enterprise->email = $enterpriseData['school_email'];
            $enterprise->phone_number = $enterpriseData['school_phone'];
            $enterprise->phone_number_2 = $enterpriseData['school_phone_2'];
            $enterprise->website = $enterpriseData['school_website'];
            $enterprise->address = $enterpriseData['school_address'];
            
            // Administrative Information
            $enterprise->administrator_id = $user->id;
            $enterprise->hm_name = $enterpriseData['hm_name'];
            
            // Branding & Appearance
            $enterprise->color = $enterpriseData['primary_color'];
            $enterprise->sec_color = $enterpriseData['secondary_color'];
            $enterprise->subdomain = $enterpriseData['subdomain'];
            
            // Financial Settings
            $enterprise->school_pay_status = $enterpriseData['school_pay_status'];
            $enterprise->school_pay_code = $enterpriseData['school_pay_code'];
            $enterprise->school_pay_password = $enterpriseData['school_pay_password'];
            $enterprise->school_pay_import_automatically = $enterpriseData['school_pay_import_automatically'] ?? 'No';
            $enterprise->school_pay_last_accepted_date = $enterpriseData['school_pay_last_accepted_date'];
            $enterprise->wallet_balance = 0; // Default wallet balance
            
            // License & System
            $enterprise->has_valid_lisence = $enterpriseData['has_valid_lisence'];
            $enterprise->expiry = $enterpriseData['expiry'];
            $enterprise->details = $enterpriseData['details'];
            
            $enterprise->save();

            // 3. Update user's enterprise_id to the created enterprise
            $user->enterprise_id = $enterprise->id;
            $user->save();

            // 4. Assign admin role to user (role_id = 2 for admin)
            $adminRole = new AdminRoleUser();
            $adminRole->user_id = $user->id;
            $adminRole->role_id = 2; // Admin role
            $adminRole->save();

            // 5. Also assign super-admin role (role_id = 6)
            $superAdminRole = new AdminRoleUser();
            $superAdminRole->user_id = $user->id;
            $superAdminRole->role_id = 6; // Super-admin role
            $superAdminRole->save();

            DB::commit();

            // Clear session data
            session()->forget(['onboarding_user_data', 'onboarding_enterprise_data']);

            // Store user info for welcome step
            session(['onboarding_success' => [
                'user_name' => $user->name,
                'school_name' => $enterprise->name,
                'email' => $user->email
            ]]);

            return response()->json([
                'success' => true,
                'next_step' => url('onboarding/step5')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Step 5: Welcome message and completion
     */
    public function step5()
    {
        $successData = session('onboarding_success');

        if (!$successData) {
            return redirect('onboarding/step1')->with('error', 'Invalid access.');
        }

        return view('onboarding.step5', compact('successData'));
    }

    /**
     * Complete onboarding and redirect to login
     */
    public function complete()
    {
        session()->forget('onboarding_success');
        return redirect('auth/login')->with('success', 'Registration completed successfully. Please login to continue.');
    }

    /**
     * AJAX validation endpoints
     */
    public function validateEmail(Request $request)
    {
        $email = $request->get('email');
        $exists = User::where('email', $email)->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Email address is already taken.' : 'Email address is available.'
        ]);
    }

    public function validatePhone(Request $request)
    {
        $phone = $request->get('phone');
        $exists = User::where('phone_number_1', $phone)->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Phone number is already taken.' : 'Phone number is available.'
        ]);
    }

    public function validateSchoolName(Request $request)
    {
        $name = $request->get('name');
        $exists = Enterprise::where('name', $name)->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'School name is already taken.' : 'School name is available.'
        ]);
    }

    public function validateSchoolEmail(Request $request)
    {
        $email = $request->get('email');
        $exists = Enterprise::where('email', $email)->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'School email is already taken.' : 'School email is available.'
        ]);
    }

    /**
     * Save form data to session for auto-save functionality
     */
    public function saveSession(Request $request)
    {
        try {
            $step = $request->input('step');
            $field = $request->input('field');
            $value = $request->input('value');

            // Validate inputs
            if (!$step || !$field) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid parameters'
                ], 400);
            }

            // Get current session data for this step
            $sessionKey = "onboarding.{$step}";
            $stepData = session($sessionKey, []);

            // Update the specific field
            $stepData[$field] = $value;

            // Save back to session
            session([$sessionKey => $stepData]);

            return response()->json([
                'success' => true,
                'message' => 'Data saved to session',
                'field' => $field,
                'value' => $value
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save to session: ' . $e->getMessage()
            ], 500);
        }
    }
}
