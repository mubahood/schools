{{-- Onboarding Progress Widget for Dashboard --}}
@if($onboardingData)
<style>
    .onboarding-widget {
        background: linear-gradient(135deg, #2c5aa0 0%, #1e3f72 100%);
        border-radius: 16px;
        color: white;
        padding: 0;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(44, 90, 160, 0.3);
        overflow: hidden;
        position: relative;
    }

    .onboarding-widget::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.05"/><circle cx="25" cy="25" r="0.5" fill="white" opacity="0.03"/><circle cx="75" cy="75" r="0.5" fill="white" opacity="0.03"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        pointer-events: none;
        z-index: 1;
    }

    .onboarding-header {
        padding: 24px 24px 20px 24px;
        position: relative;
        z-index: 2;
    }

    .onboarding-title {
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .onboarding-title i {
        font-size: 28px;
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .onboarding-subtitle {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
        font-weight: 400;
    }

    .progress-section {
        padding: 0 24px 20px 24px;
        position: relative;
        z-index: 2;
    }

    .progress-bar-container {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 3px;
        margin-bottom: 16px;
    }

    .progress-bar {
        background: linear-gradient(90deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 10px;
        height: 8px;
        transition: width 0.8s ease-in-out;
        position: relative;
        overflow: hidden;
    }

    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%);
        animation: progress-shine 2s infinite;
    }

    @keyframes progress-shine {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .progress-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .progress-percentage {
        font-weight: 700;
        font-size: 16px;
        color: #ffffff;
    }

    .progress-steps {
        opacity: 0.9;
    }

    .current-step-section {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .current-step-title {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .current-step-title i {
        font-size: 20px;
        color: #ffffff;
        width: 24px;
        text-align: center;
    }

    .current-step-description {
        font-size: 13px;
        opacity: 0.9;
        margin: 0 0 16px 0;
        line-height: 1.4;
    }

    .current-step-meta {
        display: flex;
        gap: 16px;
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 16px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .meta-item i {
        font-size: 14px;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-onboarding {
        background: linear-gradient(45deg, #ffffff 0%, #f8f9fa 100%);
        color: #2c5aa0;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
    }

    .btn-onboarding:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 255, 255, 0.4);
        color: #1e3f72;
        text-decoration: none;
    }

    .btn-onboarding:active {
        transform: translateY(0);
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .completion-summary {
        padding: 0 24px 24px 24px;
        position: relative;
        z-index: 2;
    }

    .completion-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }

    .completion-item {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        backdrop-filter: blur(5px);
    }

    .completion-number {
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 4px 0;
    }

    .completion-label {
        font-size: 11px;
        opacity: 0.9;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .next-step-hint {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 12px;
        font-size: 12px;
        opacity: 0.9;
        border-left: 3px solid #ffffff;
    }

    .next-step-hint strong {
        color: #ffffff;
    }

    .dismissible-banner {
        position: relative;
    }

    .dismiss-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        z-index: 3;
    }

    .dismiss-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    @media (max-width: 768px) {
        .onboarding-widget {
            margin: 0 -15px 20px -15px;
            border-radius: 0;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-onboarding, .btn-secondary {
            justify-content: center;
            text-align: center;
        }
        
        .completion-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Animate widget entrance */
    .onboarding-widget {
        animation: slideInFromTop 0.6s ease-out;
    }

    @keyframes slideInFromTop {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Pulse animation for current step icon */
    .current-step-title i {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.05); }
        100% { opacity: 1; transform: scale(1); }
    }
</style>

<div class="onboarding-widget dismissible-banner" id="onboarding-widget">
    {{-- Dismiss Button --}}
    <button class="dismiss-btn" onclick="dismissOnboarding()" title="Hide onboarding guide">
        <i class='bx bx-x'></i>
    </button>

    {{-- Header Section --}}
    <div class="onboarding-header">
        <h3 class="onboarding-title">
            <i class='bx bx-rocket'></i>
            Welcome to {{ $onboardingData['enterprise_name'] }}!
        </h3>
        <p class="onboarding-subtitle">
            Let's complete your school setup to unlock all features
        </p>
    </div>

    {{-- Progress Section --}}
    <div class="progress-section">
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: {{ $onboardingData['progress_percentage'] }}%"></div>
        </div>
        
        <div class="progress-stats">
            <span class="progress-percentage">{{ $onboardingData['progress_percentage'] }}% Complete</span>
            <span class="progress-steps">{{ $onboardingData['completed_steps'] }}/{{ $onboardingData['total_steps'] }} Steps</span>
        </div>

        {{-- Current Step --}}
        <div class="current-step-section">
            <h4 class="current-step-title">
                <i class='{{ $onboardingData['current_step']['step']['icon'] ?? 'bx-circle' }}'></i>
                {{ $onboardingData['current_step']['step']['title'] }}
            </h4>
            <p class="current-step-description">
                {{ $onboardingData['current_step']['step']['description'] }}
            </p>
            
            @if(isset($onboardingData['current_step']['step']['estimated_time']) || isset($onboardingData['current_step']['step']['count']))
            <div class="current-step-meta">
                @if(isset($onboardingData['current_step']['step']['estimated_time']))
                <div class="meta-item">
                    <i class='bx bx-time'></i>
                    <span>{{ $onboardingData['current_step']['step']['estimated_time'] }}</span>
                </div>
                @endif
                
                @if(isset($onboardingData['current_step']['step']['count']))
                <div class="meta-item">
                    <i class='bx bx-list-ul'></i>
                    <span>{{ $onboardingData['current_step']['step']['count'] }} added</span>
                </div>
                @endif
            </div>
            @endif

            <div class="action-buttons">
                <a href="{{ $onboardingData['current_step']['step']['action_url'] }}" class="btn-onboarding">
                    {{ $onboardingData['current_step']['step']['action_text'] }}
                    <i class='bx bx-right-arrow-alt'></i>
                </a>
                
                @if(isset($onboardingData['next_step']) && !($onboardingData['current_step']['step']['required'] ?? false))
                <button class="btn-secondary" onclick="skipCurrentStep()">
                    <i class='bx bx-skip-next'></i>
                    Skip for Now
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Completion Summary --}}
    <div class="completion-summary">
        <div class="completion-grid">
            <div class="completion-item">
                <div class="completion-number">{{ $onboardingData['completed_steps'] }}</div>
                <div class="completion-label">Completed</div>
            </div>
            <div class="completion-item">
                <div class="completion-number">{{ $onboardingData['total_steps'] - $onboardingData['completed_steps'] }}</div>
                <div class="completion-label">Remaining</div>
            </div>
        </div>

        @if(isset($onboardingData['estimated_completion']) && $onboardingData['estimated_completion'] !== '0 minutes')
        <div class="next-step-hint">
            <strong>Estimated time to completion:</strong> {{ $onboardingData['estimated_completion'] }}
        </div>
        @endif
    </div>
</div>

<script>
    function dismissOnboarding() {
        const widget = document.getElementById('onboarding-widget');
        if (widget) {
            widget.style.animation = 'slideOutToTop 0.4s ease-in';
            widget.style.transform = 'translateY(-100%)';
            widget.style.opacity = '0';
            
            setTimeout(() => {
                widget.style.display = 'none';
                
                // Store dismissal in localStorage
                localStorage.setItem('onboarding_dismissed', 'true');
                localStorage.setItem('onboarding_dismissed_at', new Date().toISOString());
            }, 400);
        }
    }

    function skipCurrentStep() {
        if (!confirm('Are you sure you want to skip this step? You can always come back to it later.')) {
            return;
        }

        fetch('{{ admin_url("onboarding/skip-step") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show updated progress
                window.location.reload();
            } else {
                alert(data.message || 'Failed to skip step. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Check if onboarding was previously dismissed and hide if so
    document.addEventListener('DOMContentLoaded', function() {
        const dismissed = localStorage.getItem('onboarding_dismissed');
        const dismissedAt = localStorage.getItem('onboarding_dismissed_at');
        
        // Show again if dismissed more than 24 hours ago
        if (dismissed && dismissedAt) {
            const dismissedDate = new Date(dismissedAt);
            const now = new Date();
            const hoursDiff = (now - dismissedDate) / (1000 * 60 * 60);
            
            if (hoursDiff > 24) {
                localStorage.removeItem('onboarding_dismissed');
                localStorage.removeItem('onboarding_dismissed_at');
            } else {
                const widget = document.getElementById('onboarding-widget');
                if (widget) {
                    widget.style.display = 'none';
                }
            }
        }
    });

    // Add slide out animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOutToTop {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endif