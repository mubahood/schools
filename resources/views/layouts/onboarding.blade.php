<?php
use App\Models\Utils;
// Ensure company data is available in layout
if (!isset($company)) {
    $company = Utils::company();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', (($company->app_name ?? Utils::app_name()) . ' | Get Started'))</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', ('Get started with ' . ($company->app_name ?? Utils::app_name()) . ' - the comprehensive school management system.'))">
    <meta name="keywords" content="school management system, education software, school registration, onboarding">
    <meta name="author" content="{{ $company->name ?? Utils::company_name() }}">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">
    
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
    
    @if (isset($company) && $company && $company->primary_color && $company->accent_color)
        <!-- Dynamic Company Branding Colors -->
        <style>
            :root {
                --primary-color: {{ $company->primary_color }} !important;
                --accent-color: {{ $company->accent_color }} !important;
                --primary-light: {{ $company->primary_color }}CC !important;
                --primary-dark: {{ $company->accent_color }} !important;
                --bs-primary: {{ $company->primary_color }} !important;
                --bs-success: {{ $company->primary_color }} !important;
            }
        </style>
    @endif
    
    @stack('styles')
</head>
<body>
    <!-- Mobile Header -->
    <div class="mobile-header">
        <div class="mobile-brand">
            <img src="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}" alt="{{ $company->name ?? Utils::company_name() }}" class="mobile-logo">
            <span class="mobile-brand-name">{{ $company->name ?? Utils::company_name() }}</span>
        </div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
            <i class='bx bx-menu'></i>
        </button>
    </div>
    
    <div class="onboarding-container">
        <!-- Left Sidebar -->
        <div class="onboarding-left" id="sidebar">
            <!-- Mobile Close Button -->
            <button class="mobile-close-btn" id="mobileCloseBtn" aria-label="Close menu">
                <i class='bx bx-x'></i>
            </button>
            
            <!-- Brand Section -->
            <div class="sidebar-brand">
                <img src="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}" alt="{{ $company->name ?? Utils::company_name() }}" class="brand-logo">
                <div class="brand-info">
                    <h1 class="brand-name">{{ $company->name ?? Utils::company_name() }}</h1>
                    <span class="brand-subtitle">{{ $company->app_name ?? Utils::app_name() }}</span>
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
        </div>
    </div>
    
    <!-- Footer (Outside container, at bottom of page) -->
    <footer class="onboarding-footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="#" class="footer-link">Help Center</a>
                <span class="footer-separator">•</span>
                <a href="#" class="footer-link">Privacy Policy</a>
                <span class="footer-separator">•</span>
                <a href="#" class="footer-link">Terms of Service</a>
            </div>
            <div class="footer-copyright">
                <span>&copy; {{ date('Y') }} {{ $company->name ?? Utils::company_name() }}. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Mobile Menu Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('mobileMenuToggle');
            const closeBtn = document.getElementById('mobileCloseBtn');
            const sidebar = document.getElementById('sidebar');
            const container = document.querySelector('.onboarding-container');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
            
            // Close sidebar when clicking outside on mobile
            if (container) {
                container.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && sidebar.classList.contains('active') && !sidebar.contains(e.target)) {
                        sidebar.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
