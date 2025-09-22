<?php
use App\Models\Utils;
// Ensure company data is available
if (!isset($company)) {
    $company = Utils::company();
}
?>
@extends('layouts.onboarding')

@section('title', 'Review Information - ' . ($company->app_name ?? Utils::app_name()))
@section('meta_description', 'Review and confirm your registration details before completing the process.')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Review & Confirm</h2>
        <p class="progress-description">
            Verify your information before finalizing registration.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator active">4</div>
        <span>Confirmation</span>
    </div>
@endsection

@section('content')
    <div class="content-title">Review Your Information</div>
    <div class="content-description">
        Please verify all details are correct before proceeding.
    </div>
    
    <div id="reviewContent">
        <!-- Administrator Information -->
        <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center;">
                <i class='bx bx-user' style="margin-right: 0.5rem;"></i>
                Administrator Information
            </h3>
            <div style="display: grid; gap: 0.75rem; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Name:</span>
                    <span style="color: var(--text-dark); font-weight: 500;">{{ $userData['first_name'] ?? '' }} {{ $userData['last_name'] ?? '' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Email:</span>
                    <span style="color: var(--text-dark);">{{ $userData['email'] ?? '' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Phone:</span>
                    <span style="color: var(--text-dark);">{{ $userData['phone_number'] ?? '' }}</span>
                </div>
            </div>
            <a href="{{ route('onboarding.step2') }}" style="color: var(--primary-color); font-size: 0.85rem; text-decoration: none; margin-top: 0.75rem; display: inline-block;">
                <i class='bx bx-edit' style="margin-right: 0.25rem;"></i>
                Edit Administrator Info
            </a>
        </div>
        
        <!-- Basic School Information -->
        <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center;">
                <i class='bx bx-building' style="margin-right: 0.5rem;"></i>
                Basic School Information
            </h3>
            <div style="display: grid; gap: 0.75rem; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">School Name:</span>
                    <span style="color: var(--text-dark); font-weight: 500;">{{ $enterpriseData['school_name'] ?? '' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Short Name:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_short_name'] ?? '' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">School Type:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_type'] ?? '' }}</span>
                </div>
                @if(!empty($enterpriseData['school_motto']))
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Motto:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_motto'] }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Religious Studies:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['has_theology'] ?? 'No' }}</span>
                </div>
                @if(!empty($enterpriseData['logo_path']))
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Logo:</span>
                    <span style="color: var(--success-color);">✓ Uploaded</span>
                </div>
                @endif
                @if(!empty($enterpriseData['welcome_message']))
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <span style="color: var(--text-light);">Welcome Message:</span>
                    <span style="color: var(--text-dark); text-align: right; max-width: 60%;">{{ Str::limit($enterpriseData['welcome_message'], 50) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Contact Information -->
        <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center;">
                <i class='bx bx-phone' style="margin-right: 0.5rem;"></i>
                Contact Information
            </h3>
            <div style="display: grid; gap: 0.75rem; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Email:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_email'] ?? '' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Primary Phone:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_phone'] ?? '' }}</span>
                </div>
                @if(!empty($enterpriseData['school_phone_2']))
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Secondary Phone:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_phone_2'] }}</span>
                </div>
                @endif
                @if(!empty($enterpriseData['school_website']))
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Website:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_website'] }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <span style="color: var(--text-light);">Address:</span>
                    <span style="color: var(--text-dark); text-align: right; max-width: 60%;">{{ $enterpriseData['school_address'] ?? '' }}</span>
                </div>
            </div>
        </div>

        <!-- Administrative Information -->
        <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center;">
                <i class='bx bx-user-circle' style="margin-right: 0.5rem;"></i>
                Administrative Information
            </h3>
            <div style="display: grid; gap: 0.75rem; font-size: 0.9rem;">
                @if(!empty($enterpriseData['hm_name']))
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Head Teacher/Principal:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['hm_name'] }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Branding & System Settings -->
        <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center;">
                <i class='bx bx-palette' style="margin-right: 0.5rem;"></i>
                Branding & System Settings
            </h3>
            <div style="display: grid; gap: 0.75rem; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-light);">Primary Color:</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; background: {{ $enterpriseData['primary_color'] ?? '#007bff' }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                        <span style="color: var(--text-dark);">{{ $enterpriseData['primary_color'] ?? '#007bff' }}</span>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-light);">Secondary Color:</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; background: {{ $enterpriseData['secondary_color'] ?? '#6c757d' }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                        <span style="color: var(--text-dark);">{{ $enterpriseData['secondary_color'] ?? '#6c757d' }}</span>
                    </div>
                </div>
                @if(!empty($enterpriseData['subdomain']))
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">Subdomain:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['subdomain'] }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Financial & License Information -->
        <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center;">
                <i class='bx bx-credit-card' style="margin-right: 0.5rem;"></i>
                Financial & License Information
            </h3>
            <div style="display: grid; gap: 0.75rem; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">SchoolPay Integration:</span>
                    <span style="color: {{ ($enterpriseData['school_pay_status'] ?? 'No') === 'Yes' ? 'var(--success-color)' : 'var(--text-light)' }};">
                        {{ ($enterpriseData['school_pay_status'] ?? 'No') === 'Yes' ? '✓ Enabled' : 'Disabled' }}
                    </span>
                </div>
                @if(($enterpriseData['school_pay_status'] ?? 'No') === 'Yes')
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">SchoolPay Email:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_pay_email'] ?? 'Not provided' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">SchoolPay Phone:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['school_pay_phone'] ?? 'Not provided' }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">License Status:</span>
                    <span style="color: {{ ($enterpriseData['has_valid_lisence'] ?? 'Yes') === 'Yes' ? 'var(--success-color)' : 'var(--error-color)' }};">
                        {{ ($enterpriseData['has_valid_lisence'] ?? 'Yes') === 'Yes' ? '✓ Valid License' : '✗ Invalid License' }}
                    </span>
                </div>
                @if(!empty($enterpriseData['license_expire_date']))
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-light);">License Expires:</span>
                    <span style="color: var(--text-dark);">{{ $enterpriseData['license_expire_date'] }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <a href="{{ route('onboarding.step3') }}" style="color: var(--primary-color); font-size: 0.85rem; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
            <i class='bx bx-edit' style="margin-right: 0.25rem;"></i>
            Edit School Information
        </a>
        
        <!-- Terms and Conditions -->
        <div style="background: var(--background-white); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;">
                <input type="checkbox" id="termsAccepted" style="margin-top: 0.125rem;" required>
                <span style="color: var(--text-dark); font-size: 0.9rem; line-height: 1.4;">
                    I confirm that all the information provided is accurate and I agree to the 
                    <a href="#" style="color: var(--primary-color); text-decoration: none;">Terms of Service</a> 
                    and 
                    <a href="#" style="color: var(--primary-color); text-decoration: none;">Privacy Policy</a>.
                </span>
            </label>
        </div>
    </div>
    
    <div id="processingMessage" style="display: none; text-align: center; padding: 2rem;">
        <div style="color: var(--primary-color); font-size: 2rem; margin-bottom: 1rem;">
            <i class='bx bx-loader bx-spin'></i>
        </div>
        <h3 style="color: var(--text-dark); margin-bottom: 0.5rem;">Creating Your School Account...</h3>
        <p style="color: var(--text-light); font-size: 0.9rem;">This may take a few moments. Please don't close this page.</p>
    </div>
    
    <div style="display: flex; gap: 1rem; margin-top: 2rem;" id="navigationButtons">
        <a href="{{ route('onboarding.step3') }}" class="btn btn-secondary" style="flex: 1;">
            <i class='bx bx-arrow-back'></i>
            Back
        </a>
        <button type="button" class="btn btn-primary" style="flex: 2;" id="confirmBtn" onclick="confirmRegistration()">
            <i class='bx bx-check'></i>
            Confirm & Create Account
        </button>
    </div>
@endsection

@push('styles')
<style>
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.processing {
    opacity: 0.5;
    pointer-events: none;
}
</style>
@endpush

@push('scripts')
<script>
function confirmRegistration() {
    const termsAccepted = document.getElementById('termsAccepted').checked;
    
    if (!termsAccepted) {
        alert('Please accept the terms and conditions to continue.');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmBtn');
    const reviewContent = document.getElementById('reviewContent');
    const processingMessage = document.getElementById('processingMessage');
    const navigationButtons = document.getElementById('navigationButtons');
    
    // Show processing state
    reviewContent.style.display = 'none';
    navigationButtons.style.display = 'none';
    processingMessage.style.display = 'block';
    
    // Submit registration
    fetch('{{ route('onboarding.process4') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            terms_accepted: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Registration successful, redirect to final step
            window.location.href = data.next_step;
        } else {
            // Show error message
            alert('Registration failed: ' + (data.message || 'Unknown error occurred'));
            
            // Restore UI
            reviewContent.style.display = 'block';
            navigationButtons.style.display = 'flex';
            processingMessage.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during registration. Please try again.');
        
        // Restore UI
        reviewContent.style.display = 'block';
        navigationButtons.style.display = 'flex';
        processingMessage.style.display = 'none';
    });
}

// Auto-check terms on page load for better UX (optional)
document.addEventListener('DOMContentLoaded', function() {
    // Could auto-check terms if desired, but better to require explicit action
    // document.getElementById('termsAccepted').checked = true;
});
</script>
@endpush
