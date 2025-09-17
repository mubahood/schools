<?php
use App\Models\Utils;
$ent = Utils::ent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ent->name }} - Sign In</title>
    
    <!-- Meta Tags -->
    <meta name="description" content="Sign in to {{ $ent->name }} school management system">
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
            max-width: 900px;
            width: 100%;
            margin: 20px;
            min-height: 600px;
            display: flex;
        }

        .auth-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('{{ asset("assets/pattern.png") }}') repeat;
            opacity: 0.1;
            z-index: 1;
        }

        .auth-left > * {
            position: relative;
            z-index: 2;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid rgba(255,255,255,0.2);
        }

        .brand-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .brand-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .welcome-text {
            text-align: center;
        }

        .welcome-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .welcome-description {
            font-size: 0.95rem;
            line-height: 1.6;
            opacity: 0.9;
        }

        .auth-right {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
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

        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
        }

        .form-links {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: var(--secondary-color);
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

        .support-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-light);
            text-align: center;
        }

        .support-title {
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
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
            gap: 0.5rem;
            color: var(--text-light);
            font-size: 0.8rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .support-contact:hover {
            color: var(--primary-color);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                margin: 10px;
                min-height: auto;
            }

            .auth-left {
                padding: 2rem;
                text-align: center;
            }

            .auth-right {
                padding: 2rem;
            }

            .form-links {
                flex-direction: column;
                gap: 0.5rem;
            }

            .support-contacts {
                flex-direction: column;
                gap: 0.5rem;
            }
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
        <!-- Left Side - Branding -->
        <div class="auth-left">
            <div class="brand-section">
                <img src="{{ $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png') }}" 
                     alt="{{ $ent->name }}" 
                     class="brand-logo">
                <h1 class="brand-name">{{ $ent->name }}</h1>
                <p class="brand-subtitle">School Management System</p>
            </div>
            
            <div class="welcome-text">
                <h2 class="welcome-title">Welcome Back!</h2>
                <p class="welcome-description">
                    {{ $ent->welcome_message ?: 'Access your school management dashboard and continue managing your educational institution efficiently.' }}
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
            <form id="loginForm" action="{{ admin_url('auth/login') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Email, Phone, or Username</label>
                    <div class="input-group">
                        <i class='bx bx-user input-icon'></i>
                        <input type="text" 
                               name="username" 
                               class="form-control with-icon" 
                               placeholder="Enter your email, phone number, or username"
                               value="{{ old('username') }}"
                               required
                               autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class='bx bx-lock input-icon'></i>
                        <input type="password" 
                               name="password" 
                               class="form-control with-icon" 
                               placeholder="Enter your password"
                               required
                               autocomplete="current-password">
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" id="loginBtn">
                        <span class="btn-text">Sign In</span>
                        <span class="spinner d-none"></span>
                    </button>
                </div>

                <div class="form-links">
                    <a href="{{ admin_url('auth/forgot-password') }}" class="auth-link">
                        <i class='bx bx-key'></i>
                        Forgot Password?
                    </a>
                    <a href="{{ admin_url('auth/support') }}" class="auth-link">
                        <i class='bx bx-support'></i>
                        Need Help?
                    </a>
                </div>
            </form>

            <!-- Support Section -->
            <div class="support-section">
                <h4 class="support-title">Need assistance? Contact our support team</h4>
                <div class="support-contacts">
                    <a href="tel:{{ Utils::get_support_phone() }}" class="support-contact">
                        <i class='bx bx-phone'></i>
                        <span>{{ Utils::get_support_phone() }}</span>
                    </a>
                    <a href="mailto:{{ Utils::get_support_email() }}" class="support-contact">
                        <i class='bx bx-envelope'></i>
                        <span>{{ Utils::get_support_email() }}</span>
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

    <script>
        $(document).ready(function() {
            // Form submission handling
            $('#loginForm').on('submit', function() {
                const $form = $(this);
                const $btn = $('#loginBtn');
                const $btnText = $btn.find('.btn-text');
                const $spinner = $btn.find('.spinner');

                // Show loading state
                $btn.addClass('loading');
                $btnText.text('Signing In...');
                $spinner.removeClass('d-none');
                
                // Disable form
                $form.find('input, button').prop('disabled', true);
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);

            // Focus first input
            $('input[name="username"]').focus();

            // Enter key handling
            $('input').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#loginForm').submit();
                }
            });
        });
    </script>
</body>
</html>