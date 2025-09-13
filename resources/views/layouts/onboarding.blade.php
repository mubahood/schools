<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name') . ' | Get Started')</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Get started with ' . config('app.name') . ' - the comprehensive school management system.')">
    <meta name="keywords" content="school management system, education software, school registration, onboarding">
    <meta name="author" content="School Dynamics">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/8tech.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Onboarding CSS -->
    <link rel="stylesheet" href="{{ asset('css/onboarding.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Header with Progress -->
    <header class="onboarding-header">
        <div class="header-container">
            <div class="brand-section">
                <img src="{{ asset('assets/8tech.png') }}" alt="{{ config('app.name') }}" class="brand-logo">
                <div class="brand-info">
                    <h1 class="brand-name">{{ config('app.name') }}</h1>
                    <span class="brand-subtitle">School Management System</span>
                </div>
            </div>
            
            <div class="progress-section">
                @yield('progress-indicator')
            </div>
        </div>
    </header>

    <!-- Main Content -->
        <!-- Main Content -->
    <main class="onboarding-main">
        <div class="content-container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="onboarding-footer">
        <div class="footer-container">
            <div class="footer-links">
                <a href="#" class="footer-link">Help Center</a>
                <a href="#" class="footer-link">Privacy Policy</a>
                <a href="#" class="footer-link">Terms of Service</a>
            </div>
            <div class="footer-info">
                <span>&copy; {{ date('Y') }} School Dynamics. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>
                    <span>Quick Setup</span>
                </div>
                <div class="feature-item">
                    <i class='bx bx-check'></i>
                    <span>Secure & Reliable</span>
                </div>
                <div class="feature-item">
                    <i class='bx bx-check'></i>
                    <span>24/7 Support</span>
                </div>
            </div>
            
            <div class="onboarding-stats">
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Schools</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">50K+</span>
                    <span class="stat-label">Students</span>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Main Content -->
        <div class="onboarding-right">
            <div class="onboarding-content">
                <!-- Back to Home Link -->
                <div class="back-to-home">
                    <a href="{{ url('/') }}" class="back-link">
                        <i class='bx bx-arrow-left'></i>
                        Back to Home
                    </a>
                </div>
                
                <!-- Main Content Area -->
                <div class="content-area">
                    @yield('content')
                </div>
                
                <!-- Help Section -->
                <div class="help-section">
                    <p>Need help? <a href="tel:+256701002020">+256 701 002 020</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarding JavaScript -->
    <script src="{{ asset('js/onboarding.js') }}"></script>
    @stack('scripts')
</body>
</html>
