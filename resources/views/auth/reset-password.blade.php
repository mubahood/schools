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
            --secondary-color: {{ $ent->sec_color ?? '#6c757d' }};
            --background-light: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --border-light: #e9ecef;
            --success-color: #28a745;
            --error-color: #dc3545;
            --shadow-light: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-medium: 0 4px 20px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-medium);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            margin: 20px;
            padding: 3rem;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .auth-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 1.5rem;
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
            border-radius: 8px;
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
            border-radius: 8px;
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
            border-radius: 8px;
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
            border-radius: 8px;
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
            border-radius: 8px;
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
            border-radius: 2px;
            background: var(--border-light);
            overflow: hidden;
        }

        .strength-progress {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-text {
            font-size: 0.8rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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
        <form id="resetForm" action="{{ admin_url('auth/reset-password') }}" method="POST">
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
                <button type="submit" class="btn btn-primary" id="resetBtn">
                    <span class="btn-text">Reset Password</span>
                    <span class="spinner d-none"></span>
                </button>
            </div>
        </form>

        <a href="{{ admin_url('auth/login') }}" class="btn btn-secondary">
            <i class='bx bx-arrow-back'></i>
            Back to Sign In
        </a>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                let feedback = '';
                
                if (password.length >= 6) strength += 1;
                if (password.match(/[a-z]/)) strength += 1;
                if (password.match(/[A-Z]/)) strength += 1;
                if (password.match(/[0-9]/)) strength += 1;
                if (password.match(/[^A-Za-z0-9]/)) strength += 1;
                
                const $progress = $('#strengthProgress');
                const $text = $('#strengthText');
                
                switch (strength) {
                    case 0:
                    case 1:
                        $progress.css({width: '20%', background: '#dc3545'});
                        $text.text('Very Weak').css('color', '#dc3545');
                        break;
                    case 2:
                        $progress.css({width: '40%', background: '#fd7e14'});
                        $text.text('Weak').css('color', '#fd7e14');
                        break;
                    case 3:
                        $progress.css({width: '60%', background: '#ffc107'});
                        $text.text('Fair').css('color', '#ffc107');
                        break;
                    case 4:
                        $progress.css({width: '80%', background: '#28a745'});
                        $text.text('Good').css('color', '#28a745');
                        break;
                    case 5:
                        $progress.css({width: '100%', background: '#28a745'});
                        $text.text('Strong').css('color', '#28a745');
                        break;
                }
                
                return strength;
            }

            // Password input handling
            $('#password').on('input', function() {
                const password = $(this).val();
                checkPasswordStrength(password);
                
                // Check if passwords match
                const confirmPassword = $('#passwordConfirm').val();
                if (confirmPassword && password !== confirmPassword) {
                    $('#passwordConfirm').addClass('is-invalid');
                } else {
                    $('#passwordConfirm').removeClass('is-invalid');
                }
            });

            // Confirm password handling
            $('#passwordConfirm').on('input', function() {
                const password = $('#password').val();
                const confirmPassword = $(this).val();
                
                if (password !== confirmPassword) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Form submission handling
            $('#resetForm').on('submit', function(e) {
                const password = $('#password').val();
                const confirmPassword = $('#passwordConfirm').val();
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return false;
                }
                
                const $form = $(this);
                const $btn = $('#resetBtn');
                const $btnText = $btn.find('.btn-text');
                const $spinner = $btn.find('.spinner');

                // Show loading state
                $btn.addClass('loading');
                $btnText.text('Resetting...');
                $spinner.removeClass('d-none');
                
                // Disable form
                $form.find('input, button').prop('disabled', true);
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);

            // Focus first input
            $('input[name="email"]').focus();
        });
    </script>
</body>
</html>