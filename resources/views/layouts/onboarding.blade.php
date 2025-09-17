<?php
use App\Models\Utils;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name') . ' | Get Started')</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Get started with ' . config('app.name') . ' - the comprehensive school management system.')">
    <meta name="keywords" content="school management system, education software, school registration, onboarding">
    <meta name="author" content="{{ Utils::app_name() }}">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/8tech.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Onboarding CSS (extends Bootstrap) -->
    <link rel="stylesheet" href="{{ asset('css/onboarding.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="onboarding-container">
        <!-- Left Sidebar -->
        <div class="onboarding-left">
            <!-- Brand Section -->
            <div class="sidebar-brand">
                <img src="{{ asset('assets/8tech.png') }}" alt="{{ Utils::app_name() }}" class="brand-logo">
                <div class="brand-info">
                    <h1 class="brand-name">{{ Utils::app_name() }}</h1>
                    <span class="brand-subtitle">School Management System</span>
                </div>
            </div>
            
            <!-- Progress Section -->
            <div class="sidebar-progress">
                <h3 class="progress-title">Setup Progress</h3>
                @yield('progress-indicator')
            </div>
            
            <!-- Stats Section -->
            <div class="sidebar-stats">
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Schools Registered</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">50K+</span>
                    <span class="stat-label">Students Managed</span>
                </div>
            </div>
        </div>
        
        <!-- Right Content Area -->
        <div class="onboarding-right">
            <div class="content-wrapper">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <footer class="content-footer">
                <div class="footer-links">
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                </div>
                <div class="footer-info">
                    <span>&copy; {{ date('Y') }} {{ Utils::app_name() }}. All rights reserved.</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    @stack('scripts')
</body>
</html>
