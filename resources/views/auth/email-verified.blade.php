@extends('layouts.verification')

@section('title', 'Email Verified Successfully')

@section('content')
<div class="verification-header">
    <div class="verification-icon">
        <i class='bx bx-check-circle'></i>
    </div>
    <h1>Email Verified!</h1>
    <p>Your email has been successfully verified</p>
</div>

<div class="verification-content">
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
        
        <div class="step completed">
            <div class="step-icon">
                <i class='bx bx-check'></i>
            </div>
            <div>
                <strong>Email Verified</strong><br>
                <small class="text-muted">Your email address has been confirmed</small>
            </div>
        </div>
        
        <div class="step active">
            <div class="step-icon">
                <i class='bx bx-user-check'></i>
            </div>
            <div>
                <strong>Ready to Continue</strong><br>
                <small class="text-muted">You can now access your dashboard</small>
            </div>
        </div>
    </div>

    <div class="alert alert-success">
        <i class='bx bx-check-circle'></i>
        <strong>Congratulations!</strong> Your email address has been successfully verified. You now have full access to your account and can proceed to your dashboard.
    </div>

    <div class="text-center">
        <a href="{{ config('admin.route.prefix') }}" class="btn btn-success btn-block mb-3">
            <i class='bx bx-dashboard'></i>
            Continue to Dashboard
        </a>
        
        <a href="{{ route('admin.logout') }}" class="btn btn-outline">
            <i class='bx bx-log-out'></i>
            Sign Out Instead
        </a>
    </div>

    <div class="text-center mt-3">
        <small class="text-muted">
            <i class='bx bx-shield-check'></i>
            Your account is now secure and ready to use.
        </small>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-redirect after 5 seconds if no action taken
    let redirectTimer = setTimeout(function() {
        window.location.href = '{{ config("admin.route.prefix") }}';
    }, 10000);
    
    // Cancel auto-redirect if user interacts with the page
    $('a, button').on('click', function() {
        clearTimeout(redirectTimer);
    });
    
    // Add countdown display
    let countdown = 10;
    const countdownElement = $('<div class="text-center mt-3"><small class="text-muted">Redirecting to dashboard in <span id="countdown">10</span> seconds...</small></div>');
    $('.verification-content').append(countdownElement);
    
    const countdownInterval = setInterval(function() {
        countdown--;
        $('#countdown').text(countdown);
        
        if (countdown <= 0) {
            clearInterval(countdownInterval);
        }
    }, 1000);
    
    // Clear timers when user leaves page
    $(window).on('beforeunload', function() {
        clearTimeout(redirectTimer);
        clearInterval(countdownInterval);
    });
});
</script>
@endpush
@endsection