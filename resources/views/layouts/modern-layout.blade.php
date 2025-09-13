<?php
use App\Models\Utils;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- Primary Meta Tags -->
    <title>{{ $title ?? Utils::app_name() . ' - Professional School Management System' }}</title>
    <meta name="title" content="{{ $title ?? Utils::app_name() . ' - Professional School Management System' }}">
    <meta name="description" content="{{ $description ?? 'Transform your school operations with ' . Utils::app_name() . ' - the comprehensive management platform trusted by hundreds of educational institutions.' }}">
    <meta name="keywords" content="school management system, education software, student information system, school administration, academic management, {{ Utils::app_name() }}">
    <meta name="author" content="{{ Utils::company_name() }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? Utils::app_name() . ' - Professional School Management System' }}">
    <meta property="og:description" content="{{ $description ?? 'Transform your school operations with comprehensive management tools' }}">
    <meta property="og:image" content="{{ Utils::get_logo() }}">
    <meta property="og:site_name" content="{{ Utils::app_name() }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $title ?? Utils::app_name() }}">
    <meta property="twitter:description" content="{{ $description ?? 'Professional School Management System' }}">
    <meta property="twitter:image" content="{{ Utils::get_logo() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ Utils::get_logo() }}">
    <link rel="apple-touch-icon" href="{{ Utils::get_logo() }}">
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Unified CSS -->
    <link rel="stylesheet" href="{{ url('css/public-unified.css') }}">
    
    @stack('styles')
    
    <!-- Schema.org structured data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ Utils::company_name() }}",
        "url": "{{ url('/') }}",
        "logo": "{{ Utils::get_logo() }}",
        "description": "Professional school management system providing comprehensive solutions for educational institutions",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "{{ Utils::get_company_address() }}"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "{{ Utils::get_support_phone() }}",
            "contactType": "customer service"
        },
        "sameAs": [
            "{{ Utils::get_whatsapp_link() }}"
        ]
    }
    </script>
</head>
<body>
    <!-- Page Loading -->
    <div class="page-loading active" id="page-loading">
        <div class="page-loading-inner">
            <div class="spinner"></div>
            <span>{{ Utils::app_name() }}</span>
        </div>
    </div>
    
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <nav class="navbar">
                <!-- Brand -->
                <a href="{{ url('/') }}" class="navbar-brand">
                    <img src="{{ Utils::get_logo() }}" alt="{{ Utils::app_name() }}" height="40">
                    <span>{{ Utils::app_name() }}</span>
                </a>
                
                <!-- Desktop Navigation -->
                <ul class="navbar-nav d-none d-lg-flex">
                    <li><a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Home</a></li>
                    <li><a href="{{ url('/#features') }}" class="nav-link">Features</a></li>
                    <li><a href="{{ url('/#schools') }}" class="nav-link">Schools</a></li>
                    <li><a href="{{ url('/#testimonials') }}" class="nav-link">Testimonials</a></li>
                    <li><a href="{{ url('/#contact') }}" class="nav-link">Contact</a></li>
                </ul>
                
                <!-- CTA Buttons -->
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ Utils::get_whatsapp_link() }}" class="btn btn-ghost d-none d-md-inline-flex" target="_blank">
                        <i class='bx bxl-whatsapp'></i>
                        Support
                    </a>
                    <a href="{{ url('/access-system') }}" class="btn btn-primary">
                        <i class='bx bx-log-in-circle'></i>
                        Access System
                    </a>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="btn btn-ghost d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                        <i class='bx bx-menu'></i>
                    </button>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Mobile Menu -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">{{ Utils::app_name() }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav">
                <li><a href="{{ url('/') }}" class="nav-link">Home</a></li>
                <li><a href="{{ url('/#features') }}" class="nav-link">Features</a></li>
                <li><a href="{{ url('/#schools') }}" class="nav-link">Schools</a></li>
                <li><a href="{{ url('/#testimonials') }}" class="nav-link">Testimonials</a></li>
                <li><a href="{{ url('/#contact') }}" class="nav-link">Contact</a></li>
            </ul>
            
            <hr class="my-4">
            
            <div class="d-grid gap-3">
                <a href="{{ Utils::get_whatsapp_link() }}" class="btn btn-outline" target="_blank">
                    <i class='bx bxl-whatsapp'></i>
                    WhatsApp Support
                </a>
                <a href="{{ url('/access-system') }}" class="btn btn-primary">
                    <i class='bx bx-log-in-circle'></i>
                    Access System
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <main style="padding-top: var(--header-height);">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray text-center text-lg-start">
        <div class="container section-sm">
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="mb-4">
                        <img src="{{ Utils::get_logo() }}" alt="{{ Utils::app_name() }}" height="40" class="mb-3">
                        <h5 class="mb-3">{{ Utils::app_name() }}</h5>
                        <p class="text-muted">
                            Transforming education through innovative school management solutions. 
                            Trusted by hundreds of institutions worldwide.
                        </p>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ url('/') }}" class="text-muted">Home</a></li>
                        <li><a href="{{ url('/#features') }}" class="text-muted">Features</a></li>
                        <li><a href="{{ url('/#schools') }}" class="text-muted">Schools</a></li>
                        <li><a href="{{ url('/access-system') }}" class="text-muted">Get Started</a></li>
                    </ul>
                </div>
                
                <!-- Features -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Features</h6>
                    <ul class="list-unstyled text-muted">
                        <li>Student Management</li>
                        <li>Academic Records</li>
                        <li>Financial Management</li>
                        <li>Communication Tools</li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="mb-3">Contact Us</h6>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class='bx bx-map text-primary'></i>
                            <span class="text-muted">{{ Utils::get_company_address() }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class='bx bx-phone text-primary'></i>
                            <a href="tel:{{ str_replace(' ', '', Utils::get_support_phone()) }}" class="text-muted">{{ Utils::get_support_phone() }}</a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class='bx bxl-whatsapp text-accent'></i>
                            <a href="{{ Utils::get_whatsapp_link() }}" class="text-muted" target="_blank">WhatsApp Support</a>
                        </div>
                    </div>
                    
                    <!-- Social Links -->
                    <div class="mt-4">
                        <a href="{{ Utils::get_whatsapp_link() }}" class="btn btn-sm btn-outline me-2" target="_blank">
                            <i class='bx bxl-whatsapp'></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Copyright -->
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; {{ date('Y') }} {{ Utils::company_name() }}. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex flex-wrap justify-content-md-end gap-3">
                        <span class="text-muted small">
                            <i class='bx bx-shield-check text-accent'></i> Secure
                        </span>
                        <span class="text-muted small">
                            <i class='bx bx-mobile text-primary'></i> Mobile Ready
                        </span>
                        <span class="text-muted small">
                            <i class='bx bx-support text-accent'></i> 24/7 Support
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top -->
    <button id="backToTop" class="btn btn-primary position-fixed" style="bottom: 2rem; right: 2rem; z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.3s ease;">
        <i class='bx bx-chevron-up'></i>
    </button>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Core JavaScript -->
    <script>
        // Page Loading
        window.addEventListener('load', function() {
            const pageLoading = document.getElementById('page-loading');
            pageLoading.classList.remove('active');
            setTimeout(() => {
                pageLoading.style.display = 'none';
            }, 400);
        });
        
        // Header Scroll Effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Back to Top Button
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.style.opacity = '1';
                backToTop.style.visibility = 'visible';
            } else {
                backToTop.style.opacity = '0';
                backToTop.style.visibility = 'hidden';
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerHeight = document.getElementById('header').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Mobile menu close on link click
        document.querySelectorAll('#mobileMenu .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('mobileMenu'));
                if (offcanvas) {
                    offcanvas.hide();
                }
            });
        });
        
        // Enhanced button interactions
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Performance optimization: Defer non-critical operations
        requestIdleCallback(() => {
            // Add any non-critical functionality here
            console.log('{{ Utils::app_name() }} - Ready');
        });
    </script>
    
    @stack('scripts')
</body>
</html>
