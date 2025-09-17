<?php
use App\Models\Utils;
$ent = Utils::ent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ent->name }} - Set New Password</title>
    
    <!-- Meta Tags -->
    <meta name="description" content="Set a new password for {{ $ent->name }} school management system">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        :root {
            --primary-color: {{ $ent->color ?? '#007bff' }};
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --border-light: #e9ecef;
            --success-color: #28a745;
            --error-color: #dc3545;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--primary-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            background: var(--white);
            max-width: 450px;
            width: 100%;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .auth-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: var(--text-light);
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-light);
            
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color), 0.1);
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            z-index: 10;
        }

        .form-control.with-icon {
            padding-left: 2.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            padding: 0.75rem 1.5rem;
            
            font-weight: 500;
            font-size: 1rem;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: var(--background-light);
            color: var(--text-dark);
        }

        .alert {
            
            border: none;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .password-requirements {
            background: var(--background-light);
            
            padding: 1rem;
            margin: 1rem 0;
            border-left: 4px solid var(--primary-color);
        }

        .password-requirements h5 {
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1rem;
        }

        .password-requirements li {
            color: var(--text-light);
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            
            background: var(--border-light);
            overflow: hidden;
        }

        .strength-progress {
            height: 100%;
            transition: all 0.3s ease;
            
        }

        .strength-text {
            font-size: 0.8rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="{{ $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png') }}" 
                 alt="{{ $ent->name }}" 
                 class="brand-logo">
            <h2 class="auth-title">Set New Password</h2>
            <p class="auth-subtitle">
                Enter your new password below. Make sure it's strong and secure.
            </p>
        </div>

        <!-- Status Messages -->
        @if (session('status'))
            <div class="alert alert-success">
                <i class='bx bx-check-circle'></i>
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class='bx bx-error-circle'></i>
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <!-- Reset Form -->
        <form id="resetForm" action="{{ url('auth/reset-password') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <i class='bx bx-envelope input-icon'></i>
                    <input type="email" 
                           name="email" 
                           class="form-control with-icon" 
                           placeholder="Enter your email address"
                           value="{{ request('email') ?? old('email') }}"
                           required
                           autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <i class='bx bx-lock input-icon'></i>
                    <input type="password" 
                           name="password" 
                           id="password"
                           class="form-control with-icon" 
                           placeholder="Enter your new password"
                           required
                           autocomplete="new-password">
                </div>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-progress" id="strengthProgress"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <i class='bx bx-lock input-icon'></i>
                    <input type="password" 
                           name="password_confirmation" 
                           id="passwordConfirm"
                           class="form-control with-icon" 
                           placeholder="Confirm your new password"
                           required
                           autocomplete="new-password">
                </div>
            </div>

            <div class="password-requirements">
                <h5><i class='bx bx-shield'></i> Password Requirements</h5>
                <ul>
                    <li>At least 6 characters long</li>
                    <li>Contains both uppercase and lowercase letters</li>
                    <li>Contains at least one number</li>
                    <li>Contains at least one special character</li>
                </ul>
            </div>

            <div class="form-group">
                <label class="form-label">Security Code</label>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <img src="{{ url('/auth/captcha') }}" 
                         alt="CAPTCHA" 
                         id="captcha-image"
                         style="border: 2px solid var(--border-light); padding: 5px; background: white;">
                    <button type="button" 
                            onclick="refreshCaptcha()" 
                            style="background: var(--primary-color); color: white; border: none; padding: 8px 12px; cursor: pointer; font-size: 14px;">
                        <i class='bx bx-refresh'></i> Refresh
                    </button>
                </div>
                <div class="input-group">
                    <i class='bx bx-shield input-icon'></i>
                    <input type="text" 
                           name="captcha" 
                           class="form-control with-icon @error('captcha') is-invalid @enderror" 
                           placeholder="Enter the numbers shown above"
                           autocomplete="off"
                           required>
                </div>
                @error('captcha')
                    <div class="invalid-feedback" style="color: var(--error-color); font-size: 0.8rem; margin-top: 0.25rem;">
                        {{ $message }}
                    </div>
                @enderror
                <small style="color: var(--text-light); font-size: 0.8rem;">
                    Please enter the 4-digit number shown in the image above.
                </small>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    Reset Password
                </button>
            </div>
        </form>

        <a href="{{ url('auth/login') }}" class="btn btn-secondary">
            <i class='bx bx-arrow-back'></i>
            Back to Sign In
        </a>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Simple JavaScript -->
    <script>
        // Simple CAPTCHA refresh function
        function refreshCaptcha() {
            const captchaImage = document.getElementById('captcha-image');
            const captchaInput = document.querySelector('input[name="captcha"]');
            
            if (captchaImage) {
                captchaImage.src = '{{ url("auth/captcha") }}?' + new Date().getTime();
            }
            
            if (captchaInput) {
                captchaInput.value = '';
                captchaInput.focus();
            }
        }

        // Basic password validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('passwordConfirm');
            
            // Check if passwords match
            function validatePasswords() {
                if (password.value && passwordConfirm.value && password.value !== passwordConfirm.value) {
                    passwordConfirm.classList.add('is-invalid');
                } else {
                    passwordConfirm.classList.remove('is-invalid');
                }
            }

            if (password) {
                password.addEventListener('input', validatePasswords);
            }
            
            if (passwordConfirm) {
                passwordConfirm.addEventListener('input', validatePasswords);
            }

            // Form submission validation
            const form = document.getElementById('resetForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (password.value !== passwordConfirm.value) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        return false;
                    }
                });
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            });
        }, 5000);

        // Focus first input when page loads
        window.addEventListener('load', function() {
            const firstInput = document.querySelector('input[name="email"]');
            if (firstInput) {
                firstInput.focus();
            }
        });
    </script>
</body>
</html>