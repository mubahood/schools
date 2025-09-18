@extends('layouts.verification')

@section('title', 'Email Verification Required')

@section('content')
<div class="verification-header">
    <div class="verification-icon">
        <i class='bx bx-envelope'></i>
    </div>
    <h1>Email Verification Required</h1>
    <p>Please verify your email address to continue</p>
</div>

<div class="verification-content">
    @if (session('message'))
        <div class="alert alert-success">
            <i class='bx bx-check-circle'></i>
            {{ session('message') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            <i class='bx bx-error-circle'></i>
            {{ session('error') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning">
            <i class='bx bx-error-circle'></i>
            {{ session('warning') }}
        </div>
    @endif

    <div class="verification-steps">
        <div class="step completed">
            <div class="step-icon">
                <i class='bx bx-check'></i>
            </div>
            <div>
                <strong>Account Created</strong><br>
                <small class="text-muted">Your account has been successfully created</small>
            </div>
        </div>
        
        <div class="step active">
            <div class="step-icon">
                2
            </div>
            <div>
                <strong>Email Verification</strong><br>
                <small class="text-muted">Verify your email address to continue</small>
            </div>
        </div>
        
        <div class="step pending">
            <div class="step-icon">
                3
            </div>
            <div>
                <strong>Access Dashboard</strong><br>
                <small class="text-muted">Complete your profile and access features</small>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <i class='bx bx-info-circle'></i>
        <strong>Verification Required:</strong> We've sent a verification email to:
        <div class="email-highlight">{{ $user->email ?? 'your registered email' }}</div>
        Please check your email and click the verification link to continue.
    </div>

    <div class="text-center">
        <form method="POST" action="{{ route('verification.send') }}" id="resendForm">
            @csrf
            <button type="submit" class="btn btn-primary btn-block mb-3" id="resendBtn">
                <i class='bx bx-paper-plane'></i>
                <span id="resendText">Resend Verification Email</span>
                <div class="loading-spinner" id="loadingSpinner" style="display: none;"></div>
            </button>
        </form>

        <a href="{{ route('admin.logout') }}" class="btn btn-outline">
            <i class='bx bx-log-out'></i>
            Sign Out
        </a>
    </div>

    <div class="text-center mt-3">
        <small class="text-muted">
            <i class='bx bx-shield-check'></i>
            Your security is our priority. Email verification helps protect your account.
        </small>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let cooldownTime = 60; // 60 seconds cooldown
    let cooldownTimer;
    
    // Check if there's a stored cooldown
    const lastSent = localStorage.getItem('email_verification_last_sent');
    if (lastSent) {
        const timePassed = Math.floor((Date.now() - parseInt(lastSent)) / 1000);
        if (timePassed < cooldownTime) {
            startCooldown(cooldownTime - timePassed);
        }
    }
    
    $('#resendForm').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $('#resendBtn');
        const spinner = $('#loadingSpinner');
        const text = $('#resendText');
        
        // Show loading state
        btn.prop('disabled', true);
        spinner.show();
        text.text('Sending...');
        
        $.post($(this).attr('action'), $(this).serialize())
            .done(function(response) {
                // Store timestamp
                localStorage.setItem('email_verification_last_sent', Date.now().toString());
                
                // Show success message
                showAlert('success', 'Verification email sent successfully! Please check your inbox.');
                
                // Start cooldown
                startCooldown(cooldownTime);
            })
            .fail(function(xhr) {
                let message = 'Failed to send verification email. Please try again.';
                
                if (xhr.status === 405) {
                    // Method not allowed - try with GET as fallback
                    $.get('{{ route("verification.send") }}')
                        .done(function() {
                            showAlert('info', 'Redirecting to verification page...');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        })
                        .fail(function() {
                            showAlert('danger', 'Unable to process request. Please refresh the page and try again.');
                            resetButton();
                        });
                    return;
                }
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
                
                // Reset button
                resetButton();
            });
    });
    
    function startCooldown(seconds) {
        const btn = $('#resendBtn');
        const text = $('#resendText');
        let remaining = seconds;
        
        btn.prop('disabled', true);
        $('#loadingSpinner').hide();
        
        cooldownTimer = setInterval(function() {
            text.text(`Resend in ${remaining}s`);
            remaining--;
            
            if (remaining < 0) {
                clearInterval(cooldownTimer);
                resetButton();
            }
        }, 1000);
    }
    
    function resetButton() {
        const btn = $('#resendBtn');
        const spinner = $('#loadingSpinner');
        const text = $('#resendText');
        
        btn.prop('disabled', false);
        spinner.hide();
        text.text('Resend Verification Email');
    }
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type}" style="animation: slideInFromTop 0.3s ease-out;">
                <i class='bx bx-${type === 'success' ? 'check-circle' : 'error-circle'}'></i>
                ${message}
            </div>
        `;
        
        // Remove existing alerts
        $('.verification-content .alert').first().remove();
        
        // Add new alert at the top
        $('.verification-content').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.verification-content .alert').first().fadeOut();
        }, 5000);
    }
    
    // Auto-refresh check for email verification
    let checkInterval = setInterval(function() {
        $.get('{{ route("verification.check") }}')
            .done(function(response) {
                if (response.verified) {
                    clearInterval(checkInterval);
                    showAlert('success', 'Email verified! Redirecting to dashboard...');
                    setTimeout(function() {
                        window.location.href = '{{ config("admin.route.prefix") }}';
                    }, 2000);
                }
            })
            .fail(function(xhr) {
                // Handle different error types
                if (xhr.status === 401) {
                    // User logged out
                    clearInterval(checkInterval);
                    showAlert('warning', 'Session expired. Please log in again.');
                    setTimeout(function() {
                        window.location.href = '{{ route("public.login") }}';
                    }, 2000);
                } else if (xhr.status === 404) {
                    // Route not found
                    clearInterval(checkInterval);
                    console.log('Verification check route not found');
                } else {
                    // Other errors - continue checking
                    console.log('Verification check failed, will retry...');
                }
            });
    }, 10000); // Check every 10 seconds
    
    // Clear interval when user leaves page
    $(window).on('beforeunload', function() {
        clearInterval(checkInterval);
        if (cooldownTimer) {
            clearInterval(cooldownTimer);
        }
    });
});
</script>
@endpush
@endsection