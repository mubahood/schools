<?php
use App\Models\Utils;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', Utils::app_name() . ' | School Management System')</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', Utils::app_name() . ' is a comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in schools.')">>
    <meta name="keywords" content="school management system, education software, school administration, student management, teacher tools, school communication, online learning, school software, education technology">
    <meta name="author" content="{{ Utils::company_name() }}">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ Utils::app_name() }} | School Management System">
    <meta property="og:description" content="{{ Utils::app_name() }} helps schools manage their operations efficiently with advanced tools and features.">
    <meta property="og:image" content="{{ Utils::get_logo() }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ Utils::get_logo() }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Modern Public CSS -->
    <link rel="stylesheet" href="{{ asset('css/modern-public.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="modern-header">
        <div class="modern-container">
            <div class="header-content">
                <a href="{{ url('/') }}" class="logo">
                    <img src="{{ Utils::get_logo() }}" alt="{{ Utils::app_name() }}">
                    {{ Utils::app_name() }}
                </a>
                
                <nav class="nav-menu">
                    <a href="{{ url('/') }}" class="nav-link">Home</a>
                    <a href="{{ url('/about') }}" class="nav-link">About</a>
                    <a href="{{ url('/schools') }}" class="nav-link">Schools</a>
                    <a href="{{ url('/testimonials') }}" class="nav-link">Testimonials</a>
                    <a href="{{ url('/contact') }}" class="nav-link">Contact</a>
                </nav>
                
                <div class="header-actions">
                    <a href="{{ url('/access-system') }}" class="btn btn-primary">
                        <i class='bx bx-rocket'></i>
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="modern-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>{{ Utils::app_name() }}</h4>
                    <p>Transforming education through innovative school management solutions.</p>
                    <div class="flex gap-3 mt-4">
                        <a href="https://facebook.com/8technologies" target="_blank" style="color: var(--primary-color); font-size: 1.5rem;"><i class='bx bxl-facebook'></i></a>
                        <a href="https://twitter.com/8technologies" target="_blank" style="color: var(--primary-color); font-size: 1.5rem;"><i class='bx bxl-twitter'></i></a>
                        <a href="https://linkedin.com/company/8technologies" target="_blank" style="color: var(--primary-color); font-size: 1.5rem;"><i class='bx bxl-linkedin'></i></a>
                        <a href="https://instagram.com/8technologies" target="_blank" style="color: var(--primary-color); font-size: 1.5rem;"><i class='bx bxl-instagram'></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Features</h4>
                    <a href="{{ url('/about') }}">Student Management</a>
                    <a href="{{ url('/about') }}">Teacher Portal</a>
                    <a href="{{ url('/about') }}">Financial Management</a>
                    <a href="{{ url('/about') }}">Communication Hub</a>
                    <a href="{{ url('/about') }}">Analytics</a>
                </div>
                
                <div class="footer-section">
                    <h4>Resources</h4>
                    <a href="{{ url('/contact') }}">Documentation</a>
                    <a href="{{ url('/contact') }}">API Reference</a>
                    <a href="{{ url('/contact') }}">Support Center</a>
                    <a href="{{ url('/contact') }}">Training Videos</a>
                    <a href="{{ url('/contact') }}">Blog</a>
                </div>
                
                <div class="footer-section">
                    <h4>Company</h4>
                    <a href="{{ url('/about') }}">About Us</a>
                    <a href="{{ url('/schools') }}">Our Schools</a>
                    <a href="{{ url('/testimonials') }}">Testimonials</a>
                    <a href="{{ url('/contact') }}">Contact</a>
                    <a href="{{ url('/contact') }}">Privacy Policy</a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ Utils::app_name() }}. All rights reserved. Made by {{ Utils::company_name() }}.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
