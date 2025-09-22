<?php
use App\Models\Utils;
// Ensure company data is available
if (!isset($company)) {
    $company = Utils::company();
}
$ent = Utils::ent();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company->name ?? Utils::company_name() }} - Sign In</title>

    <!-- Meta Tags -->
    <meta name="description" content="Sign in to {{ $company->app_name ?? Utils::app_name() }} school management system">
    <meta name="keywords" content="login, sign in, {{ $company->name ?? Utils::company_name() }}, school management">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">

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
            --primary-color: {{ $company && $company->primary_color ? $company->primary_color : '#01AEF0' }};
            --accent-color: {{ $company && $company->accent_color ? $company->accent_color : '#39CA78' }};
            --primary-light: {{ $company && $company->primary_color ? $company->primary_color : '#01AEF0' }}20;
            --text-dark: #2d3748;
            --text-light: #718096;
            --border-light: #e2e8f0;
            --success-color: var(--accent-color);
            --error-color: #dc3545;
            --white: #ffffff;
            --background-light: #f7fafc;
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
            max-width: 800px;
            width: 100%;
            min-height: 500px;
            display: flex;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .auth-left {
            flex: 1;
            background-color: var(--primary-color);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            border: 2px solid var(--white);
        }

        .brand-section {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .brand-link {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .brand-link:hover {
            text-decoration: none;
            color: inherit;
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .brand-link:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline-offset: 4px;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .brand-subtitle {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .welcome-text {
            text-align: center;
        }

        .welcome-title {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .welcome-description {
            font-size: 0.9rem;
            line-height: 1.5;
            opacity: 0.85;
        }

        .auth-right {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem;
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

        .form-footer {
            margin-top: 1rem;
            text-align: center;
        }

        .form-links {
            display: flex;
            justify-content: space-between;
            margin-top: 0.8rem;
            font-size: 0.85rem;
        }

        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
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

        .support-section {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-light);
            text-align: center;
        }

        .support-title {
            font-size: 0.85rem;
            color: var(--text-dark);
            margin-bottom: 0.8rem;
        }

        .support-contacts {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .support-contact {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--text-light);
            font-size: 0.75rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .support-contact:hover {
            color: var(--primary-color);
        }

        .remember-section {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .remember-section input[type="checkbox"] {
            margin: 0;
        }

        .remember-section label {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        /* Registration Section */
        .registration-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-light);
            text-align: center;
        }

        .registration-divider {
            margin-bottom: 1rem;
            color: var(--text-light);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .btn-registration {
            background-color: var(--accent-color);
            border: none;
            padding: 0.7rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.3s ease;
            color: var(--white);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            border-radius: 6px;
        }

        .btn-registration:hover {
            background-color: var(--accent-color);
            opacity: 0.9;
            color: var(--white);
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .registration-description {
            margin-top: 0.75rem;
            font-size: 0.8rem;
            color: var(--text-light);
            line-height: 1.4;
            margin-bottom: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                max-width: 400px;
            }

            .auth-left {
                padding: 1.5rem;
            }

            .auth-right {
                padding: 1.5rem;
            }

            .brand-logo {
                width: 60px;
                height: 60px;
            }

            .brand-name {
                font-size: 1.3rem;
            }

            .auth-title {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .auth-left,
            .auth-right {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <!-- Left Side - Branding -->
        <div class="auth-left">
            <div class="brand-section">
                <a href="{{ url('/') }}" class="brand-link">
                    <img src="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}"
                        alt="{{ $company->name ?? Utils::company_name() }}" class="brand-logo">
                    <h1 class="brand-name">{{ $company->name ?? Utils::company_name() }}</h1>
                    <p class="brand-subtitle">{{ $company->app_name ?? Utils::app_name() }}</p>
                </a>
            </div>

            <div class="welcome-text">
                <h2 class="welcome-title">Welcome Back!</h2>
                <p class="welcome-description">
                    {!! $company && $company->welcome_message ? $company->welcome_message : ($ent->welcome_message ?:
                        'Access your school management dashboard and continue managing your educational institution efficiently.') !!}
                </p>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="auth-right">
            <div class="auth-header">
                <h2 class="auth-title">Sign In</h2>
                <p class="auth-subtitle">Enter your credentials to access your account</p>
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

            <!-- Login Form -->
            <form id="loginForm" action="{{ url('auth/login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Email, Phone, or Username</label>
                    <div class="input-group">
                        <i class='bx bx-user input-icon'></i>
                        <input type="text" name="username" class="form-control with-icon"
                            placeholder="Enter your email, phone number, or username" value="{{ old('username') }}"
                            required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class='bx bx-lock input-icon'></i>
                        <input type="password" name="password" class="form-control with-icon"
                            placeholder="Enter your password" required autocomplete="current-password">
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
                        Sign In
                    </button>
                </div>

                <div class="form-links">
                    <a href="{{ url('auth/forgot-password') }}" class="auth-link">
                        <i class='bx bx-key'></i>
                        Forgot Password?
                    </a>
                    <a href="{{ url('auth/support') }}" class="auth-link">
                        <i class='bx bx-support'></i>
                        Need Help?
                    </a>
                </div>
            </form>

            <!-- Registration Section -->
            <div class="registration-section">
                <div class="registration-divider">
                    <span>New to our platform?</span>
                </div>
                <a href="{{ route('onboarding.step1') }}" class="btn btn-registration">
                    <i class='bx bx-plus-circle'></i>
                    Register New School
                </a>
                <p class="registration-description">
                    Start your journey with our comprehensive school management system
                </p>
            </div>

            <!-- Support Section -->
            <div class="support-section">
                <h4 class="support-title">Need assistance? Contact our support team</h4>
                <div class="support-contacts">
                    <a href="tel:{{ $company && $company->phone ? $company->phone : Utils::get_support_phone() }}" class="support-contact">
                        <i class='bx bx-phone'></i>
                        <span>{{ $company && $company->phone ? $company->phone : Utils::get_support_phone() }}</span>
                    </a>
                    <a href="mailto:{{ $company && $company->email ? $company->email : Utils::get_support_email() }}" class="support-contact">
                        <i class='bx bx-envelope'></i>
                        <span>{{ $company && $company->email ? $company->email : Utils::get_support_email() }}</span>
                    </a>
                    <a href="{{ Utils::get_whatsapp_link() }}" target="_blank" class="support-contact">
                        <i class='bx bxl-whatsapp'></i>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
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
            const firstInput = document.querySelector('input[name="username"]');
            if (firstInput) {
                firstInput.focus();
            }
        });
    </script>
</body>

</html>
