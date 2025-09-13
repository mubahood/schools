@extends('layouts.onboarding')

@section('title', 'School Information - ' . config('app.name'))
@section('meta_description', 'Provide comprehensive school details to complete the registration process.')

@section('progress-indicator')
<div class="progress-indicator">
    <div class="progress-step completed">
        <i class='bx bx-check'></i>
        <span>Account</span>
    </div>
    <div class="progress-divider active"></div>
    <div class="progress-step completed">
        <i class='bx bx-check'></i>
        <span>Profile</span>
    </div>
    <div class="progress-divider active"></div>
    <div class="progress-step active">
        <i class='bx bx-building'></i>
        <span>School Info</span>
    </div>
    <div class="progress-divider"></div>
    <div class="progress-step">
        <i class='bx bx-check'></i>
        <span>Complete</span>
    </div>
</div>
@endsection

@section('content')
<div class="content-card">
    <div class="content-header">
        <h1 class="content-title">School Information</h1>
        <p class="content-subtitle">Complete your school information across all categories to set up your professional management system.</p>
    </div>

    <form id="step3Form" method="POST" enctype="multipart/form-data">
            @csrf
            
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button type="button" class="tab-btn" data-tab="basic">
                <i class='bx bx-info-circle'></i>
                <span>Basic Info</span>
            </button>
            <button type="button" class="tab-btn" data-tab="contact">
                <i class='bx bx-phone'></i>
                <span>Contact</span>
            </button>
            <button type="button" class="tab-btn" data-tab="admin">
                <i class='bx bx-user-pin'></i>
                <span>Administration</span>
            </button>
            <button type="button" class="tab-btn" data-tab="branding">
                <i class='bx bx-palette'></i>
                <span>Branding</span>
            </button>
            <button type="button" class="tab-btn" data-tab="financial">
                <i class='bx bx-credit-card'></i>
                <span>Financial</span>
            </button>
            <button type="button" class="tab-btn" data-tab="license">
                <i class='bx bx-shield-check'></i>
                <span>License</span>
            </button>
        </div>

        <!-- Basic Information Tab -->
        <div class="tab-content" id="basic-tab">
            <div class="form-section">
                <h2 class="section-title">Basic School Information</h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="school_name">
                            School Name <span style="color: var(--error-color);">*</span>
                        </label>
                        <input type="text" id="school_name" name="school_name" class="form-input" required 
                               placeholder="Enter the full official name of your school">
                        <span class="form-help">This will appear on all official documents and reports</span>
                        <div class="error-message" id="school_name_error"></div>
                    </div>
                        School Name <span style="color: var(--error-color);">*</span>
                    </label>
                    <input type="text" id="school_name" name="school_name" class="form-input" required 
                           placeholder="Enter the full official name of your school">
                    <small class="form-help">This will appear on all official documents and reports</small>
                    <div class="error-message" id="school_name_error"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="school_short_name">
                        Short Name/Abbreviation <span style="color: var(--error-color);">*</span>
                    </label>
                    <input type="text" id="school_short_name" name="school_short_name" class="form-input" 
                           maxlength="20" placeholder="Auto-generated from school name">
                    <small class="form-help">e.g., SMS for St. Mary's School (auto-generated but editable)</small>
                    <div class="error-message" id="school_short_name_error"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        School Type <span style="color: var(--error-color);">*</span>
                    </label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="school_type" value="Primary" required>
                            <span class="radio-label">
                                <strong>Primary School</strong>
                                <small>Elementary education (P1-P7)</small>
                            </span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="school_type" value="Secondary" required>
                            <span class="radio-label">
                                <strong>Secondary School (O'Level)</strong>
                                <small>Ordinary Level education (S1-S4)</small>
                            </span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="school_type" value="Advanced" required>
                            <span class="radio-label">
                                <strong>Advanced School (O'Level + A'Level)</strong>
                                <small>Complete secondary education (S1-S6)</small>
                            </span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="school_type" value="University" required>
                            <span class="radio-label">
                                <strong>University/Tertiary Institution</strong>
                                <small>Higher education institution</small>
                            </span>
                        </label>
                    </div>
                    <small class="form-help">Select the type that best describes your educational institution</small>
                    <div class="error-message" id="school_type_error"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="school_motto">School Motto</label>
                    <input type="text" id="school_motto" name="school_motto" class="form-input" 
                           placeholder="Enter your school's motto or slogan">
                    <small class="form-help">Your school's guiding principle or inspirational phrase</small>
                    <div class="error-message" id="school_motto_error"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="welcome_message">Welcome Message</label>
                    <textarea id="welcome_message" name="welcome_message" class="form-input" rows="4" 
                              placeholder="Welcome message displayed on dashboards and reports"></textarea>
                    <small class="form-help">This message will be displayed to users when they log in</small>
                    <div class="error-message" id="welcome_message_error"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="logo">School Logo</label>
                    <input type="file" id="logo" name="logo" class="form-input" accept="image/jpeg,image/png,image/jpg">
                    <small class="form-help">Upload your school logo (recommended: 200x200px, PNG/JPG, max 2MB)</small>
                    <div class="error-message" id="logo_error"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        Religious Studies <span style="color: var(--error-color);">*</span>
                    </label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="has_theology" value="Yes" required>
                            <span class="radio-label">
                                <strong>Yes</strong>
                                <small>School offers religious/theology subjects</small>
                            </span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="has_theology" value="No" required>
                            <span class="radio-label">
                                <strong>No</strong>
                                <small>School does not offer religious subjects</small>
                            </span>
                        </label>
                    </div>
                    <small class="form-help">Indicate whether your school offers religious or theology subjects</small>
                    <div class="error-message" id="has_theology_error"></div>
                </div>
            </div>

        <!-- Contact Information Tab -->
        <div class="tab-content" id="contact-tab">
            <h3 class="tab-title">Contact Information</h3>
            
            <div class="form-group">
                <label class="form-label" for="school_phone">Primary Phone Number *</label>
                <input type="tel" id="school_phone" name="school_phone" class="form-input" required placeholder="Enter main contact number">
                <small class="form-help">Main contact number for the school</small>
                <div class="error-message" id="school_phone_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="school_phone_2">Secondary Phone Number</label>
                <input type="tel" id="school_phone_2" name="school_phone_2" class="form-input" placeholder="Enter alternative contact number">
                <small class="form-help">Alternative contact number</small>
                <div class="error-message" id="school_phone_2_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="school_email">Email Address *</label>
                <input type="email" id="school_email" name="school_email" class="form-input" required placeholder="Enter official school email">
                <small class="form-help">Official school email address</small>
                <div class="error-message" id="school_email_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="school_website">Website URL</label>
                <input type="url" id="school_website" name="school_website" class="form-input" placeholder="https://example.com">
                <small class="form-help">School website URL (e.g., https://school.com)</small>
                <div class="error-message" id="school_website_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="school_address">Physical Address *</label>
                <textarea id="school_address" name="school_address" class="form-input" rows="3" required placeholder="Enter complete physical address"></textarea>
                <small class="form-help">Complete physical address of the school</small>
                <div class="error-message" id="school_address_error"></div>
            </div>
        </div>

        <!-- Administration Tab -->
        <div class="tab-content" id="admin-tab">
            <h3 class="tab-title">Administrative Information</h3>
            
            <div class="form-group">
                <label class="form-label" for="hm_name">Head Teacher/Principal Name</label>
                <input type="text" id="hm_name" name="hm_name" class="form-input" placeholder="Enter name of head teacher or principal">
                <small class="form-help">Name of the current head teacher or principal</small>
                <div class="error-message" id="hm_name_error"></div>
            </div>
            
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Administrator Assignment</strong>
                    <p>You will be automatically assigned as the school owner/administrator after registration.</p>
                </div>
            </div>
        </div>

        <!-- Branding & Appearance Tab -->
        <div class="tab-content" id="branding-tab">
            <h3 class="tab-title">Branding & Appearance</h3>
            
            <div class="form-group">
                <label class="form-label" for="primary_color">Primary Color *</label>
                <input type="color" id="primary_color" name="primary_color" class="form-input color-input" value="#007bff" required>
                <small class="form-help">Main brand color used throughout the system</small>
                <div class="error-message" id="primary_color_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="secondary_color">Secondary Color *</label>
                <input type="color" id="secondary_color" name="secondary_color" class="form-input color-input" value="#6c757d" required>
                <small class="form-help">Secondary brand color for accents</small>
                <div class="error-message" id="secondary_color_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="subdomain">Subdomain</label>
                <input type="text" id="subdomain" name="subdomain" class="form-input" placeholder="myschool" maxlength="50">
                <small class="form-help">Unique subdomain for school (letters, numbers, hyphens only)</small>
                <div class="error-message" id="subdomain_error"></div>
            </div>
        </div>

        <!-- Financial Settings Tab -->
        <div class="tab-content" id="financial-tab">
            <h3 class="tab-title">Financial Settings</h3>
            
            <div class="section-divider">
                <h4>SchoolPay Integration</h4>
            </div>
            
            <div class="form-group">
                <label class="form-label">SchoolPay Status *</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="school_pay_status" value="Yes" required>
                        <span class="radio-label">Enabled</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="school_pay_status" value="No" required checked>
                        <span class="radio-label">Disabled</span>
                    </label>
                </div>
                <small class="form-help">Enable SchoolPay payment gateway integration</small>
                <div class="error-message" id="school_pay_status_error"></div>
            </div>
            
            <div id="schoolpay-fields" style="display: none;">
                <div class="form-group">
                    <label class="form-label" for="school_pay_code">SchoolPay Institution Code *</label>
                    <input type="text" id="school_pay_code" name="school_pay_code" class="form-input" placeholder="Enter your SchoolPay institution code">
                    <small class="form-help">Your SchoolPay institution code</small>
                    <div class="error-message" id="school_pay_code_error"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="school_pay_password">SchoolPay API Password *</label>
                    <input type="password" id="school_pay_password" name="school_pay_password" class="form-input" placeholder="Enter SchoolPay API password">
                    <small class="form-help">SchoolPay API access password</small>
                    <div class="error-message" id="school_pay_password_error"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Auto Import Transactions</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="school_pay_import_automatically" value="Yes">
                            <span class="radio-label">Yes - Import automatically</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="school_pay_import_automatically" value="No" checked>
                            <span class="radio-label">No - Manual import only</span>
                        </label>
                    </div>
                    <small class="form-help">Automatically import SchoolPay transactions?</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="school_pay_last_accepted_date">Import From Date</label>
                    <input type="date" id="school_pay_last_accepted_date" name="school_pay_last_accepted_date" class="form-input" value="{{ date('Y-01-01') }}">
                    <small class="form-help">Import transactions from this date onwards</small>
                </div>
            </div>
        </div>

        <!-- License & System Tab -->
        <div class="tab-content" id="license-tab">
            <h3 class="tab-title">License & System Settings</h3>
            
            <div class="form-group">
                <label class="form-label">License Status *</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="has_valid_lisence" value="Yes" required checked>
                        <span class="radio-label">Valid License</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="has_valid_lisence" value="No" required>
                        <span class="radio-label">Invalid/Expired License</span>
                    </label>
                </div>
                <small class="form-help">Current license validity status</small>
                <div class="error-message" id="has_valid_lisence_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="expiry">License Expiry Date</label>
                <input type="date" id="expiry" name="expiry" class="form-input" min="{{ date('Y-m-d') }}">
                <small class="form-help">When does the school license expire?</small>
                <div class="error-message" id="expiry_error"></div>
            </div>
            
            <div class="section-divider">
                <h4>Additional Information</h4>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="details">Additional Details</label>
                <textarea id="details" name="details" class="form-input" rows="4" placeholder="Any additional information about the school"></textarea>
                <small class="form-help">Any additional information about the school</small>
                <div class="error-message" id="details_error"></div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="form-navigation">
            <a href="{{ url('onboarding/step2') }}" class="btn btn-outline">
                <i class="bx bx-arrow-left"></i> 
                Previous Step
            </a>
            
            <div class="tab-navigation-buttons">
                <button type="button" id="prevTabBtn" class="btn btn-outline" onclick="prevTab()" style="display: none;">
                    <i class="bx bx-arrow-left"></i> 
                    Previous Section
                </button>
                <button type="button" id="nextTabBtn" class="btn btn-primary" onclick="nextTab()">
                    Next Section 
                    <i class="bx bx-arrow-right"></i>
                </button>
            </div>
            
            <button type="submit" id="submitBtn" class="btn btn-primary" style="display: none;">
                <i class="bx bx-check"></i>
                Complete Setup
            </button>
        </div>
    </form>
    </div>
@endsection

@section('additional-styles')
<style>
    /* Enhanced Tab Navigation Styles */
    .tab-navigation {
        margin-bottom: 2.5rem;
    }
    
    .tab-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }
    
    .tab-btn i {
        font-size: 1rem;
    }
    
    /* Tab Content Enhancements */
    .tab-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--text-dark);
        margin-bottom: 2rem;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: -0.025em;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-light);
    }
    
    .tab-title i {
        color: var(--primary-color);
        font-size: 1.5rem;
    }
    
    /* Enhanced Radio Groups */
    .radio-option {
        padding: 1.25rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        transition: all 0.2s ease;
        background: var(--background-primary);
    }
    
    .radio-option:hover {
        border-color: var(--primary-color);
        background: var(--primary-light);
        box-shadow: var(--shadow-sm);
    }
    
    .radio-option input[type="radio"]:checked + .radio-label {
        color: var(--primary-color);
    }
    
    .radio-option:has(input[type="radio"]:checked) {
        border-color: var(--primary-color);
        background: var(--primary-light);
        box-shadow: var(--shadow-md);
    }
    
    .radio-label {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .radio-label strong {
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .radio-label small {
        color: var(--text-light);
        font-size: 0.8rem;
        line-height: 1.4;
    }
    
    /* Enhanced Form Elements */
    .form-input {
        font-size: 0.9rem;
        padding: 1rem;
    }
    
    .form-input:focus {
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    
    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
    }
    
    .form-help {
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }
    
    /* Color Input Enhancements */
    .color-input {
        width: 100px;
        height: 50px;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .color-input:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-sm);
    }
    
    /* Section Divider Enhancements */
    .section-divider {
        margin: 2.5rem 0 2rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-light);
    }
    
    .section-divider h4 {
        color: var(--text-dark);
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        letter-spacing: -0.025em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .section-divider h4::before {
        content: '';
        width: 4px;
        height: 20px;
        background: var(--primary-color);
        border-radius: 2px;
    }
    
    /* Info Box Enhancements */
    .info-box {
        background: linear-gradient(135deg, var(--primary-light) 0%, rgba(79, 70, 229, 0.05) 100%);
        border: 2px solid rgba(79, 70, 229, 0.15);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin: 1.5rem 0;
    }
    
    .info-box i {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin-top: 0;
    }
    
    /* Form Navigation Enhancements */
    .form-navigation {
        background: var(--background-secondary);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-top: 2.5rem;
        border: 1px solid var(--border-light);
    }
    
    .tab-navigation-buttons .btn {
        min-width: 140px;
    }
    
    /* Animation Enhancements */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .tab-content.active {
        animation: slideIn 0.3s ease;
    }
    
    /* File Input Styling */
    input[type="file"].form-input {
        padding: 0.75rem;
        border: 2px dashed var(--border-color);
        background: var(--background-secondary);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    input[type="file"].form-input:hover {
        border-color: var(--primary-color);
        background: var(--primary-light);
    }
    
    input[type="file"].form-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    
    /* Responsive Enhancements */
    @media (max-width: 768px) {
        .tab-navigation {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
        
        .tab-btn {
            font-size: 0.8rem;
            padding: 0.75rem 0.5rem;
            text-align: center;
        }
        
        .tab-title {
            font-size: 1.25rem;
        }
        
        .form-container {
            padding: 1.5rem;
        }
    }
    
    @media (max-width: 480px) {
        .tab-btn {
            flex-direction: column;
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Initialize first tab as active
    if (tabButtons.length > 0 && tabContents.length > 0) {
        tabButtons[0].classList.add('active');
        tabContents[0].classList.add('active');
    }
    
    // Show specific tab
    function showTab(tabName) {
        currentTab = tabs.indexOf(tabName);
        
        // Update tab buttons
        $('.tab-btn').removeClass('active');
        $(`.tab-btn[data-tab="${tabName}"]`).addClass('active');
        
        // Update tab content
        $('.tab-content').removeClass('active');
        $(`#${tabName}-tab`).addClass('active');
        
        // Update navigation buttons
        updateNavigationButtons();
    }
    
    // Update navigation buttons visibility
    function updateNavigationButtons() {
        $('#prevTabBtn').toggle(currentTab > 0);
        $('#nextTabBtn').toggle(currentTab < tabs.length - 1);
        $('#submitBtn').toggle(currentTab === tabs.length - 1);
    }
    
    // Tab navigation buttons
    $('#prevTabBtn').on('click', function() {
        if (currentTab > 0) {
            showTab(tabs[currentTab - 1]);
        }
    });
    
    $('#nextTabBtn').on('click', function() {
        if (currentTab < tabs.length - 1) {
            showTab(tabs[currentTab + 1]);
        }
    });
    
    // Auto-generate short name from school name
    $('#school_name').on('input', function() {
        if (!$('#school_short_name').val()) {
            const name = $(this).val();
            const words = name.split(' ');
            let shortName = '';
            words.forEach(function(word) {
                if (word.length > 0) {
                    shortName += word.charAt(0).toUpperCase();
                }
            });
            $('#school_short_name').val(shortName.substring(0, 5));
        }
    });
    
    // Auto-generate subdomain from school name
    $('#school_name').on('input', function() {
        if (!$('#subdomain').val()) {
            const name = $(this).val().toLowerCase();
            const subdomain = name.replace(/[^a-z0-9]/g, '').substring(0, 20);
            $('#subdomain').val(subdomain);
        }
    });
    
    // Color picker feedback
    $('input[type="color"]').on('change', function() {
        const color = $(this).val();
        $(this).closest('.form-group').find('.form-help').html(`Selected color: ${color}`);
    });
    
    // SchoolPay conditional fields
    $('input[name="school_pay_status"]').on('change', function() {
        const isEnabled = $(this).val() === 'Yes';
        $('#schoolpay-fields').toggle(isEnabled);
        
        // Update required attributes
        $('#school_pay_code, #school_pay_password').attr('required', isEnabled);
    });
    
    // Form submission
    $('#step3Form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading state
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: '{{ url("onboarding/step3/process") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    window.location.href = response.next_step;
                } else {
                    // Display validation errors
                    $('.error-message').text('');
                    if (response.errors) {
                        $.each(response.errors, function(field, messages) {
                            $(`#${field}_error`).text(messages[0]);
                        });
                    }
                    $('#submitBtn').prop('disabled', false).html('Continue <i class="fas fa-arrow-right"></i>');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $('#submitBtn').prop('disabled', false).html('Continue <i class="fas fa-arrow-right"></i>');
            }
        });
    });
    
    // Initialize
    updateNavigationButtons();
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Initialize first tab as active
    if (tabButtons.length > 0 && tabContents.length > 0) {
        tabButtons[0].classList.add('active');
        tabContents[0].classList.add('active');
    }
    
    // Tab switching functionality
    tabButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            // Remove active class from all tabs and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            button.classList.add('active');
            if (tabContents[index]) {
                tabContents[index].classList.add('active');
            }
            
            // Update navigation buttons
            updateTabNavigation();
        });
    });
    
    // Update tab navigation button visibility
    function updateTabNavigation() {
        const activeIndex = Array.from(tabButtons).findIndex(btn => btn.classList.contains('active'));
        const prevBtn = document.getElementById('prevTabBtn');
        const nextBtn = document.getElementById('nextTabBtn');
        
        if (prevBtn && nextBtn) {
            // Show/hide previous button
            prevBtn.style.display = activeIndex > 0 ? 'flex' : 'none';
            
            // Update next button text for last tab
            if (activeIndex === tabButtons.length - 1) {
                nextBtn.innerHTML = 'Complete Setup <i class="bx bx-check"></i>';
                nextBtn.onclick = function() {
                    document.getElementById('submitBtn').click();
                };
            } else {
                nextBtn.innerHTML = 'Next Section <i class="bx bx-arrow-right"></i>';
                nextBtn.onclick = nextTab;
            }
        }
    }
    
    // Next/Previous tab navigation
    window.nextTab = function() {
        const activeIndex = Array.from(tabButtons).findIndex(btn => btn.classList.contains('active'));
        if (activeIndex < tabButtons.length - 1) {
            const nextIndex = activeIndex + 1;
            tabButtons[nextIndex].click();
        }
    };
    
    window.prevTab = function() {
        const activeIndex = Array.from(tabButtons).findIndex(btn => btn.classList.contains('active'));
        if (activeIndex > 0) {
            const prevIndex = activeIndex - 1;
            tabButtons[prevIndex].click();
        }
    };
    
    // Initialize navigation
    updateTabNavigation();
    
    // School code auto-generation
    const schoolNameInput = document.getElementById('school_name');
    const schoolCodeInput = document.getElementById('school_code');
    
    if (schoolNameInput && schoolCodeInput) {
        schoolNameInput.addEventListener('input', function() {
            if (!schoolCodeInput.value.trim()) {
                const name = this.value.trim();
                const code = name
                    .toUpperCase()
                    .replace(/[^A-Z0-9\s]/g, '')
                    .split(' ')
                    .map(word => word.substring(0, 3))
                    .join('')
                    .substring(0, 10);
                
                if (code) {
                    schoolCodeInput.value = code;
                }
            }
        });
    }
    
    // License key formatting
    const licenseKeyInput = document.getElementById('license_key');
    if (licenseKeyInput) {
        licenseKeyInput.addEventListener('input', function() {
            let value = this.value.replace(/[^A-Z0-9]/g, '');
            value = value.replace(/(.{4})/g, '$1-').replace(/-$/, '');
            this.value = value;
        });
    }
});
</script>
@endpush

@endsection
