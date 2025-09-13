/**
 * Onboarding Wizard JavaScript
 * Handles form validation, session storage, and navigation
 */

class OnboardingWizard {
    constructor() {
        this.currentStep = this.getCurrentStep();
        this.initializeStepNavigation();
        this.setupGlobalHandlers();
    }

    getCurrentStep() {
        const path = window.location.pathname;
        const match = path.match(/step(\d+)/);
        return match ? parseInt(match[1]) : 1;
    }

    /**
     * Store form data in sessionStorage with step prefix
     */
    saveStepData(step, data) {
        sessionStorage.setItem(`onboarding_step${step}_data`, JSON.stringify(data));
    }

    /**
     * Load form data from sessionStorage
     */
    loadStepData(step) {
        const savedData = sessionStorage.getItem(`onboarding_step${step}_data`);
        return savedData ? JSON.parse(savedData) : null;
    }

    /**
     * Clear all onboarding data from sessionStorage
     */
    clearAllData() {
        for (let i = 1; i <= 5; i++) {
            sessionStorage.removeItem(`onboarding_step${i}_data`);
        }
    }

    /**
     * Setup global event handlers
     */
    setupGlobalHandlers() {
        // Add loading states to navigation buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('a[href*="onboarding/step"]')) {
                this.showLoading(e.target);
            }
        });

        // Auto-save form data
        document.addEventListener('input', (e) => {
            if (e.target.matches('.form-input')) {
                this.debounce(() => {
                    this.autoSaveCurrentStep();
                }, 300)();
            }
        });

        // Handle back/forward browser navigation
        window.addEventListener('popstate', () => {
            this.currentStep = this.getCurrentStep();
        });
    }

    /**
     * Initialize step navigation indicators
     */
    initializeStepNavigation() {
        // Update progress indicators if they exist
        const indicators = document.querySelectorAll('.progress-step-indicator');
        indicators.forEach((indicator, index) => {
            const stepNumber = index + 1;
            if (stepNumber < this.currentStep) {
                indicator.classList.add('completed');
            } else if (stepNumber === this.currentStep) {
                indicator.classList.add('active');
            }
        });
    }

    /**
     * Auto-save current step form data
     */
    autoSaveCurrentStep() {
        const form = document.querySelector('form');
        if (!form) return;

        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        this.saveStepData(this.currentStep, data);
    }

    /**
     * Show loading state on element
     */
    showLoading(element) {
        const originalText = element.innerHTML;
        element.innerHTML = '<i class="bx bx-loader bx-spin"></i> Loading...';
        element.style.pointerEvents = 'none';

        // Restore after timeout (fallback)
        setTimeout(() => {
            element.innerHTML = originalText;
            element.style.pointerEvents = 'auto';
        }, 5000);
    }

    /**
     * Debounce function for performance
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Validate email format
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Validate phone number format
     */
    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[0-9]{10,15}$/;
        return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }

    /**
     * Show error message for field
     */
    showFieldError(fieldName, message) {
        const errorElement = document.getElementById(fieldName + '_error');
        const inputElement = document.getElementById(fieldName);

        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        if (inputElement) {
            inputElement.classList.add('error');
        }
    }

    /**
     * Clear error message for field
     */
    clearFieldError(fieldName) {
        const errorElement = document.getElementById(fieldName + '_error');
        const inputElement = document.getElementById(fieldName);

        if (errorElement) {
            errorElement.style.display = 'none';
        }

        if (inputElement) {
            inputElement.classList.remove('error');
        }
    }

    /**
     * Clear all form errors
     */
    clearAllErrors() {
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.form-input').forEach(el => {
            el.classList.remove('error');
        });
    }

    /**
     * Show success message
     */
    showSuccess(message, duration = 3000) {
        const successDiv = document.createElement('div');
        successDiv.className = 'success-toast';
        successDiv.innerHTML = `
            <i class="bx bx-check-circle"></i>
            <span>${message}</span>
        `;
        successDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(successDiv);

        setTimeout(() => {
            successDiv.remove();
        }, duration);
    }

    /**
     * Show error toast
     */
    showError(message, duration = 5000) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-toast';
        errorDiv.innerHTML = `
            <i class="bx bx-error-circle"></i>
            <span>${message}</span>
        `;
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ef4444;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(errorDiv);

        setTimeout(() => {
            errorDiv.remove();
        }, duration);
    }
}

// Initialize onboarding wizard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.onboardingWizard = new OnboardingWizard();
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .progress-step-indicator.completed {
        background: #10b981 !important;
        border-color: #10b981 !important;
        color: white !important;
    }

    .progress-step-indicator.completed::after {
        content: '✓';
        font-size: 0.8rem;
    }

    .form-input.error {
        border-color: #ef4444 !important;
        background-color: #fef2f2;
    }

    .form-input.success {
        border-color: #10b981 !important;
        background-color: #f0fdf4;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: none;
    }
`;
document.head.appendChild(style);
