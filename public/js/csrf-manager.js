/**
 * CSRF Token Auto-Refresh System
 * Prevents 419 Page Expired errors on public forms
 */

class CSRFTokenManager {
    constructor() {
        this.refreshInterval = 90 * 60 * 1000; // 90 minutes (before 120min expiry)
        this.tokenName = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        if (!this.tokenName) {
            console.warn('CSRF meta tag not found');
            return;
        }

        // Start auto-refresh
        this.startAutoRefresh();
        
        // Refresh on form submission
        this.attachFormListeners();
        
        // Refresh on page visibility change
        this.attachVisibilityListener();
    }

    async refreshToken() {
        try {
            const response = await fetch('/csrf-token', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateTokens(data.token);
                console.log('CSRF token refreshed successfully');
                return true;
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
        }
        return false;
    }

    updateTokens(newToken) {
        // Update meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', newToken);
        }

        // Update all CSRF input fields
        document.querySelectorAll('input[name="_token"]').forEach(input => {
            input.value = newToken;
        });

        // Update any data attributes that might contain the token
        document.querySelectorAll('[data-csrf-token]').forEach(element => {
            element.setAttribute('data-csrf-token', newToken);
        });

        this.tokenName = newToken;
    }

    startAutoRefresh() {
        setInterval(() => {
            this.refreshToken();
        }, this.refreshInterval);
    }

    attachFormListeners() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                // Check if token is getting old (more than 100 minutes)
                const tokenAge = this.getTokenAge();
                if (tokenAge > 100 * 60 * 1000) {
                    e.preventDefault();
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn?.textContent;
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Refreshing...';
                    }

                    // Refresh token and resubmit
                    const success = await this.refreshToken();
                    if (success) {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }
                        form.submit();
                    } else {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }
                        alert('Session expired. Please refresh the page and try again.');
                    }
                }
            });
        });
    }

    attachVisibilityListener() {
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                // Page became visible, refresh token if needed
                const tokenAge = this.getTokenAge();
                if (tokenAge > 60 * 60 * 1000) { // More than 1 hour
                    this.refreshToken();
                }
            }
        });
    }

    getTokenAge() {
        // Simple estimation based on when the script was loaded
        return Date.now() - (window.pageLoadTime || Date.now());
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.pageLoadTime = Date.now();
    window.csrfManager = new CSRFTokenManager();
});

// Also handle CAPTCHA refresh to prevent CSRF issues
function refreshCaptchaWithToken() {
    if (window.csrfManager) {
        window.csrfManager.refreshToken().then(() => {
            // Original CAPTCHA refresh logic
            const captchaImage = document.getElementById('captcha-image');
            const captchaInput = document.querySelector('input[name="captcha"]');
            
            if (captchaImage) {
                captchaImage.src = captchaImage.src.split('?')[0] + '?' + new Date().getTime();
            }
            
            if (captchaInput) {
                captchaInput.value = '';
                captchaInput.focus();
            }
        });
    }
}

// Export for global use
window.refreshCaptchaWithToken = refreshCaptchaWithToken;