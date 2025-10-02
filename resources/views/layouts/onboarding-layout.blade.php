<?php
use App\Models\Utils;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $description ?? Utils::app_name() . ' - Professional School Management System' }}">
    <meta name="keywords" content="school management, education software, student portal, administration">
    <meta name="author" content="{{ Utils::company_name() }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $title ?? Utils::app_name() }}">
    <meta property="og:description" content="{{ $description ?? 'Professional School Management System' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <title>{{ $title ?? Utils::app_name() }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    
    <!-- Google Fonts - Optimized loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Onboarding CSS -->
    <style>
        :root {
            /* Color Palette */
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            
            /* Neutral Colors */
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            
            /* Typography */
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            --font-size-4xl: 2.25rem;
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 0.75rem;
            --spacing-lg: 1rem;
            --spacing-xl: 1.5rem;
            --spacing-2xl: 2rem;
            --spacing-3xl: 3rem;
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family);
            font-size: var(--font-size-base);
            line-height: 1.6;
            color: var(--gray-700);
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .onboarding-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }
        
        .onboarding-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            max-width: 1200px;
            width: 100%;
            min-height: 600px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            transition: all 0.3s ease;
        }
        
        .explainer-side {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: var(--spacing-3xl);
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .explainer-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='m0 40l40-40h-40v40zm40 0v-40h-40l40 40z'/%3E%3C/g%3E%3C/svg%3E") repeat;
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
        }
        
        .explainer-content {
            position: relative;
            z-index: 2;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-2xl);
        }
        
        .brand-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            backdrop-filter: blur(10px);
        }
        
        .brand-text h1 {
            font-size: var(--font-size-xl);
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }
        
        .brand-text p {
            font-size: var(--font-size-sm);
            opacity: 0.9;
            margin: 0;
        }
        
        .explainer-title {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: var(--spacing-lg);
        }
        
        .explainer-description {
            font-size: var(--font-size-lg);
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: var(--spacing-2xl);
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            font-size: var(--font-size-base);
        }
        
        .feature-icon {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .action-side {
            padding: var(--spacing-3xl);
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--white);
        }
        
        .action-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }
        
        .action-title {
            font-size: var(--font-size-2xl);
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--spacing-sm);
        }
        
        .action-subtitle {
            color: var(--gray-500);
            font-size: var(--font-size-base);
        }
        
        .action-options {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }
        
        .option-card {
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
        }
        
        .option-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
            color: inherit;
        }
        
        .option-card:hover .option-icon {
            background: var(--primary-color);
            color: var(--white);
            transform: scale(1.1);
        }
        
        .option-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-md);
        }
        
        .option-icon {
            width: 48px;
            height: 48px;
            background: var(--gray-100);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--gray-600);
            transition: all 0.3s ease;
        }
        
        .option-content h3 {
            font-size: var(--font-size-lg);
            font-weight: 600;
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--gray-900);
        }
        
        .option-content p {
            color: var(--gray-500);
            font-size: var(--font-size-sm);
            margin: 0;
            line-height: 1.5;
        }
        
        .support-section {
            margin-top: var(--spacing-2xl);
            padding-top: var(--spacing-xl);
            border-top: 1px solid var(--gray-200);
            text-align: center;
        }
        
        .support-text {
            color: var(--gray-500);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .support-actions {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .support-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-lg);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--gray-600);
            font-size: var(--font-size-sm);
            transition: all 0.3s ease;
        }
        
        .support-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .support-btn.primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }
        
        .support-btn.primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: var(--white);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .onboarding-container {
                padding: var(--spacing-sm);
            }
            
            .onboarding-card {
                grid-template-columns: 1fr;
                min-height: auto;
            }
            
            .explainer-side {
                padding: var(--spacing-2xl) var(--spacing-xl);
                order: 2;
            }
            
            .action-side {
                padding: var(--spacing-2xl) var(--spacing-xl);
                order: 1;
            }
            
            .explainer-title {
                font-size: var(--font-size-2xl);
            }
            
            .explainer-description {
                font-size: var(--font-size-base);
            }
            
            .action-title {
                font-size: var(--font-size-xl);
            }
            
            .support-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .support-btn {
                width: 100%;
                justify-content: center;
                max-width: 200px;
            }
        }
        
        @media (max-width: 480px) {
            .explainer-side,
            .action-side {
                padding: var(--spacing-xl) var(--spacing-lg);
            }
            
            .option-header {
                gap: var(--spacing-md);
            }
            
            .option-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
        }
        
        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Focus States */
        .option-card:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        .support-btn:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="onboarding-container">
        <div class="onboarding-card">
            <div class="explainer-side">
                <div class="explainer-content">
                    <div class="brand-logo">
                        <div class="brand-icon">
                            <i class='bx bx-graduation'></i>
                        </div>
                        <div class="brand-text">
                            <h1>@yield('explainer-title', 'Welcome')</h1>
                        </div>
                    </div>
                    
                    <p class="explainer-description">
                        @yield('explainer-description', 'Get started with your journey')
                    </p>
                    
                    <ul class="feature-list">
                        @yield('features')
                    </ul>
                </div>
            </div>
            
            <div class="action-side">
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Enhanced keyboard navigation
        document.addEventListener('DOMContentLoaded', function() {
            const optionCards = document.querySelectorAll('.option-card');
            
            optionCards.forEach(card => {
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        card.click();
                    }
                });
                
                // Make cards focusable
                if (!card.hasAttribute('tabindex')) {
                    card.setAttribute('tabindex', '0');
                }
            });
        });
        
        // Smooth loading states
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a, .option-card');
            if (link && !link.target) {
                const icon = link.querySelector('i');
                if (icon && !icon.classList.contains('loading-spinner')) {
                    const originalIcon = icon.className;
                    icon.className = 'loading-spinner';
                    
                    // Restore icon if navigation fails
                    setTimeout(() => {
                        if (icon.className === 'loading-spinner') {
                            icon.className = originalIcon;
                        }
                    }, 3000);
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
