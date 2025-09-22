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
    <title>@yield('title', (($company->app_name ?? Utils::app_name()) . ' | School Management System'))</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', (($company->app_name ?? Utils::app_name()) . ' is a comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in schools.'))">
    <meta name="keywords" content="school management system, education software, school administration, student management, teacher tools, school communication, online learning, school software, education technology">
    <meta name="author" content="{{ $company->name ?? Utils::company_name() }}">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ ($company->app_name ?? Utils::app_name()) . ' | School Management System' }}">
    <meta property="og:description" content="{{ ($company->app_name ?? Utils::app_name()) . ' helps schools manage their operations efficiently with advanced tools and features.' }}">
    <meta property="og:image" content="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern Public CSS -->
    <link rel="stylesheet" href="{{ asset('css/modern-public.css') }}">

    @if (isset($company) && $company && $company->primary_color && $company->accent_color)
        <!-- Dynamic Company Branding Colors -->
        <style>
            :root {
                --primary-color: {{ $company->primary_color }} !important;
                --accent-color: {{ $company->accent_color }} !important;
            }
        </style>
    @endif

    @yield('head-styles')

    @stack('styles')
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ url('/') }}" class="logo">
                    <img src="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}" alt="{{ $company->name ?? Utils::company_name() }}">
                    {{ $company->name ?? Utils::company_name() }}
                </a>

                <div class="header-actions">
                    <a href="{{ url('access-system') }}" class="btn btn-primary">
                        Access the System
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
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>{{ $company->name ?? Utils::company_name() }}</h4>
                    <p>Transforming education through innovative school management solutions.</p>
                </div>

                <div class="footer-section">
                    <h4>Contact</h4>
                    <a href="mailto:{{ $company && $company->email ? $company->email : 'info@newlinetech.com' }}">{{ $company && $company->email ? $company->email : 'info@newlinetech.com' }}</a>
                    <a href="tel:{{ $company && $company->phone ? str_replace([' ', '(', ')', '-'], '', $company->phone) : '+15551234567' }}">{{ $company && $company->phone ? $company->phone : '+1 (555) 123-4567' }}</a>
                </div>

                <div class="footer-section">
                    <h4>System Access</h4>
                    <a href="{{ url('access-system') }}">Access the System</a>
                    <a href="{{ url('/admin/auth/login') }}">Admin Portal</a>
                    <a href="{{ route('knowledge-base.index') }}">Knowledge Base</a>
                    <a href="{{ url('/auth/support') }}">Support Center</a>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $company->name ?? Utils::company_name() }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
