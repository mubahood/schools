<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Email Verification')</title>
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/bootstrap.css') }}" rel="stylesheet">
    
    <!-- BoxIcons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2c5aa0;
            --primary-light: #3b6bb8;
            --primary-dark: #1e3f72;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #e3f2fd;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --background-light: #ffffff;
            --text-light: #6c757d;
            --text-dark: #2c3e50;
            --border-color: #e9ecef;
            --box-shadow: 0 2px 20px rgba(44, 90, 160, 0.08);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 0;
            padding: 20px;
        }

        .verification-container {
            background: var(--background-light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 0;
            margin: 0 auto;
            max-width: 480px;
            width: 100%;
            position: relative;
            border: 1px solid var(--border-color);
        }

        .verification-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem 2rem 1.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .verification-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: white;
        }

        .verification-header p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
        }

        .verification-icon {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
        }

        .verification-icon i {
            font-size: 1.8rem;
            color: white;
        }

        .verification-content {
            padding: 2rem;
        }

        .alert {
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .alert-success {
            background: #f8fff9;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-warning {
            background: #fffbf0;
            color: #856404;
            border-color: #ffeaa7;
        }

        .alert-danger {
            background: #fff5f5;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .alert-info {
            background: var(--info-color);
            color: var(--primary-dark);
            border-color: #bee5eb;
        }

        .btn {
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }

        .btn-outline:hover {
            background: var(--light-color);
            color: var(--text-dark);
            text-decoration: none;
            border-color: var(--text-light);
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .verification-steps {
            background: var(--light-color);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .step {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem 0;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step.completed {
            color: var(--success-color);
        }

        .step.active {
            color: var(--primary-color);
            font-weight: 500;
        }

        .step.pending {
            color: var(--text-light);
        }

        .step-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .step.completed .step-icon {
            background: var(--success-color);
            color: white;
        }

        .step.active .step-icon {
            background: var(--primary-color);
            color: white;
        }

        .step.pending .step-icon {
            background: var(--border-color);
            color: var(--text-light);
        }

        .loading-spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .verification-container {
                max-width: none;
            }
            
            .verification-header {
                padding: 1.5rem;
            }
            
            .verification-header h1 {
                font-size: 1.3rem;
            }
            
            .verification-content {
                padding: 1.5rem;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.85rem;
            }
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--text-light) !important;
        }

        .mt-3 { margin-top: 1rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }

        .email-highlight {
            background: #f8f9fa;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            font-weight: 500;
            color: var(--text-dark);
            display: inline-block;
            margin: 0.5rem 0;
            border: 1px solid var(--border-color);
        }

        /* Remove excessive animations and progress indicators */
        .progress-indicator {
            display: none;
        }

        /* Simplified animation for page entrance */
        .verification-container {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="verification-container">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('scripts')
</body>
</html>