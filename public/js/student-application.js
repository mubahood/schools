/**
 * Student Application Portal JavaScript
 * Handles auto-save, session management, file uploads, and dynamic interactions
 */

(function($) {
    'use strict';
    
    // Configuration
    const CONFIG = {
        autoSaveInterval: 30000, // 30 seconds
        sessionHeartbeatInterval: 60000, // 1 minute
        sessionWarningTime: 300, // 5 minutes (in seconds)
        sessionDangerTime: 120, // 2 minutes (in seconds)
    };
    
    // Session timer
    let sessionTimeRemaining = 7200; // 2 hours in seconds
    let sessionTimerInterval = null;
    let sessionHeartbeatInterval = null;
    let autoSaveInterval = null;
    
    /**
     * Initialize application
     */
    $(document).ready(function() {
        initializeSessionTimer();
        initializeSessionHeartbeat();
        initializeAutoSave();
        initializeFormValidation();
    });
    
    /**
     * Initialize session timer display
     */
    function initializeSessionTimer() {
        if ($('#sessionIndicator').length === 0) {
            return;
        }
        
        $('#sessionIndicator').show();
        
        // Update timer every second
        sessionTimerInterval = setInterval(function() {
            sessionTimeRemaining--;
            
            if (sessionTimeRemaining <= 0) {
                handleSessionTimeout();
                return;
            }
            
            updateSessionDisplay();
        }, 1000);
    }
    
    /**
     * Update session timer display
     */
    function updateSessionDisplay() {
        const minutes = Math.floor(sessionTimeRemaining / 60);
        const seconds = sessionTimeRemaining % 60;
        const timeString = pad(minutes) + ':' + pad(seconds);
        
        $('#sessionTimer').text(timeString);
        
        // Update indicator styling based on time remaining
        const indicator = $('#sessionIndicator');
        indicator.removeClass('warning danger success');
        
        if (sessionTimeRemaining <= CONFIG.sessionDangerTime) {
            indicator.addClass('danger');
        } else if (sessionTimeRemaining <= CONFIG.sessionWarningTime) {
            indicator.addClass('warning');
        } else {
            indicator.addClass('success');
        }
    }
    
    /**
     * Send heartbeat to keep session alive
     */
    function initializeSessionHeartbeat() {
        if ($('#sessionIndicator').length === 0) {
            return;
        }
        
        sessionHeartbeatInterval = setInterval(function() {
            sendHeartbeat();
        }, CONFIG.sessionHeartbeatInterval);
    }
    
    /**
     * Send heartbeat request
     */
    function sendHeartbeat() {
        $.ajax({
            url: '/apply/session/heartbeat',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.time_remaining) {
                    sessionTimeRemaining = response.time_remaining;
                }
            },
            error: function() {
                console.warn('Session heartbeat failed');
            }
        });
    }
    
    /**
     * Handle session timeout
     */
    function handleSessionTimeout() {
        clearInterval(sessionTimerInterval);
        clearInterval(sessionHeartbeatInterval);
        clearInterval(autoSaveInterval);
        
        alert('Your session has expired. Please start a new application or resume your existing one.');
        window.location.href = '/apply';
    }
    
    /**
     * Initialize auto-save functionality
     */
    function initializeAutoSave() {
        const forms = $('form#bioDataForm');
        
        if (forms.length === 0) {
            return;
        }
        
        // Auto-save on form input
        forms.find('input, select, textarea').on('input change', function() {
            clearInterval(autoSaveInterval);
            
            autoSaveInterval = setTimeout(function() {
                performAutoSave(forms);
            }, 3000); // Wait 3 seconds after user stops typing
        });
        
        // Periodic auto-save
        setInterval(function() {
            performAutoSave(forms);
        }, CONFIG.autoSaveInterval);
    }
    
    /**
     * Perform auto-save
     */
    function performAutoSave(form) {
        if (!form || form.length === 0) {
            return;
        }
        
        const formData = form.serialize();
        
        // Show saving indicator
        $('#autoSaveStatus')
            .removeClass('saved')
            .addClass('saving')
            .html('<i class="fa fa-spinner fa-spin"></i> Saving...')
            .fadeIn();
        
        $.ajax({
            url: '/apply/session/save',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show saved indicator
                    $('#autoSaveStatus')
                        .removeClass('saving')
                        .addClass('saved')
                        .html('<i class="fa fa-check-circle"></i> Saved');
                    
                    // Hide after 2 seconds
                    setTimeout(function() {
                        $('#autoSaveStatus').fadeOut();
                    }, 2000);
                }
            },
            error: function() {
                // Hide on error
                $('#autoSaveStatus').fadeOut();
            }
        });
    }
    
    /**
     * Initialize form validation
     */
    function initializeFormValidation() {
        // Email validation
        $('input[type="email"]').on('blur', function() {
            const email = $(this).val();
            if (email && !isValidEmail(email)) {
                $(this).addClass('error');
                showFieldError($(this), 'Please enter a valid email address');
            } else {
                $(this).removeClass('error');
                hideFieldError($(this));
            }
        });
        
        // Phone validation (Uganda format)
        $('input[type="tel"]').on('blur', function() {
            const phone = $(this).val();
            if (phone && !isValidPhone(phone)) {
                $(this).addClass('error');
                showFieldError($(this), 'Please enter a valid phone number (e.g., +256...)');
            } else {
                $(this).removeClass('error');
                hideFieldError($(this));
            }
        });
        
        // Required field validation
        $('input[required], select[required], textarea[required]').on('blur', function() {
            if (!$(this).val()) {
                $(this).addClass('error');
                showFieldError($(this), 'This field is required');
            } else {
                $(this).removeClass('error');
                hideFieldError($(this));
            }
        });
    }
    
    /**
     * File upload with progress bar
     */
    window.uploadFileWithProgress = function(formElement, progressCallback, successCallback, errorCallback) {
        const form = $(formElement);
        const formData = new FormData(formElement);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                
                // Upload progress
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        if (progressCallback) {
                            progressCallback(percentComplete);
                        }
                    }
                }, false);
                
                return xhr;
            },
            success: function(response) {
                if (successCallback) {
                    successCallback(response);
                }
            },
            error: function(xhr) {
                if (errorCallback) {
                    errorCallback(xhr);
                }
            }
        });
    };
    
    /**
     * Dynamic school branding
     */
    window.applySchoolBranding = function(primaryColor, secondaryColor, logoUrl) {
        // Update CSS variables
        document.documentElement.style.setProperty('--school-primary', primaryColor);
        document.documentElement.style.setProperty('--school-secondary', secondaryColor);
        
        // Update logo if provided
        if (logoUrl) {
            $('.school-logo').attr('src', logoUrl);
        }
    };
    
    /**
     * Validation helpers
     */
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function isValidPhone(phone) {
        // Uganda phone format: +256... or 0... (10 digits)
        const re = /^(\+256|0)[0-9]{9}$/;
        return re.test(phone.replace(/\s/g, ''));
    }
    
    function showFieldError(field, message) {
        hideFieldError(field);
        
        const errorDiv = $('<div>')
            .addClass('field-error')
            .css({
                'color': '#dd4b39',
                'font-size': '12px',
                'margin-top': '5px'
            })
            .text(message);
        
        field.after(errorDiv);
        field.css('border-color', '#dd4b39');
    }
    
    function hideFieldError(field) {
        field.next('.field-error').remove();
        field.css('border-color', '');
    }
    
    /**
     * Utility functions
     */
    function pad(num) {
        return (num < 10 ? '0' : '') + num;
    }
    
    /**
     * Confirm before leaving page with unsaved changes
     */
    let formChanged = false;
    
    $('form input, form select, form textarea').on('change', function() {
        formChanged = true;
    });
    
    $('form').on('submit', function() {
        formChanged = false;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
})(jQuery);
