@extends('layouts.onboarding')

@section('title', 'Email Verification - ' . \App\Models\Utils::app_name())

@section('content')
<div class="onboarding-container">
    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step active" data-step="1">
            <div class="step-circle">
                <i class='bx bx-check'></i>
            </div>
            <span>Create Account</span>
        </div>
        <div class="step active" data-step="2">
            <div class="step-circle">
                <i class='bx bx-mail-send'></i>
            </div>
            <span>Verify Email</span>
        </div>
        <div class="step" data-step="3">
            <div class="step-circle">3</div>
            <span>School Details</span>
        </div>
        <div class="step" data-step="4">
            <div class="step-circle">4</div>
            <span>Setup Classes</span>
        </div>
        <div class="step" data-step="5">
            <div class="step-circle">5</div>
            <span>Complete</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="onboarding-content">
        <div class="onboarding-header">
            <h1>Verify Your Email Address</h1>
            <p>We've sent a verification link to <strong>{{ $user->email }}</strong></p>
        </div>

        <div class="verification-container">
            <!-- Verification Status -->
            <div class="verification-status">
                @if($wizard->email_is_verified === 'Yes')
                    <div class="status-card verified">
                        <div class="status-icon">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <h3>Email Verified!</h3>
                        <p>Your email address has been successfully verified.</p>
                        <a href="{{ route('onboarding.step3') }}" class="btn btn-primary">
                            Continue to School Setup <i class='bx bx-right-arrow-alt'></i>
                        </a>
                    </div>
                @else
                    <div class="status-card pending">
                        <div class="status-icon">
                            <i class='bx bx-mail-send'></i>
                        </div>
                        <h3>Check Your Email</h3>
                        <p>We've sent a verification link to your email address. Click the link in the email to verify your account.</p>
                        
                        <div class="verification-actions">
                            <button id="sendVerificationBtn" class="btn btn-primary">
                                <i class='bx bx-paper-plane'></i>
                                Send Verification Email
                            </button>
                            
                            <button id="resendVerificationBtn" class="btn btn-outline" style="display: none;">
                                <i class='bx bx-refresh'></i>
                                Resend Email
                            </button>
                        </div>

                        <!-- Progress Indicator -->
                        <div class="verification-progress" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <p>Sending email...</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Help Section -->
            <div class="help-section">
                <h4>Didn't receive the email?</h4>
                <ul>
                    <li>Check your spam or junk folder</li>
                    <li>Make sure {{ $user->email }} is correct</li>
                    <li>Try resending the verification email</li>
                </ul>
                
                <div class="change-email">
                    <p>Need to change your email address?</p>
                    <a href="{{ route('onboarding.step2') }}" class="link">Update email address</a>
                </div>
            </div>

            <!-- Admin Override (for testing) -->
            @if(config('app.debug'))
                <div class="debug-section">
                    <h4>Debug Options</h4>
                    <button id="markVerifiedBtn" class="btn btn-outline btn-sm">
                        <i class='bx bx-check'></i>
                        Mark as Verified (Debug)
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .verification-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .status-card {
        background: white;
        border-radius: 16px;
        padding: 40px;
        text-align: center;
        box-shadow: var(--shadow-md);
        border: 2px solid transparent;
        margin-bottom: 30px;
    }

    .status-card.verified {
        border-color: var(--accent-color);
        background: linear-gradient(135deg, rgba(54, 202, 120, 0.05) 0%, rgba(54, 202, 120, 0.1) 100%);
    }

    .status-card.pending {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, rgba(0, 180, 250, 0.05) 0%, rgba(0, 180, 250, 0.1) 100%);
    }

    .status-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        color: var(--primary-color);
    }

    .verified .status-icon {
        color: var(--accent-color);
    }

    .status-card h3 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: var(--text-primary);
    }

    .status-card p {
        font-size: 1.1rem;
        color: var(--text-secondary);
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .verification-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }

    .verification-progress {
        margin: 20px 0;
    }

    .progress-bar {
        width: 100%;
        height: 6px;
        background: rgba(0, 180, 250, 0.1);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .progress-fill {
        height: 100%;
        background: var(--primary-color);
        width: 0%;
        border-radius: 3px;
        animation: progressAnimation 2s ease-in-out infinite;
    }

    @keyframes progressAnimation {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 100%; }
    }

    .help-section {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 12px;
        padding: 25px;
        border: 1px solid rgba(0, 180, 250, 0.1);
    }

    .help-section h4 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: var(--text-primary);
    }

    .help-section ul {
        list-style: none;
        padding: 0;
        margin-bottom: 20px;
    }

    .help-section li {
        padding: 8px 0;
        padding-left: 25px;
        position: relative;
        color: var(--text-secondary);
    }

    .help-section li::before {
        content: '•';
        color: var(--primary-color);
        font-weight: bold;
        position: absolute;
        left: 0;
        font-size: 1.2rem;
    }

    .change-email {
        padding-top: 20px;
        border-top: 1px solid rgba(0, 180, 250, 0.1);
        text-align: center;
    }

    .debug-section {
        margin-top: 30px;
        padding: 20px;
        background: rgba(255, 193, 7, 0.1);
        border-radius: 8px;
        border: 1px solid rgba(255, 193, 7, 0.3);
        text-align: center;
    }

    .debug-section h4 {
        font-size: 1rem;
        color: #856404;
        margin-bottom: 15px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .status-card {
            padding: 30px 20px;
        }

        .verification-actions {
            flex-direction: column;
        }

        .verification-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sendBtn = document.getElementById('sendVerificationBtn');
    const resendBtn = document.getElementById('resendVerificationBtn');
    const markVerifiedBtn = document.getElementById('markVerifiedBtn');
    const progressIndicator = document.querySelector('.verification-progress');

    // Send verification email
    if (sendBtn) {
        sendBtn.addEventListener('click', function() {
            sendVerificationEmail();
        });
    }

    // Resend verification email
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            sendVerificationEmail(true);
        });
    }

    // Mark as verified (debug)
    if (markVerifiedBtn) {
        markVerifiedBtn.addEventListener('click', function() {
            markAsVerified();
        });
    }

    // Auto-check verification status
    let statusCheckInterval;
    
    function startStatusChecking() {
        statusCheckInterval = setInterval(checkVerificationStatus, 5000); // Check every 5 seconds
    }

    function stopStatusChecking() {
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
        }
    }

    // Send verification email function
    function sendVerificationEmail(isResend = false) {
        const button = isResend ? resendBtn : sendBtn;
        const originalText = button.innerHTML;
        
        // Show progress
        button.disabled = true;
        button.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Sending...';
        
        if (progressIndicator) {
            progressIndicator.style.display = 'block';
        }

        const url = isResend ? '{{ route("onboarding.email.resend") }}' : '{{ route("onboarding.email.send") }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                
                // Hide send button, show resend button
                if (sendBtn) sendBtn.style.display = 'none';
                if (resendBtn) resendBtn.style.display = 'inline-flex';
                
                // Start checking verification status
                startStatusChecking();
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('Failed to send verification email. Please try again.');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
            
            if (progressIndicator) {
                progressIndicator.style.display = 'none';
            }
        });
    }

    // Check verification status
    function checkVerificationStatus() {
        fetch('{{ route("onboarding.email.verification") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(response => {
            if (response.redirected) {
                // Email was verified, redirect to next step
                window.location.href = response.url;
            }
        })
        .catch(error => {
            console.error('Status check error:', error);
        });
    }

    // Mark as verified (debug function)
    function markAsVerified() {
        if (!confirm('Are you sure you want to mark email as verified? (Debug only)')) {
            return;
        }

        fetch('{{ route("onboarding.email.complete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('Failed to mark as verified.');
        });
    }

    // Success message function
    function showSuccessMessage(message) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #36ca78 0%, #2fb368 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(54, 202, 120, 0.3);
            z-index: 10000;
            font-weight: 500;
            font-size: 14px;
            max-width: 350px;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 16px;">✅</span>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Error message function
    function showErrorMessage(message) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
            z-index: 10000;
            font-weight: 500;
            font-size: 14px;
            max-width: 350px;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 16px;">❌</span>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
});
</script>
@endpush