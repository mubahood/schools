<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use App\Models\User;
use App\Models\AdminRoleUser;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\BillingService;
use App\Models\OnboardingDraft;
use App\Models\OnBoardWizard;

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

        // Nothing is written to admin_users/enterprises until the owner proves the
        // phone or email is theirs. The draft survives a closed browser.
        $draft = OnboardingDraft::start($request->only(['first_name', 'last_name', 'email', 'phone_number', 'password']), $request->ip());
        [$ok, $why] = $draft->canResend();
        $channels = $ok ? $draft->sendOtp() : [];
        session([
            'onboarding_user_data' => $draft->user_data,
            'onboarding_draft_token' => $draft->token,
            'onboarding_otp_channels' => $channels,
        ]);

        return response()->json([
            'success' => true,
            'next_step' => url('onboarding/verify')
        ]);
    }

        /**
     * Step 3: Comprehensive school information collection
     */
    public function step3()
    {
        if (!$this->verifiedDraft()) {
            return redirect('onboarding/verify')->with('verify_error', 'Please verify your phone or email first.');
        }
        return view('onboarding.step3');
    }

    /** The current session's draft, only if it has been verified and not yet used. */
    private function verifiedDraft(): ?OnboardingDraft
    {
        $token = session('onboarding_draft_token');
        if (!$token) {
            return null;
        }
        $d = OnboardingDraft::where('token', $token)->whereNull('consumed_at')->first();

        return ($d && $d->isVerified()) ? $d : null;
    }

    private function currentDraft(): ?OnboardingDraft
    {
        $token = session('onboarding_draft_token');

        return $token ? OnboardingDraft::where('token', $token)->whereNull('consumed_at')->first() : null;
    }

    public function verify()
    {
        $draft = $this->currentDraft();
        if (!$draft) {
            return redirect('onboarding/step2')->with('error', 'Please start again.');
        }
        if ($draft->isVerified()) {
            return redirect('onboarding/step3');
        }
        return view('onboarding.verify', ['draft' => $draft, 'channels' => session('onboarding_otp_channels', [])]);
    }

    public function processVerify(Request $request)
    {
        $draft = $this->currentDraft();
        if (!$draft) {
            return redirect('onboarding/step2')->with('error', 'Please start again.');
        }
        $err = $draft->verify((string) $request->input('code', ''), 'sms');
        if ($err) {
            return redirect('onboarding/verify')->with('verify_error', $err);
        }
        return redirect('onboarding/step3');
    }

    public function resendCode()
    {
        $draft = $this->currentDraft();
        if (!$draft) {
            return redirect('onboarding/step2');
        }
        [$ok, $why] = $draft->canResend();
        if (!$ok) {
            return redirect('onboarding/verify')->with('verify_error', $why);
        }
        session(['onboarding_otp_channels' => $draft->sendOtp()]);
        return redirect('onboarding/verify');
    }

    /** Resume from the emailed link after the browser was closed. */
    public function resume($token)
    {
        $draft = OnboardingDraft::where('token', $token)->whereNull('consumed_at')->first();
        if (!$draft) {
            return redirect('onboarding/step1')->with('error', 'That link has expired. Please register again.');
        }
        session(['onboarding_user_data' => $draft->user_data, 'onboarding_draft_token' => $draft->token]);
        if ($draft->enterprise_data) {
            session(['onboarding_enterprise_data' => $draft->enterprise_data]);
        }
        if (!$draft->isVerified()) {
            return redirect('onboarding/verify');
        }
        return redirect($draft->enterprise_data ? 'onboarding/step4' : 'onboarding/step3');
    }

    /**
     * Process Step 3: Validate and store comprehensive enterprise data in session
     */
    public function processStep3(Request $request)
    {
        // Normalise the web address BEFORE validation so a typed "St. Mary's"
        // becomes "st-mary-s" instead of failing the regex. Empty -> from the name.
        $request->merge(['subdomain' => self::normaliseSubdomain($request->subdomain ?: $request->school_name)]);

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
            
            // Web address
            'subdomain' => 'nullable|string|min:3|max:30|regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/',

            // Branding
            'primary_color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            // Basic Information Messages
            'school_name.required' => 'School name is required.',
            'school_name.unique' => 'A school with this name is already registered.',
            'subdomain.regex' => 'Web address may only contain lowercase letters, numbers and hyphens.',
            'subdomain.min' => 'Web address must be at least 3 characters.',
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

        // Web address: the owner's choice, or derived from the name. It becomes the
        // tenant subdomain, so it must be unique and not a reserved word.
        $subdomain = self::normaliseSubdomain($request->subdomain ?: $request->school_name);
        if ($err = self::subdomainProblem($subdomain)) {
            return response()->json(['success' => false, 'errors' => ['subdomain' => [$err]]]);
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
        
        // Handle logo upload if provided
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            if ($logoFile->isValid()) {
                $logoName = time() . '_' . $logoFile->getClientOriginalName();
                $logoPath = $logoFile->storeAs('public/uploads/logos', $logoName);
                $logoPath = str_replace('public/', 'storage/', $logoPath);
            }
        }

        // Add default values for fields that will be collected in later steps
        $enterpriseData['secondary_color'] = '#6c757d'; // Default gray
        $enterpriseData['school_pay_status'] = 'No';
        $enterpriseData['has_valid_lisence'] = 'Yes';
        $enterpriseData['school_motto'] = '';
        $enterpriseData['welcome_message'] = '';
        $enterpriseData['details'] = '';
        $enterpriseData['logo_path'] = $logoPath; // Set logo path or null
        
        // Add missing contact fields
        $enterpriseData['school_phone_2'] = '';
        $enterpriseData['school_website'] = '';
        
        // Add missing financial fields
        $enterpriseData['school_pay_code'] = '';
        $enterpriseData['school_pay_password'] = '';
        $enterpriseData['school_pay_import_automatically'] = 'No';
        $enterpriseData['school_pay_last_accepted_date'] = null;
        
        // Access is granted by BillingService::startTrial() at creation time.
        $enterpriseData['expiry'] = null;

        session(['onboarding_enterprise_data' => $enterpriseData]);
        if ($draft = $this->currentDraft()) {
            $draft->enterprise_data = $enterpriseData;
            $draft->save();
        }

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
        // Verification first: an unverified visitor gets sent to the code
        // page rather than told their session expired.
        $draft = $this->verifiedDraft();
        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your phone or email before creating the school.',
                'next_step' => url('onboarding/verify'),
            ]);
        }
        $userData = session('onboarding_user_data');
        $enterpriseData = session('onboarding_enterprise_data');

        if (!$userData || !$enterpriseData) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please start again.'
            ]);
        }
        // Re-check uniqueness at the moment of creation: another signup may have
        // taken the email/phone/subdomain while this one sat unverified.
        if (User::where('email', $userData['email'])->exists() || User::where('phone_number_1', $userData['phone_number'])->exists()) {
            return response()->json(['success' => false, 'message' => 'That email or phone number has just been registered by someone else.']);
        }
        if (self::subdomainProblem($enterpriseData['subdomain'])) {
            return response()->json(['success' => false, 'message' => 'That web address was just taken. Please go back and choose another.']);
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
            // Hashed once at step 2; the plaintext never lived in the draft.
            $user->password = $userData['password_hash'] ?? Hash::make($userData['password'] ?? Str::random(16));
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
            $enterprise->motto = $enterpriseData['school_motto'] ?? '';
            $enterprise->welcome_message = $enterpriseData['welcome_message'] ?? '';
            $enterprise->has_theology = $enterpriseData['has_theology'];
            $enterprise->logo = $enterpriseData['logo_path'] ?? null;
            
            // Contact Information
            $enterprise->email = $enterpriseData['school_email'];
            $enterprise->phone_number = $enterpriseData['school_phone'];
            $enterprise->phone_number_2 = $enterpriseData['school_phone_2'] ?? '';
            $enterprise->website = $enterpriseData['school_website'] ?? '';
            $enterprise->address = $enterpriseData['school_address'];
            
            // Administrative Information
            $enterprise->administrator_id = $user->id;
            $enterprise->hm_name = $enterpriseData['hm_name'] ?? '';
            
            // Branding & Appearance
            $enterprise->color = $enterpriseData['primary_color'];
            $enterprise->sec_color = $enterpriseData['secondary_color'] ?? '#6c757d';
            $enterprise->subdomain = $enterpriseData['subdomain'];
            
            // Financial Settings
            $enterprise->school_pay_status = $enterpriseData['school_pay_status'] ?? 'No';
            $enterprise->school_pay_code = $enterpriseData['school_pay_code'] ?? '';
            $enterprise->school_pay_password = $enterpriseData['school_pay_password'] ?? '';
            $enterprise->school_pay_import_automatically = $enterpriseData['school_pay_import_automatically'] ?? 'No';
            $enterprise->school_pay_last_accepted_date = $enterpriseData['school_pay_last_accepted_date'] ?? null;
            $enterprise->wallet_balance = 0; // Default wallet balance
            $enterprise->can_send_messages = 'Yes'; // Default can send messages
            
            // License & System
            $enterprise->has_valid_lisence = $enterpriseData['has_valid_lisence'] ?? 'Yes';
            $enterprise->expiry = $enterpriseData['expiry'] ?? null;
            $enterprise->details = $enterpriseData['details'] ?? '';
            
            $enterprise->save();

            // 3. Update user's enterprise_id to the created enterprise
            $user->enterprise_id = $enterprise->id;
            $user->save();

            // 4. The owner holds exactly one role: Enterprise Admin (Owner), id 2.
            //    (Role 6 was previously added here believing it was super-admin;
            //    it is Director of Studies.)
            $adminRole = new AdminRoleUser();
            $adminRole->user_id = $user->id;
            $adminRole->role_id = 2;
            $adminRole->save();

            // 5. Unique tenant address, enforced by the DB.
            DB::table('enterprises')->where('id', $enterprise->id)
                ->update(['subdomain_slug' => $enterpriseData['subdomain']]);

            // 6. A school cannot do anything without an academic year and a term;
            //    the old flow left new owners to discover that alone. Seed the
            //    current year with three terms, the first one active.
            self::seedFirstAcademicYear($enterprise->id);

            // 7. 30-day trial starts now; billing takes over from here.
            BillingService::startTrial($enterprise);

            // 8. Identity was proven before creation, so the in-app "verify your
            //    email" gate is already satisfied — the owner lands on the dashboard.
            OnBoardWizard::where('enterprise_id', $enterprise->id)->update([
                'email_is_verified' => 'Yes', 'email_verified_at' => now(), 'current_step' => 'school_details',
            ]);
            $draft->consumed_at = now();
            $draft->enterprise_id = $enterprise->id;
            $draft->save();

            DB::commit();

            // Clear session data
            session()->forget(['onboarding_user_data', 'onboarding_enterprise_data', 'onboarding_draft_token', 'onboarding_otp_channels']);

            // Store user info for welcome step
            $trialEnds = now()->addDays(BillingService::TRIAL_DAYS);
            session(['onboarding_success' => [
                'user_name' => $user->name,
                'school_name' => $enterprise->name,
                'email' => $user->email,
                'subdomain' => $enterpriseData['subdomain'],
                'trial_ends' => $trialEnds->format('d M Y'),
                'trial_days' => BillingService::TRIAL_DAYS,
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

    public const RESERVED_SUBDOMAINS = ['www', 'api', 'admin', 'app', 'mail', 'ftp', 'login', 'register', 'billing',
        'gateway', 'static', 'cdn', 'help', 'support', 'test', 'demo', 'schooldynamics', 'onboarding', 'kihp'];

    public static function normaliseSubdomain(?string $raw): string
    {
        $s = strtolower(trim((string) $raw));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        $s = trim(preg_replace('/-+/', '-', $s), '-');

        return substr($s, 0, 30);
    }

    /** Null when usable, otherwise the message to show. */
    public static function subdomainProblem(string $s): ?string
    {
        if (strlen($s) < 3) {
            return 'Web address must be at least 3 characters.';
        }
        if (in_array($s, self::RESERVED_SUBDOMAINS, true)) {
            return 'That web address is reserved. Please choose another.';
        }
        $taken = Enterprise::whereRaw('LOWER(TRIM(subdomain)) = ?', [$s])->orWhere('subdomain_slug', $s)->exists();

        return $taken ? 'That web address is already taken.' : null;
    }

    public function validateSubdomain(Request $request)
    {
        $s = self::normaliseSubdomain($request->get('subdomain'));
        $problem = self::subdomainProblem($s);

        return response()->json([
            'available' => $problem === null,
            'value' => $s,
            'message' => $problem ?: ($s . '.schooldynamics.ug is available.'),
        ]);
    }

    /**
     * Make sure a brand-new school can be used immediately.
     *
     * Enterprise::my_update() already creates the academic year (and the
     * AcademicYear model seeds its terms), so this only fills gaps: terms if
     * none exist, exactly one active term, and the dp_year / dp_term_id
     * pointers that older screens still read. Idempotent.
     */
    public static function seedFirstAcademicYear(int $enterpriseId): void
    {
        $now = now();
        $yearId = DB::table('academic_years')->where('enterprise_id', $enterpriseId)->where('is_active', 1)->value('id')
            ?: DB::table('academic_years')->where('enterprise_id', $enterpriseId)->orderBy('id')->value('id');
        if (!$yearId) {
            $y = (int) date('Y');
            $yearId = DB::table('academic_years')->insertGetId([
                'enterprise_id' => $enterpriseId, 'name' => (string) $y, 'details' => 'Academic year ' . $y,
                'starts' => "$y-01-01", 'ends' => "$y-12-31", 'is_active' => 1, 'demo_id' => 0, 'process_data' => 'Yes',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        if (!DB::table('terms')->where('academic_year_id', $yearId)->exists()) {
            $y = (int) date('Y');
            foreach ([[1, "$y-02-01", "$y-05-10"], [2, "$y-05-25", "$y-08-25"], [3, "$y-09-15", "$y-12-10"]] as [$n, $from, $to]) {
                DB::table('terms')->insert([
                    'enterprise_id' => $enterpriseId, 'academic_year_id' => $yearId, 'name' => (string) $n,
                    'term_name' => (string) $n, 'details' => "Term $n - $y", 'starts' => $from, 'ends' => $to,
                    'is_active' => $n === 1 ? 1 : 0, 'demo_id' => 0, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
        $activeTerm = DB::table('terms')->where('academic_year_id', $yearId)->where('is_active', 1)->orderBy('id')->value('id');
        if (!$activeTerm) {
            $activeTerm = DB::table('terms')->where('academic_year_id', $yearId)->orderBy('id')->value('id');
            DB::table('terms')->where('id', $activeTerm)->update(['is_active' => 1]);
        }
        DB::table('enterprises')->where('id', $enterpriseId)->update(['dp_year' => $yearId, 'dp_term_id' => $activeTerm]);
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
