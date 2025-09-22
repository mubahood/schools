<?php
use App\Models\Utils;
// Ensure company data is available
if (!isset($company)) {
    $company = Utils::company();
}
?>
@extends('layouts.onboarding')

@section('title', 'Personal Information - ' . ($company->app_name ?? Utils::app_name()))
@section('meta_description', 'Provide your personal information to create your administrator account.')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Personal Information</h2>
        <p class="progress-description">
            Tell us about yourself as the school administrator.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator active">2</div>
        <span>User Details</span>
    </div>
@endsection

@section('content')
    <div class="content-title">Your Administrator Account</div>
    <div class="content-description">
        This will be your login account to manage the school.
    </div>
    
    <form id="step2Form" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label" for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name" class="form-input" required placeholder="Enter your first name">
            <div class="error-message" id="first_name_error"></div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name" class="form-input" required placeholder="Enter your last name">
            <div class="error-message" id="last_name_error"></div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="email">Email Address *</label>
            <input type="email" id="email" name="email" class="form-input" required placeholder="Enter your email address">
            <div class="validation-message" id="email_validation"></div>
            <div class="error-message" id="email_error"></div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="phone_number">Phone Number *</label>
            <input type="tel" id="phone_number" name="phone_number" class="form-input" required placeholder="Enter your phone number">
            <div class="validation-message" id="phone_validation"></div>
            <div class="error-message" id="phone_number_error"></div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="password">Password *</label>
            <input type="password" id="password" name="password" class="form-input" required placeholder="Create a strong password">
            <div class="error-message" id="password_error"></div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirm Password *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required placeholder="Confirm your password">
            <div class="error-message" id="password_confirmation_error"></div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <a href="{{ route('onboarding.step1') }}" class="btn btn-secondary" style="flex: 1;">
                <i class='bx bx-arrow-back'></i>
                Back
            </a>
            <button type="submit" class="btn btn-primary" style="flex: 2;" id="submitBtn">
                Continue
                <i class='bx bx-arrow-right'></i>
            </button>
        </div>
    </form>
@endsection

@push('styles')
<style>
.error-message {
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.25rem;
    display: none;
}

.validation-message {
    font-size: 0.8rem;
    margin-top: 0.25rem;
    display: none;
}

.validation-message.available {
    color: #10b981;
}

.validation-message.unavailable {
    color: #ef4444;
}

.form-input.error {
    border-color: #ef4444;
}

.form-input.success {
    border-color: #10b981;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script>
// Store form data in sessionStorage
function saveFormData() {
    const formData = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        email: document.getElementById('email').value,
        phone_number: document.getElementById('phone_number').value,
        password: document.getElementById('password').value,
        password_confirmation: document.getElementById('password_confirmation').value
    };
    sessionStorage.setItem('onboarding_step2_data', JSON.stringify(formData));
}

// Load form data from sessionStorage
function loadFormData() {
    const savedData = sessionStorage.getItem('onboarding_step2_data');
    if (savedData) {
        const formData = JSON.parse(savedData);
        Object.keys(formData).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = formData[key];
            }
        });
    }
}

// Real-time validation
let emailTimeout, phoneTimeout;

document.getElementById('email').addEventListener('input', function() {
    clearTimeout(emailTimeout);
    emailTimeout = setTimeout(() => {
        validateEmail(this.value);
    }, 500);
    saveFormData();
});

document.getElementById('phone_number').addEventListener('input', function() {
    clearTimeout(phoneTimeout);
    phoneTimeout = setTimeout(() => {
        validatePhone(this.value);
    }, 500);
    saveFormData();
});

// Save data on input
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('input', saveFormData);
});

function validateEmail(email) {
    if (!email || email.length < 5) return;
    
    fetch(`{{ route('onboarding.validate.email') }}?email=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(data => {
            const element = document.getElementById('email_validation');
            const input = document.getElementById('email');
            
            element.style.display = 'block';
            element.textContent = data.message;
            
            if (data.available) {
                element.className = 'validation-message available';
                input.className = 'form-input success';
            } else {
                element.className = 'validation-message unavailable';
                input.className = 'form-input error';
            }
        });
}

function validatePhone(phone) {
    if (!phone || phone.length < 8) return;
    
    fetch(`{{ route('onboarding.validate.phone') }}?phone=${encodeURIComponent(phone)}`)
        .then(response => response.json())
        .then(data => {
            const element = document.getElementById('phone_validation');
            const input = document.getElementById('phone_number');
            
            element.style.display = 'block';
            element.textContent = data.message;
            
            if (data.available) {
                element.className = 'validation-message available';
                input.className = 'form-input success';
            } else {
                element.className = 'validation-message unavailable';
                input.className = 'form-input error';
            }
        });
}

// Form submission
document.getElementById('step2Form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Processing...';
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.form-input').forEach(el => el.classList.remove('error'));
    
    const formData = new FormData(this);
    
    fetch('{{ route('onboarding.process2') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear session storage as data is now saved on server
            sessionStorage.removeItem('onboarding_step2_data');
            window.location.href = data.next_step;
        } else {
            // Display validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorElement = document.getElementById(field + '_error');
                    const inputElement = document.getElementById(field);
                    
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                        errorElement.style.display = 'block';
                    }
                    
                    if (inputElement) {
                        inputElement.classList.add('error');
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Continue <i class="bx bx-arrow-right"></i>';
    });
});

// Load saved data on page load
document.addEventListener('DOMContentLoaded', loadFormData);
</script>
@endpush
