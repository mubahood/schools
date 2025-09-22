<?php
use App\Models\Utils;
// Ensure company data is available
if (!isset($company)) {
    $company = Utils::company();
}
?>
@extends('layouts.onboarding')

@section('title', 'School Information - ' . ($company->app_name ?? Utils::app_name()))

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">School Information</h2>
        <p class="progress-description">
            Provide your school details and configuration.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator active">3</div>
        <span>School Details</span>
    </div>
@endsection

@section('content')
    <div class="content-title">School Information</div>
    <div class="content-description">
        Complete your school information to set up your management system.
    </div>
    
    <form id="step3Form" method="POST">
        @csrf
        
        <div class="form-section-header">
            <i class='bx bx-info-circle'></i>
            <span>Basic School Information</span>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="school_name">School Name *</label>
            <input type="text" id="school_name" name="school_name" class="form-input" required
                   value="{{ old('school_name', session('onboarding.step3.school_name', '')) }}"
                   placeholder="Enter the full official name of your school">
            <div class="form-help">This will appear on all official documents and reports</div>
            <div class="error-message" id="school_name_error"></div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="school_short_name">Short Name *</label>
                <input type="text" id="school_short_name" name="school_short_name" class="form-input" required
                       value="{{ old('school_short_name', session('onboarding.step3.school_short_name', '')) }}"
                       placeholder="Auto-generated from school name"
                       maxlength="10">
                <div class="form-help">e.g., SMS for St. Mary's School</div>
                <div class="error-message" id="school_short_name_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="founded_year">Year Founded</label>
                <input type="number" id="founded_year" name="founded_year" class="form-input"
                       value="{{ old('founded_year', session('onboarding.step3.founded_year', '')) }}"
                       min="1800" max="2025" placeholder="e.g., 1995">
                <div class="form-help">Year the school was established</div>
                <div class="error-message" id="founded_year_error"></div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">School Type *</label>
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="school_type" value="Primary" required
                           {{ (old('school_type', session('onboarding.step3.school_type')) == 'Primary') ? 'checked' : '' }}>
                    <span class="radio-label">
                        <strong>Primary School</strong>
                        <small>Elementary education (P1-P7)</small>
                    </span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="school_type" value="Secondary" required
                           {{ (old('school_type', session('onboarding.step3.school_type')) == 'Secondary') ? 'checked' : '' }}>
                    <span class="radio-label">
                        <strong>Secondary School</strong>
                        <small>Ordinary Level education (S1-S4)</small>
                    </span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="school_type" value="Advanced" required
                           {{ (old('school_type', session('onboarding.step3.school_type')) == 'Advanced') ? 'checked' : '' }}>
                    <span class="radio-label">
                        <strong>Advanced School</strong>
                        <small>Complete secondary education (S1-S6)</small>
                    </span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="school_type" value="University" required
                           {{ (old('school_type', session('onboarding.step3.school_type')) == 'University') ? 'checked' : '' }}>
                    <span class="radio-label">
                        <strong>University/Tertiary</strong>
                        <small>Higher education institution</small>
                    </span>
                </label>
            </div>
            <div class="form-help">Select the type that best describes your educational institution</div>
            <div class="error-message" id="school_type_error"></div>
        </div>

        <div class="form-group">
            <label class="form-label">Religious Studies/Theology *</label>
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="has_theology" value="Yes" required
                           {{ (old('has_theology', session('onboarding.step3.has_theology', 'No')) == 'Yes') ? 'checked' : '' }}>
                    <span class="radio-label">
                        <strong>Yes</strong>
                        <small>School offers religious/theology subjects</small>
                    </span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="has_theology" value="No" required
                           {{ (old('has_theology', session('onboarding.step3.has_theology', 'No')) == 'No') ? 'checked' : '' }}>
                    <span class="radio-label">
                        <strong>No</strong>
                        <small>School does not offer religious studies</small>
                    </span>
                </label>
            </div>
            <div class="form-help">Does your school offer religious/theology subjects or studies?</div>
            <div class="error-message" id="has_theology_error"></div>
        </div>

        <!-- School Branding -->
        <div class="form-section-header">
            <i class='bx bx-palette'></i>
            <span>School Branding</span>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="primary_color">Primary School Color *</label>
            <div class="color-picker-wrapper">
                <input type="color" id="primary_color" name="primary_color" class="form-input color-input" required
                       value="{{ old('primary_color', session('onboarding.step3.primary_color', '#00b4fa')) }}">
                <input type="text" id="primary_color_text" class="form-input color-text" 
                       value="{{ old('primary_color', session('onboarding.step3.primary_color', '#00b4fa')) }}"
                       placeholder="#00b4fa" maxlength="7">
                <div class="color-preview" id="color_preview"></div>
            </div>
            <div class="form-help">Choose the main color that represents your school brand</div>
            <div class="error-message" id="primary_color_error"></div>
        </div>

        <!-- Contact Information -->
        <div class="form-section-header">
            <i class='bx bx-phone'></i>
            <span>Contact Information</span>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="school_phone">Phone Number *</label>
                <input type="tel" id="school_phone" name="school_phone" class="form-input" required
                       value="{{ old('school_phone', session('onboarding.step3.school_phone', '')) }}"
                       placeholder="Enter main contact number">
                <div class="form-help">Main contact number for the school</div>
                <div class="error-message" id="school_phone_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="school_email">Email Address *</label>
                <input type="email" id="school_email" name="school_email" class="form-input" required
                       value="{{ old('school_email', session('onboarding.step3.school_email', '')) }}"
                       placeholder="info@yourschool.com">
                <div class="form-help">Official school email address</div>
                <div class="error-message" id="school_email_error"></div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="school_address">School Address *</label>
            <textarea id="school_address" name="school_address" class="form-input" rows="3" required
                      placeholder="Enter complete school address including street, city, and postal code">{{ old('school_address', session('onboarding.step3.school_address', '')) }}</textarea>
            <div class="form-help">Complete physical address of the school</div>
            <div class="error-message" id="school_address_error"></div>
        </div>

        <div class="form-section-header">
            <i class='bx bx-user-pin'></i>
            <span>Administrative Information</span>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="hm_name">Head Teacher Name</label>
                <input type="text" id="hm_name" name="hm_name" class="form-input"
                       value="{{ old('hm_name', session('onboarding.step3.hm_name', '')) }}"
                       placeholder="Enter name of head teacher or principal">
                <div class="form-help">Name of the current head teacher or principal</div>
                <div class="error-message" id="hm_name_error"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="hm_phone">Head Teacher Phone</label>
                <input type="tel" id="hm_phone" name="hm_phone" class="form-input"
                       value="{{ old('hm_phone', session('onboarding.step3.hm_phone', '')) }}"
                       placeholder="Enter head teacher's contact number">
                <div class="form-help">Direct contact for the head teacher</div>
                <div class="error-message" id="hm_phone_error"></div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ url('onboarding/step2') }}" class="btn btn-outline">
                <i class="bx bx-arrow-back"></i>
                Back
            </a>
            
            <button type="submit" class="btn btn-primary">
                Continue
                <i class="bx bx-arrow-to-right"></i>
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === SESSION SAVING FUNCTIONALITY ===
    // Save form data to session on every input change
    const formInputs = document.querySelectorAll('#step3Form input, #step3Form textarea, #step3Form select');
    
    // Function to save individual field to session via AJAX
    function saveToSession(fieldName, fieldValue) {
        fetch('{{ route("onboarding.save-session") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                step: 'step3',
                field: fieldName,
                value: fieldValue
            })
        }).catch(error => {
            console.warn('Session save failed:', error);
        });
    }
    
    // Add event listeners to save data on change
    formInputs.forEach(input => {
        const eventType = input.type === 'radio' || input.type === 'checkbox' ? 'change' : 'input';
        
        input.addEventListener(eventType, function() {
            let value = this.value;
            
            // Handle radio buttons - get the checked value
            if (this.type === 'radio') {
                value = document.querySelector(`input[name="${this.name}"]:checked`)?.value || '';
            }
            
            // Save to session
            saveToSession(this.name, value);
            
            // Show visual feedback
            this.style.borderLeft = '3px solid var(--accent-color)';
            setTimeout(() => {
                this.style.borderLeft = '';
            }, 1000);
        });
    });
    
    // === AUTO-GENERATION FUNCTIONALITY ===
    // Auto-generate short name from school name
    const schoolNameInput = document.getElementById('school_name');
    const shortNameInput = document.getElementById('school_short_name');
    
    if (schoolNameInput && shortNameInput) {
        schoolNameInput.addEventListener('input', function() {
            if (!shortNameInput.value || shortNameInput.dataset.autoGenerated === 'true') {
                const words = this.value.split(' ');
                let shortName = '';
                
                for (let word of words) {
                    if (word.length > 0) {
                        shortName += word.charAt(0).toUpperCase();
                    }
                }
                
                shortNameInput.value = shortName.substring(0, 10);
                shortNameInput.dataset.autoGenerated = 'true';
                
                // Save auto-generated value to session
                saveToSession('school_short_name', shortNameInput.value);
            }
        });
        
        shortNameInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
    }
    
    // === COLOR PICKER FUNCTIONALITY ===
    const colorInput = document.getElementById('primary_color');
    const colorText = document.getElementById('primary_color_text');
    const colorPreview = document.getElementById('color_preview');
    
    if (colorInput && colorText && colorPreview) {
        // Function to update color preview
        function updateColorPreview(color) {
            colorPreview.style.backgroundColor = color;
            colorPreview.classList.add('selected');
            
            // Update text input if it wasn't the source of change
            if (colorText.value !== color) {
                colorText.value = color;
            }
            
            // Update color input if it wasn't the source of change
            if (colorInput.value !== color) {
                colorInput.value = color;
            }
        }
        
        // Initialize color preview
        updateColorPreview(colorInput.value);
        
        // Handle color picker change
        colorInput.addEventListener('input', function() {
            updateColorPreview(this.value);
            saveToSession('primary_color', this.value);
        });
        
        // Handle text input change
        colorText.addEventListener('input', function() {
            let color = this.value;
            
            // Validate hex color format
            if (color.match(/^#[0-9A-Fa-f]{6}$/)) {
                updateColorPreview(color);
                saveToSession('primary_color', color);
            } else if (color.match(/^[0-9A-Fa-f]{6}$/)) {
                color = '#' + color;
                this.value = color;
                updateColorPreview(color);
                saveToSession('primary_color', color);
            }
        });
        
        // Format text input on blur
        colorText.addEventListener('blur', function() {
            if (!this.value.startsWith('#') && this.value.length === 6) {
                this.value = '#' + this.value;
                updateColorPreview(this.value);
            }
        });
    }
    
    // === FORM VALIDATION AND SUBMISSION ===
    const form = document.getElementById('step3Form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            // Validate required fields
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    const errorElement = document.getElementById(field.name + '_error');
                    if (errorElement) {
                        errorElement.textContent = 'This field is required';
                    }
                    field.style.borderColor = 'var(--error)';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            // Validate email format
            const emailField = document.getElementById('school_email');
            if (emailField && emailField.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailField.value)) {
                    const errorElement = document.getElementById('school_email_error');
                    if (errorElement) {
                        errorElement.textContent = 'Please enter a valid email address';
                    }
                    emailField.style.borderColor = 'var(--error)';
                    isValid = false;
                }
            }
            
            // Validate color format
            const colorField = document.getElementById('primary_color_text');
            if (colorField && colorField.value) {
                if (!colorField.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                    const errorElement = document.getElementById('primary_color_error');
                    if (errorElement) {
                        errorElement.textContent = 'Please enter a valid hex color (e.g., #00b4fa)';
                    }
                    colorField.style.borderColor = 'var(--error)';
                    isValid = false;
                }
            }
            
            if (isValid) {
                // Show loading state
                const submitButton = form.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
                submitButton.disabled = true;
                
                // Submit form data via AJAX
                const formData = new FormData(form);
                
                fetch(form.action || '{{ route("onboarding.process3") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success - redirect to next step
                        window.location.href = data.next_step || '{{ url("onboarding/step4") }}';
                    } else {
                        // Handle validation errors
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                        
                        let errorMessages = [];
                        let fieldErrorCount = 0;
                        
                        if (data.errors) {
                            // Display field-specific validation errors
                            Object.keys(data.errors).forEach(fieldName => {
                                const errorElement = document.getElementById(fieldName + '_error');
                                const fieldElement = document.getElementById(fieldName) || document.querySelector(`[name="${fieldName}"]`);
                                
                                if (errorElement) {
                                    errorElement.textContent = data.errors[fieldName][0];
                                    errorElement.style.display = 'block';
                                    errorElement.style.color = 'var(--error)';
                                    errorElement.style.fontWeight = '500';
                                    fieldErrorCount++;
                                }
                                if (fieldElement) {
                                    fieldElement.style.borderColor = 'var(--error)';
                                    fieldElement.style.borderWidth = '2px';
                                }
                                
                                // Collect error messages for summary
                                const fieldLabel = document.querySelector(`label[for="${fieldName}"]`)?.textContent?.replace('*', '').trim() || fieldName;
                                errorMessages.push(`${fieldLabel}: ${data.errors[fieldName][0]}`);
                            });
                        }
                        
                        // Create comprehensive error message
                        let errorNotificationMessage = '';
                        if (errorMessages.length > 0) {
                            if (errorMessages.length === 1) {
                                errorNotificationMessage = errorMessages[0];
                            } else {
                                errorNotificationMessage = `${fieldErrorCount} validation errors found:\n• ${errorMessages.join('\n• ')}`;
                            }
                        } else if (data.message) {
                            errorNotificationMessage = data.message;
                        } else {
                            errorNotificationMessage = 'Validation failed. Please check the highlighted fields.';
                        }
                        
                        // Show detailed error notification
                        showErrorNotification(errorNotificationMessage);
                        
                        // Scroll to first error field
                        const firstErrorField = form.querySelector('[style*="border-color: var(--error)"]');
                        if (firstErrorField) {
                            setTimeout(() => {
                                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                firstErrorField.focus();
                            }, 500);
                        }
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                    showErrorNotification('An error occurred while saving. Please try again.');
                });
            } else {
                // Scroll to first error
                const firstError = form.querySelector('[style*="border-color: var(--error)"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
    
    // === VISUAL FEEDBACK ===
    // Add success animation when fields are filled correctly
    formInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() && this.checkValidity()) {
                this.style.borderLeft = '3px solid var(--accent-color)';
                setTimeout(() => {
                    this.style.borderLeft = '';
                }, 2000);
            }
        });
    });
    
    // Auto-save notification
    let saveTimeout;
    window.showSaveNotification = function() {
        clearTimeout(saveTimeout);
        
        // Create or update notification
        let notification = document.getElementById('save-notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'save-notification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--accent-color);
                color: white;
                padding: 10px 15px;
                border-radius: var(--radius-none);
                font-size: var(--font-size-sm);
                font-weight: 600;
                z-index: 1000;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
            document.body.appendChild(notification);
        }
        
        notification.textContent = '✓ Progress saved';
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
        
        saveTimeout = setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
        }, 2000);
    };
    
    // Clear field errors function
    window.clearFieldErrors = function() {
        // Clear all error elements
        const errorElements = document.querySelectorAll('[id$="_error"]');
        errorElements.forEach(element => {
            element.style.display = 'none';
            element.textContent = '';
        });
        
        // Reset border styles for form inputs
        const formInputs = form.querySelectorAll('input, select, textarea');
        formInputs.forEach(input => {
            input.style.borderColor = '';
            input.style.borderWidth = '';
        });
    };

    // Error notification function
    window.showErrorNotification = function(message) {
        // Clear any existing error notification
        const existingNotification = document.getElementById('error-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.id = 'error-notification';
        
        // Enhanced styling for better readability
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
            z-index: 10000;
            font-weight: 500;
            font-size: 14px;
            line-height: 1.4;
            max-width: 400px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            white-space: pre-line;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
        `;
        
        // Enhanced icon and message formatting
        const isMultipleErrors = message.includes('\n•');
        const icon = isMultipleErrors ? '⚠️' : '❌';
        const title = isMultipleErrors ? 'Validation Errors' : 'Validation Error';
        
        notification.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <span style="font-size: 18px; flex-shrink: 0; margin-top: 2px;">${icon}</span>
                <div>
                    <div style="font-weight: 600; margin-bottom: 8px; font-size: 15px;">${title}</div>
                    <div style="opacity: 0.95;">${message}</div>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Enhanced animation
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto remove after longer duration for multiple errors
        const duration = isMultipleErrors ? 8000 : 5000;
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 400);
        }, duration);
        
        // Click to dismiss
        notification.addEventListener('click', () => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 400);
        });
    };
});
</script>
@endpush
