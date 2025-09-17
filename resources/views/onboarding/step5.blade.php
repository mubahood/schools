@extends('layouts.onboarding')

@section('title', 'Welcome to ' . \App\Models\Utils::app_name() . ' - Registration Complete')
@section('meta_description', 'Registration completed successfully. Welcome to your new school management platform.')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Welcome Aboard!</h2>
        <p class="progress-description">
            Your school registration is now complete.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator active" style="background: var(--accent-color);">
            <i class='bx bx-check'></i>
        </div>
        <span>Complete</span>
    </div>
@endsection

@section('content')
    <div class="completion-container">
        <div class="completion-icon">
            <i class='bx bx-check-circle'></i>
        </div>
        
        <div class="content-title">Registration Successful!</div>
        <div class="content-description">
            Welcome to {{ \App\Models\Utils::app_name() }}, {{ $successData['user_name'] ?? 'Administrator' }}!
        </div>
    </div>
    
    <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin: 2rem 0; text-align: center; margin-bottom: 2rem;">
        <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.1rem;">
            🎉 {{ $successData['school_name'] ?? 'Your School' }} is now registered!
        </h3>
        <p style="color: var(--text-light); font-size: 0.9rem; line-height: 1.4; margin-bottom: 1rem;">
            Your administrator account has been created and your school management platform is ready to use.
        </p>
        <div style="background: var(--background-white); padding: 1rem; border-radius: 6px; border-left: 4px solid var(--primary-color);">
            <strong style="color: var(--text-dark);">Login Email:</strong> 
            <span style="color: var(--primary-color);">{{ $successData['email'] ?? '' }}</span>
        </div>
    </div>
    
    <div style="background: var(--background-white); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
        <h4 style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1rem; display: flex; align-items: center;">
            <i class='bx bx-list-check' style="margin-right: 0.5rem; color: var(--primary-color);"></i>
            What's Next?
        </h4>
        <div style="display: grid; gap: 0.75rem; font-size: 0.85rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class='bx bx-check' style="color: #10b981; font-size: 1rem;"></i>
                <span style="color: var(--text-dark);">Log in to your administrator dashboard</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class='bx bx-check' style="color: #10b981; font-size: 1rem;"></i>
                <span style="color: var(--text-dark);">Set up your academic year and terms</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class='bx bx-check' style="color: #10b981; font-size: 1rem;"></i>
                <span style="color: var(--text-dark);">Add classes and subjects</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class='bx bx-check' style="color: #10b981; font-size: 1rem;"></i>
                <span style="color: var(--text-dark);">Start enrolling students and staff</span>
            </div>
        </div>
    </div>
    
    <div style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;">
        <h4 style="margin-bottom: 0.75rem; font-size: 1rem;">
            <i class='bx bx-support' style="margin-right: 0.5rem;"></i>
            Need Help Getting Started?
        </h4>
        <p style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 1rem;">
            Our support team is here to help you set up your school management system.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; font-size: 0.8rem;">
            <div style="display: flex; align-items: center; gap: 0.25rem;">
                <i class='bx bx-phone'></i>
                <span>+256 XXX XXX XXX</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.25rem;">
                <i class='bx bx-envelope'></i>
                <span>support@{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'schooldynamics.com' }}</span>
            </div>
        </div>
    </div>
    
    <form method="POST" action="{{ route('onboarding.complete') }}">
        @csrf
        <div style="display: flex; gap: 1rem;">
            <a href="{{ url('/') }}" class="btn btn-secondary" style="flex: 1;">
                <i class='bx bx-home'></i>
                Back to Home
            </a>
            <button type="submit" class="btn btn-primary" style="flex: 2;">
                <i class='bx bx-log-in'></i>
                Go to Login
            </button>
        </div>
    </form>
@endsection

@push('styles')
<style>
/* Success-specific styling */
.progress-step-indicator.active {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: white !important;
}

@keyframes celebration {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.celebration-icon {
    animation: celebration 2s ease-in-out infinite;
}

/* Confetti effect (optional) */
@keyframes confetti-fall {
    0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
}

.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    background: var(--primary-color);
    animation: confetti-fall 3s linear infinite;
    z-index: 1000;
}
</style>
@endpush

@push('scripts')
<script>
// Add some celebration confetti effect
function createConfetti() {
    const colors = ['#6366F1', '#00B3FA', '#10b981', '#f59e0b', '#ef4444'];
    
    for (let i = 0; i < 50; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDelay = Math.random() * 3 + 's';
            confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
            
            document.body.appendChild(confetti);
            
            // Remove confetti after animation
            setTimeout(() => {
                confetti.remove();
            }, 5000);
        }, i * 50);
    }
}

// Add celebration effect on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add celebration class to success icon
    const successIcon = document.querySelector('.bx-check-circle');
    if (successIcon) {
        successIcon.classList.add('celebration-icon');
    }
    
    // Create confetti effect
    setTimeout(createConfetti, 500);
});

// Clear any remaining session storage
sessionStorage.removeItem('onboarding_step2_data');
sessionStorage.removeItem('onboarding_step3_data');
</script>
@endpush
