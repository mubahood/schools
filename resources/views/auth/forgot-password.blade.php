<?php
use App\Models\Utils;
$ent = Utils::ent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ent->name }} - Reset Password</title>
    
    <!-- Meta Tags -->
    <meta name="description" content="Reset your password for {{ $ent->name }} school management system">
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
            margin-bottom: 0.4rem;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1px solid var(--border-light);
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            z-index: 10;
        }

        .form-control.with-icon {
            padding-left: 2.2rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.7rem 1.2rem;
            font-weight: 500;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.3s ease;
            color: var(--white);
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: var(--primary-color);
            opacity: 0.9;
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            padding: 0.7rem 1.2rem;
            font-weight: 500;
            font-size: 0.9rem;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            margin-top: 0.8rem;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            color: var(--text-dark);
        }

        .alert {
            border: none;
            padding: 0.6rem 0.8rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .info-box {
            background: #f8f9fa;
            padding: 1rem;
            margin: 1rem 0;
            border-left: 3px solid var(--primary-color);
        }

        .info-box h5 {
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .info-box p {
            color: var(--text-light);
            font-size: 0.8rem;
            margin: 0;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="{{ $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png') }}" 
                 alt="{{ $ent->name }}" 
                 class="brand-logo">
            <h2 class="auth-title">Reset Password</h2>
            <p class="auth-subtitle">
                Enter your email address, phone number, or username and we'll send you a link to reset your password.
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
        <form id="resetForm" action="{{ url('auth/forgot-password') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Email, Phone, or Username</label>
                <div class="input-group">
                    <i class='bx bx-user input-icon'></i>
                    <input type="text" 
                           name="identifier" 
                           class="form-control with-icon" 
                           placeholder="Enter your email, phone number, or username"
                           value="{{ old('identifier') }}"
                           required
                           autocomplete="username">
                </div>
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
                    Send Reset Link
                </button>
            </div>
        </form>

        <div class="info-box">
            <h5><i class='bx bx-info-circle'></i> How it works</h5>
            <p>
                We'll search for your account using the information you provide. If found, we'll send a password reset link to your registered email address. The link will be valid for 60 minutes.
            </p>
        </div>

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
            const firstInput = document.querySelector('input[name="identifier"]');
            if (firstInput) {
                firstInput.focus();
            }
        });
    </script>
</body>
</html>