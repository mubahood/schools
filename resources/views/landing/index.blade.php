<?php
use App\Models\Utils;
?>
@extends('layouts.modern-public')

@section('title', ($company ? $company->name : 'Newline Technologies') . ' - School Management System | Complete Education Solution')
@section('meta_description', 'Transform your educational institution with our comprehensive school management system. Streamline operations, enhance communication, and improve student outcomes with advanced tools designed for modern schools.')
@section('meta_keywords', 'school management system, education software, student information system, school administration, academic management, teacher portal, parent communication, school operations, education technology, LMS')

@section('og_title', ($company ? $company->name : 'Newline Technologies') . ' - Complete School Management Solution')
@section('og_description', 'Revolutionary school management platform trusted by hundreds of educational institutions. Streamline operations, enhance communication, and boost academic excellence.')
@section('og_type', 'website')

@section('twitter_title', ($company ? $company->name : 'Newline Technologies') . ' - School Management System')
@section('twitter_description', 'Transform your school operations with our comprehensive management platform. Enhance communication and improve student outcomes.')

@push('structured-data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "{{ $company ? $company->app_name : 'School Management System' }}",
    "description": "Comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in educational institutions",
    "url": "{{ url('/') }}",
    "applicationCategory": "EducationalApplication",
    "operatingSystem": "Web-based",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD",
        "description": "Free trial available"
    },
    "provider": {
        "@type": "Organization",
        "name": "{{ $company ? $company->name : 'Newline Technologies' }}",
        "url": "{{ url('/') }}",
        "logo": "{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}"
    },
    "featureList": [
        "Student Information Management",
        "Academic Records Management", 
        "Fee Management System",
        "Parent-Teacher Communication",
        "Report Card Generation",
        "Attendance Tracking",
        "Examination Management",
        "Transport Management",
        "Library Management",
        "Financial Management"
    ],
    "screenshot": "{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}",
    "softwareVersion": "2024",
    "dateModified": "{{ date('Y-m-d') }}",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "ratingCount": "150",
        "bestRating": "5",
        "worstRating": "1"
    }
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "{{ $company ? $company->name : 'Newline Technologies' }} - School Management System",
    "description": "Transform your educational institution with our comprehensive school management system",
    "url": "{{ url('/') }}",
    "mainEntity": {
        "@type": "Organization",
        "name": "{{ $company ? $company->name : 'Newline Technologies' }}",
        "description": "Leading provider of school management solutions"
    },
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "{{ url('/') }}"
            }
        ]
    }
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "What is a school management system?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "A school management system is a comprehensive software solution that helps educational institutions manage their daily operations, including student information, academic records, fee management, and parent-teacher communication."
            }
        },
        {
            "@type": "Question", 
            "name": "How does the school management system improve efficiency?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Our system automates administrative tasks, centralizes data management, enables real-time communication, and provides detailed reporting capabilities, significantly reducing manual work and improving operational efficiency."
            }
        },
        {
            "@type": "Question",
            "name": "Is training provided for using the system?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, we provide comprehensive training and ongoing support to ensure your staff can effectively use all features of the school management system."
            }
        }
    ]
}
</script>
@endpush

@section('head-styles')
<style>
    :root {
        --primary-color: {{ $company && $company->primary_color ? $company->primary_color : '#01AEF0' }};
        --accent-color: {{ $company && $company->accent_color ? $company->accent_color : '#39CA78' }};
    }
</style>
@endsection

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>
                    School Management System by
                    <span class="highlight">{{ $company ? $company->name : 'Newline Technologies' }}</span>
                </h1>
                <p>
                    Comprehensive platform designed for modern educational institutions. 
                    Streamline operations, enhance communication, and improve student outcomes with our advanced management tools.
                </p>
                <div class="hero-actions">
                    <a href="{{ url('access-system') }}" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Access the System
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="logo-container">
                    <img src="{{ $company && $company->logo ? Utils::img_url($company->logo) : Utils::get_logo() }}" 
                         alt="{{ $company ? $company->name : 'Newline Technologies' }} Logo"
                         width="200"
                         height="200"
                         loading="eager"
                         decoding="async">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- System Introduction Video Section -->
<section class="section video-section">
    <div class="container">
        <div class="video-content">
            <div class="video-header text-center">
                <h2 class="video-title">See {{ $company ? $company->app_name : 'Our System' }} in Action</h2>
                <p class="video-subtitle">Watch this comprehensive overview to understand how our school management system can transform your institution</p>
            </div>
            
            <div class="video-wrapper">
                <div class="video-container">
                    <iframe 
                        src="https://www.youtube.com/embed/-4j5okWNORg?rel=0&showinfo=0&modestbranding=1&playsinline=1" 
                        title="School Management System Introduction"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="video-overlay">
                    <button class="play-button" onclick="playVideo()">
                        <i class="fas fa-play"></i>
                    </button>
                </div>
            </div>
            
            {{-- <div class="video-features">
                <div class="video-feature-grid">
                    <div class="video-feature">
                        <i class="fas fa-clock"></i>
                        <span>5-minute overview</span>
                    </div>
                    <div class="video-feature">
                        <i class="fas fa-desktop"></i>
                        <span>Live system demo</span>
                    </div>
                    <div class="video-feature">
                        <i class="fas fa-lightbulb"></i>
                        <span>Key features walkthrough</span>
                    </div>
                    <div class="video-feature">
                        <i class="fas fa-rocket"></i>
                        <span>Quick setup guide</span>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section section-light">
    <div class="container">
        <div class="section-title">
            <h2>Core Features</h2>
            <p>Essential tools designed to simplify school administration and enhance educational outcomes</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card feature-card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="card-title">Student Management</h5>
                <p class="card-text">
                    Complete student information system with detailed profiles, academic records, and comprehensive progress tracking capabilities.
                </p>
            </div>
            
            <div class="card feature-card">
                <div class="card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h5 class="card-title">Teacher Portal</h5>
                <p class="card-text">
                    Comprehensive tools for educators including digital gradebook, attendance tracking, and seamless parent communication.
                </p>
            </div>
            
            <div class="card feature-card">
                <div class="card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h5 class="card-title">Smart Scheduling</h5>
                <p class="card-text">
                    Automated timetable generation with conflict detection and real-time schedule updates for all stakeholders.
                </p>
            </div>
            
            <div class="card feature-card">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h5 class="card-title">Analytics & Reports</h5>
                <p class="card-text">
                    Detailed insights and customizable reports to track performance trends and make data-driven decisions.
                </p>
            </div>
            
            <div class="card feature-card">
                <div class="card-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h5 class="card-title">Mobile Access</h5>
                <p class="card-text">
                    Fully responsive interface accessible anywhere, anytime on any device for seamless management on-the-go.
                </p>
            </div>
            
            <div class="card feature-card">
                <div class="card-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h5 class="card-title">Secure Platform</h5>
                <p class="card-text">
                    Enterprise-grade security with advanced data encryption, automated backups, and 99.9% uptime guarantee.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- School Types Section -->
<section class="section school-types-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Supporting All Types of Educational Institutions</h2>
            <p class="section-subtitle">Our comprehensive system adapts to meet the unique needs of every educational institution</p>
        </div>
        
        <div class="school-types-grid">
            <div class="school-type-card" data-type="primary">
                <div class="school-type-header">
                    <div class="school-type-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3 class="school-type-title">Primary Schools</h3>
                    <p class="school-type-subtitle">Foundation education management</p>
                </div>
                <div class="school-type-content">
                    <div class="school-type-features">
                        <div class="feature-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Early childhood tracking</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-book-open"></i>
                            <span>Basic curriculum management</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span>Parent-teacher communication</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-star"></i>
                            <span>Progress assessment tools</span>
                        </div>
                    </div>
                    <div class="school-type-badge">
                        <span>Ages 5-12</span>
                    </div>
                </div>
            </div>
            
            <div class="school-type-card featured" data-type="secondary">
                <div class="featured-badge">Most Common</div>
                <div class="school-type-header">
                    <div class="school-type-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="school-type-title">Secondary Schools</h3>
                    <p class="school-type-subtitle">Advanced academic management</p>
                </div>
                <div class="school-type-content">
                    <div class="school-type-features">
                        <div class="feature-item">
                            <i class="fas fa-calculator"></i>
                            <span>Subject-based learning</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Exam & grading systems</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>Timetable management</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-trophy"></i>
                            <span>Academic performance tracking</span>
                        </div>
                    </div>
                    <div class="school-type-badge">
                        <span>Ages 13-18</span>
                    </div>
                </div>
            </div>
            
            <div class="school-type-card" data-type="institutions">
                <div class="school-type-header">
                    <div class="school-type-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3 class="school-type-title">Higher Institutions</h3>
                    <p class="school-type-subtitle">Semester-based education</p>
                </div>
                <div class="school-type-content">
                    <div class="school-type-features">
                        <div class="feature-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>1st & 2nd semester system</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-book"></i>
                            <span>Course unit management</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Credit hours tracking</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-certificate"></i>
                            <span>Degree program management</span>
                        </div>
                    </div>
                    <div class="school-type-badge">
                        <span>Higher Ed</span>
                    </div>
                </div>
            </div>
            
          {{--   <div class="school-type-card" data-type="religious">
                <div class="school-type-header">
                    <div class="school-type-icon">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <h3 class="school-type-title">Religious Schools</h3>
                    <p class="school-type-subtitle">Secular & theology education</p>
                </div>
                <div class="school-type-content">
                    <div class="school-type-features">
                        <div class="feature-item">
                            <i class="fas fa-quran"></i>
                            <span>Arabic language support</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-pray"></i>
                            <span>Islamic studies curriculum</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-moon"></i>
                            <span>Prayer time integration</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-hands"></i>
                            <span>Religious event management</span>
                        </div>
                    </div>
                    <div class="school-type-badge">
                        <span>Faith-Based</span>
                    </div>
                </div>
            </div> --}}
        </div>
        
        {{-- <div class="school-types-footer">
            <div class="universal-features">
                <h4>Universal Features Across All Institution Types</h4>
                <div class="universal-grid">
                    <div class="universal-item">
                        <i class="fas fa-cog"></i>
                        <span>Customizable workflows</span>
                    </div>
                    <div class="universal-item">
                        <i class="fas fa-language"></i>
                        <span>Multi-language support</span>
                    </div>
                    <div class="universal-item">
                        <i class="fas fa-sync"></i>
                        <span>Flexible academic years</span>
                    </div>
                    <div class="universal-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure data management</span>
                    </div>
                    <div class="universal-item">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Mobile accessibility</span>
                    </div>
                    <div class="universal-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Comprehensive reporting</span>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</section>

<!-- Sample Documents Section -->
<section class="section sample-documents-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Sample Documents</h2>
            <p class="section-subtitle">Explore the professional documents and reports generated by our system</p>
        </div>
        
        <div class="documents-grid">
            <div class="document-card" data-category="admissions">
                <div class="document-icon">
                    <i class="fas fa-file-text"></i>
                </div>
                <h3 class="document-title">Admission Letter</h3>
                <p class="document-description">Professional admission letters with school branding</p>
                                <a href="https://drive.google.com/file/d/1Ehi7UpulNLCuzNnGwNdyGZHvOh_5nkXF/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="financial">
                <div class="document-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 class="document-title">Payment Receipt</h3>
                <p class="document-description">Detailed payment receipts for fees and services</p>
                                <a href="https://drive.google.com/file/d/1xYFRcUgf51cDy6LuL0wzmxPuv6qZnC71/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="financial">
                <div class="document-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="document-title">Financial Report</h3>
                <p class="document-description">Comprehensive financial analysis and reports</p>
                                <a href="https://drive.google.com/file/d/1MkUwdecoOY-pYLV428nws7-aG-9OeTVr/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="financial">
                <div class="document-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="document-title">Demand Notice (List)</h3>
                <p class="document-description">Batch demand notices for outstanding payments</p>
                                <a href="https://drive.google.com/file/d/1h6uL_ULyZKPwqS5sCHp8DlMU1LA3NmAx/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="access">
                <div class="document-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="document-title">Gate Pass</h3>
                <p class="document-description">Digital gate passes for visitor and student access</p>
                                <a href="https://drive.google.com/file/d/1MtMs2cMl0KaQGpWKr0rzzHDOA6wJMNtk/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="access">
                <div class="document-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3 class="document-title">Meal Cards</h3>
                <p class="document-description">Digital meal cards for cafeteria services</p>
                                <a href="https://drive.google.com/file/d/1hAzEH1UomREZ3ongXZoYP-cgMVgmPQ5Q/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="financial">
                <div class="document-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="document-title">Demand Notice</h3>
                <p class="document-description">Individual demand notices for fee collection</p>
                                <a href="https://drive.google.com/file/d/1Fu6x8hb1CeiCPJZvFCPu3GTGClWz20lq/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="identification">
                <div class="document-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <h3 class="document-title">Student ID Cards</h3>
                <p class="document-description">Professional student identification cards</p>
                                <a href="https://drive.google.com/file/d/1iN7eg2Qy0yBnOhWce7BcAHZlEB50dJjp/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="identification">
                <div class="document-icon">
                    <i class="fas fa-id-badge"></i>
                </div>
                <h3 class="document-title">Employee ID Cards</h3>
                <p class="document-description">Staff identification and access cards</p>
                                <a href="https://drive.google.com/file/d/1Vg6Nx-a0z3pRN9nsufod4rOP7NqH1yx3/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="academic">
                <div class="document-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="document-title">Batch Report Cards</h3>
                <p class="document-description">Comprehensive batch student report cards</p>
                                <a href="https://drive.google.com/file/d/1hfy5kKJJWI_GDPxbIljknfsZbYFErAek/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="academic">
                <div class="document-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="document-title">Theology Report Cards</h3>
                <p class="document-description">Specialized report cards with theology subjects</p>
                                <a href="https://drive.google.com/file/d/1UbE_dHdC10P9RM6N3B_I602JSD6RNfU6/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="academic">
                <div class="document-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="document-title">Single Report Card</h3>
                <p class="document-description">Individual student academic report card</p>
                                <a href="https://drive.google.com/file/d/1jMNS8Tq8XZQYiebiwPInclyuZ0K0mJWi/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>

            <div class="document-card" data-category="academic">
                <div class="document-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h3 class="document-title">Exam Assessment</h3>
                <p class="document-description">Detailed examination assessment reports</p>
                                <a href="https://drive.google.com/file/d/1km_w1z5oz66aOZGQCNmdtDqYnnWkKLkL/view?usp=drive_link" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                    View Sample
                </a>
            </div>
        </div>

        <div class="section-footer text-center">
            <a href="https://drive.google.com/file/d/1km_w1z5oz66aOZGQCNmdtDqYnnWkKLkL/view?usp=drive_link" target="_blank" class="btn btn-primary">
                <i class="fas fa-download"></i>
                View All Documents
            </a>
        </div>
    </div>
</section>



<!-- Pricing Section -->
<section class="section pricing-section" id="pricing">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-subtitle">Three packages sized by your active students. Start with a 30-day free trial — no card needed.</p>
            <div class="pricing-toggle" role="group" aria-label="Billing period">
                <button type="button" class="pt-btn active" data-period="6m">6 months</button>
                <button type="button" class="pt-btn" data-period="12m">12 months</button>
            </div>
        </div>

        <div class="pricing-grid">
            <div class="pricing-card" data-tier="starter">
                <div class="pricing-header">
                    <div class="pricing-icon"><i class="fas fa-school"></i></div>
                    <h3 class="pricing-title">Starter</h3>
                    <p class="pricing-description">For nurseries and small schools</p>
                </div>
                <div class="pricing-details">
                    <div class="pricing-range">Up to 100 active students</div>
                    <div class="pricing-amount">
                        <span class="currency">UGX</span>
                        <span class="price" data-6m="100,000" data-12m="200,000">100,000</span>
                        <span class="period pt-period">per 6 months</span>
                    </div>
                    <div class="pricing-calculation"><small class="pt-inst" data-6m="33,334" data-12m="66,667">or 3 instalments of UGX 33,334</small></div>
                </div>
                <div class="pricing-features">
                    <div class="feature"><i class="fas fa-check"></i><span>Students, parents &amp; staff</span></div>
                    <div class="feature"><i class="fas fa-check"></i><span>Fees, receipts &amp; SchoolPay</span></div>
                    <div class="feature"><i class="fas fa-check"></i><span>Report cards &amp; assessments</span></div>
                    <div class="feature"><i class="fas fa-check"></i><span>Parent mobile app</span></div>
                </div>
            </div>

            <div class="pricing-card featured" data-tier="growth">
                <div class="popular-badge">Most Popular</div>
                <div class="pricing-header">
                    <div class="pricing-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3 class="pricing-title">Growth</h3>
                    <p class="pricing-description">For established primary and secondary schools</p>
                </div>
                <div class="pricing-details">
                    <div class="pricing-range">101 – 500 active students</div>
                    <div class="pricing-amount">
                        <span class="currency">UGX</span>
                        <span class="price" data-6m="300,000" data-12m="600,000">300,000</span>
                        <span class="period pt-period">per 6 months</span>
                    </div>
                    <div class="pricing-calculation"><small class="pt-inst" data-6m="100,000" data-12m="200,000">or 3 instalments of UGX 100,000</small></div>
                </div>
                <div class="pricing-features">
                    <div class="feature"><i class="fas fa-check"></i><span>Everything in Starter</span></div>
                    <div class="feature"><i class="fas fa-check"></i><span>Bulk SMS &amp; messaging</span></div>
                    <div class="feature"><i class="fas fa-check"></i><span>Priority support</span></div>
                </div>
            </div>

            <div class="pricing-card" data-tier="scale">
                <div class="pricing-header">
                    <div class="pricing-icon"><i class="fas fa-university"></i></div>
                    <h3 class="pricing-title">Scale</h3>
                    <p class="pricing-description">For large schools and multi-section institutions</p>
                </div>
                <div class="pricing-details">
                    <div class="pricing-range">501 – 1,000 active students</div>
                    <div class="pricing-amount">
                        <span class="currency">UGX</span>
                        <span class="price" data-6m="700,000" data-12m="1,400,000">700,000</span>
                        <span class="period pt-period">per 6 months</span>
                    </div>
                    <div class="pricing-calculation"><small class="pt-inst" data-6m="233,334" data-12m="466,667">or 3 instalments of UGX 233,334</small></div>
                </div>
                <div class="pricing-features">
                    <div class="feature"><i class="fas fa-check"></i><span>Everything in Growth</span></div>
                    <div class="feature"><i class="fas fa-check"></i><span>Dedicated onboarding</span></div>
                    <div class="feature"><i class="fas fa-check"></i><span>Priority support</span></div>
                </div>
            </div>
        </div>

        <p class="text-center" style="margin-top:1.5rem;color:#6c757d;font-size:.9rem">
            Pay online by MTN Mobile Money, Airtel Money or card, or by bank transfer. More than 1,000 students? <a href="#contact">Talk to us</a> for a tailored package.
        </p>
    </div>
</section>
<style>
  .pricing-toggle{display:inline-flex;border:1px solid #cfd6df;border-radius:999px;padding:3px;margin-top:1rem;background:#fff}
  .pricing-toggle .pt-btn{border:0;background:transparent;padding:.45rem 1.2rem;border-radius:999px;font-weight:600;cursor:pointer;color:#555}
  .pricing-toggle .pt-btn.active{background:#1a5c52;color:#fff}
</style>
<script>
(function(){
  var btns=document.querySelectorAll('.pricing-toggle .pt-btn'); if(!btns.length) return;
  btns.forEach(function(b){ b.addEventListener('click',function(){
    btns.forEach(function(x){x.classList.remove('active')}); b.classList.add('active');
    var p=b.dataset.period;
    document.querySelectorAll('.pricing-card .price').forEach(function(el){ el.textContent=el.dataset[p]; });
    document.querySelectorAll('.pt-period').forEach(function(el){ el.textContent='per '+(p==='12m'?'12':'6')+' months'; });
    document.querySelectorAll('.pt-inst').forEach(function(el){ el.textContent='or 3 instalments of UGX '+el.dataset[p]; });
  });});
})();
</script>

<!-- Call to Action Section -->
<section class="section cta-section">
    <div class="container">
        <div class="cta-content">
            <div class="cta-text">
                <h2>Ready to Transform Your School?</h2>
                <p>Join hundreds of educational institutions that have streamlined their operations with our comprehensive school management platform.</p>
            </div>
            <div class="cta-actions">
                <a href="{{ url('access-system') }}" class="btn btn-primary btn-large">
                    <i class="fas fa-rocket"></i>
                    Access the System
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Video functionality
function playVideo() {
    const overlay = document.querySelector('.video-overlay');
    const iframe = document.querySelector('.video-container iframe');
    
    if (overlay && iframe) {
        overlay.classList.add('hidden');
        // Add autoplay parameter to start video
        const currentSrc = iframe.src;
        if (!currentSrc.includes('autoplay=1')) {
            iframe.src = currentSrc + '&autoplay=1';
        }
    }
}

// Initialize modal functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Animate document cards on scroll (reduced animation for smaller cards)
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -30px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 50); // Faster animation for smaller cards
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Set initial state and observe cards
    document.querySelectorAll('.document-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)'; // Smaller initial offset
        card.style.transition = 'all 0.4s ease'; // Faster transition
        observer.observe(card);
    });
    
    // Add click handlers for better mobile experience
    document.querySelectorAll('.document-card').forEach(card => {
        card.addEventListener('touchstart', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        card.addEventListener('touchend', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>
@endpush
