<?php
use App\Models\Utils;

// Ensure company data is available in layout
if (!isset($company)) {
    $company = Utils::company();
}
?>
<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- Primary Meta Tags -->
    <title>@yield('title', (($company->app_name ?? Utils::app_name()) . ' | School Management System'))</title>
    <meta name="title" content="@yield('title', (($company->app_name ?? Utils::app_name()) . ' | School Management System'))">
    <meta name="description" content="@yield('meta_description', (($company->app_name ?? Utils::app_name()) . ' is a comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in schools.'))">
    <meta name="keywords" content="@yield('meta_keywords', 'school management system, education software, school administration, student management, teacher tools, school communication, online learning, school software, education technology')">
    <meta name="author" content="{{ $company->name ?? Utils::company_name() }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="{{ $company && $company->primary_color ? $company->primary_color : '#01AEF0' }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $company->app_name ?? Utils::app_name() }}">
    <meta property="og:title" content="@yield('og_title', $company->app_name ?? Utils::app_name())">
    <meta property="og:description" content="@yield('og_description', ($company->app_name ?? Utils::app_name()) . ' helps schools manage their operations efficiently with advanced tools and features.')">
    <meta property="og:image" content="@yield('og_image', ($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo()))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $company->name ?? Utils::company_name() }} Logo">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@{{ $company->twitter_handle ?? Utils::company_name() }}">
    <meta name="twitter:creator" content="@{{ $company->twitter_handle ?? Utils::company_name() }}">
    <meta name="twitter:title" content="@yield('twitter_title', $company->app_name ?? Utils::app_name())">
    <meta name="twitter:description" content="@yield('twitter_description', ($company->app_name ?? Utils::app_name()) . ' helps schools manage their operations efficiently.')">
    <meta name="twitter:image" content="@yield('twitter_image', ($company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo()))">
    <meta name="twitter:image:alt" content="{{ $company->name ?? Utils::company_name() }} Logo">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="format-detection" content="telephone=no">
    <meta name="generator" content="{{ $company->app_name ?? Utils::app_name() }}">
    
    <!-- Favicon and App Icons -->
    <link rel="icon" type="image/x-icon" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">
    <link rel="apple-touch-icon" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ url('manifest.json') }}">
    
    <!-- iOS Safari Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $company->app_name ?? Utils::app_name() }}">
    
    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileColor" content="{{ $company && $company->primary_color ? $company->primary_color : '#01AEF0' }}">
    <meta name="msapplication-config" content="none">
    
    <!-- DNS Prefetch for Performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"></noscript>
    
    <link rel="preload" href="{{ asset('css/modern-public.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('css/modern-public.css') }}"></noscript>

    <!-- Google Fonts (fallback) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'">

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
    
    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ $company->name ?? Utils::company_name() }}",
        "alternateName": "{{ $company->app_name ?? Utils::app_name() }}",
        "url": "{{ url('/') }}",
        "logo": "{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}",
        "description": "{{ $company->app_name ?? Utils::app_name() }} is a comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in schools.",
        "foundingDate": "{{ $company->created_at ?? '2023' }}",
        "email": "{{ $company && $company->email ? $company->email : 'info@newlinetech.com' }}",
        "telephone": "{{ $company && $company->phone ? $company->phone : '+1-555-123-4567' }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "{{ $company && $company->address ? $company->address : '' }}",
            "addressLocality": "{{ $company && $company->city ? $company->city : '' }}",
            "addressRegion": "{{ $company && $company->state ? $company->state : '' }}",
            "postalCode": "{{ $company && $company->postal_code ? $company->postal_code : '' }}",
            "addressCountry": "{{ $company && $company->country ? $company->country : 'US' }}"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "{{ $company && $company->phone ? $company->phone : '+1-555-123-4567' }}",
            "contactType": "customer service",
            "email": "{{ $company && $company->email ? $company->email : 'info@newlinetech.com' }}",
            "availableLanguage": ["en"]
        },
        "sameAs": [
            @php
                $socialUrls = array_filter([
                    $company && $company->facebook_url ? $company->facebook_url : null,
                    $company && $company->twitter_url ? $company->twitter_url : null,
                    $company && $company->linkedin_url ? $company->linkedin_url : null,
                    $company && $company->instagram_url ? $company->instagram_url : null,
                    url('/knowledge-base')
                ]);
            @endphp
            @foreach($socialUrls as $index => $url)
                "{{ $url }}"@if($index < count($socialUrls) - 1),@endif
            @endforeach
        ],
        "knowsAbout": [
            "School Management System",
            "Education Technology",
            "Student Information System",
            "School Administration",
            "Academic Management",
            "Educational Software"
        ],
        "areaServed": {
            "@type": "Place",
            "name": "Worldwide"
        },
        "serviceType": "Education Technology Services",
        "founder": {
            "@type": "Organization",
            "name": "{{ $company->name ?? Utils::company_name() }}"
        }
    }
    </script>
    
    @stack('structured-data')
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ url('/') }}" class="logo">
                    <img src="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}" 
                         alt="{{ $company->name ?? Utils::company_name() }}" 
                         width="40" 
                         height="40"
                         loading="eager">
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
