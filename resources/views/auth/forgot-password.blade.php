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

        .info-box {
            background: var(--background-light);
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            border-left: 4px solid var(--primary-color);
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
        <form id="resetForm" action="{{ admin_url('auth/forgot-password') }}" method="POST">
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
                <button type="submit" class="btn btn-primary" id="resetBtn">
                    <span class="btn-text">Send Reset Link</span>
                    <span class="spinner d-none"></span>
                </button>
            </div>
        </form>

        <div class="info-box">
            <h5><i class='bx bx-info-circle'></i> How it works</h5>
            <p>
                We'll search for your account using the information you provide. If found, we'll send a password reset link to your registered email address. The link will be valid for 60 minutes.
            </p>
        </div>

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
            // Form submission handling
            $('#resetForm').on('submit', function() {
                const $form = $(this);
                const $btn = $('#resetBtn');
                const $btnText = $btn.find('.btn-text');
                const $spinner = $btn.find('.spinner');

                // Show loading state
                $btn.addClass('loading');
                $btnText.text('Sending...');
                $spinner.removeClass('d-none');
                
                // Disable form
                $form.find('input, button').prop('disabled', true);
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);

            // Focus first input
            $('input[name="identifier"]').focus();

            // Enter key handling
            $('input').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#resetForm').submit();
                }
            });
        });
    </script>
</body>
</html>