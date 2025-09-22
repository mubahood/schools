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
            
            <div class="school-type-card" data-type="religious">
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
            </div>
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
                <button class="btn btn-outline btn-sm" onclick="previewDocument('admission-letter', 'https://drive.google.com/file/d/1Ehi7UpulNLCuzNnGwNdyGZHvOh_5nkXF/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="financial">
                <div class="document-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 class="document-title">Payment Receipt</h3>
                <p class="document-description">Detailed payment receipts for fees and services</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('receipt', 'https://drive.google.com/file/d/1xYFRcUgf51cDy6LuL0wzmxPuv6qZnC71/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="financial">
                <div class="document-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="document-title">Financial Report</h3>
                <p class="document-description">Comprehensive financial analysis and reports</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('financial-report', 'https://drive.google.com/file/d/1MkUwdecoOY-pYLV428nws7-aG-9OeTVr/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="financial">
                <div class="document-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="document-title">Demand Notice (List)</h3>
                <p class="document-description">Batch demand notices for outstanding payments</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('demand-notice-list', 'https://drive.google.com/file/d/1h6uL_ULyZKPwqS5sCHp8DlMU1LA3NmAx/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="access">
                <div class="document-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="document-title">Gate Pass</h3>
                <p class="document-description">Digital gate passes for visitor and student access</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('gate-pass', 'https://drive.google.com/file/d/1MtMs2cMl0KaQGpWKr0rzzHDOA6wJMNtk/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="access">
                <div class="document-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3 class="document-title">Meal Cards</h3>
                <p class="document-description">Digital meal cards for cafeteria services</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('meal-cards', 'https://drive.google.com/file/d/1hAzEH1UomREZ3ongXZoYP-cgMVgmPQ5Q/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="financial">
                <div class="document-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="document-title">Demand Notice</h3>
                <p class="document-description">Individual demand notices for fee collection</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('demand-notice', 'https://drive.google.com/file/d/1Fu6x8hb1CeiCPJZvFCPu3GTGClWz20lq/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="identification">
                <div class="document-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <h3 class="document-title">Student ID Cards</h3>
                <p class="document-description">Professional student identification cards</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('id-cards', 'https://drive.google.com/file/d/1iN7eg2Qy0yBnOhWce7BcAHZlEB50dJjp/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="identification">
                <div class="document-icon">
                    <i class="fas fa-id-badge"></i>
                </div>
                <h3 class="document-title">Employee ID Cards</h3>
                <p class="document-description">Staff identification and access cards</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('employee-id-cards', 'https://drive.google.com/file/d/1Vg6Nx-a0z3pRN9nsufod4rOP7NqH1yx3/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="academic">
                <div class="document-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="document-title">Batch Report Cards</h3>
                <p class="document-description">Comprehensive batch student report cards</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('batch-report-cards', 'https://drive.google.com/file/d/1hfy5kKJJWI_GDPxbIljknfsZbYFErAek/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="academic">
                <div class="document-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="document-title">Theology Report Cards</h3>
                <p class="document-description">Specialized report cards with theology subjects</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('batch-report-theology', 'https://drive.google.com/file/d/1UbE_dHdC10P9RM6N3B_I602JSD6RNfU6/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="academic">
                <div class="document-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="document-title">Single Report Card</h3>
                <p class="document-description">Individual student academic report card</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('single-report-card', 'https://drive.google.com/file/d/1jMNS8Tq8XZQYiebiwPInclyuZ0K0mJWi/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
            </div>

            <div class="document-card" data-category="academic">
                <div class="document-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h3 class="document-title">Exam Assessment</h3>
                <p class="document-description">Detailed examination assessment reports</p>
                <button class="btn btn-outline btn-sm" onclick="previewDocument('exam-assessment', 'https://drive.google.com/file/d/1km_w1z5oz66aOZGQCNmdtDqYnnWkKLkL/view?usp=drive_link')">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
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

<!-- Document Preview Modal -->
<div id="documentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Document Preview</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="document-preview">
                <iframe id="documentFrame" src="" frameborder="0"></iframe>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            <a id="viewFullDocument" href="" target="_blank" class="btn btn-primary">
                <i class="fas fa-external-link-alt"></i>
                Open Full Document
            </a>
        </div>
    </div>
</div>

<!-- Pricing Section -->
<section class="section pricing-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-subtitle">Pay only for active students with no hidden fees. Start with a 30-day free trial.</p>
        </div>
        
        <div class="pricing-grid">
            <div class="pricing-card" data-tier="starter">
                <div class="pricing-header">
                    <div class="pricing-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <h3 class="pricing-title">Starter</h3>
                    <p class="pricing-description">Perfect for small schools getting started</p>
                </div>
                <div class="pricing-details">
                    <div class="pricing-range">1 - 500 Students</div>
                    <div class="pricing-amount">
                        <span class="currency">UGX</span>
                        <span class="price">3,000</span>
                        <span class="period">per active student</span>
                    </div>
                    <div class="pricing-calculation">
                        <small>Example: 100 students = UGX 300,000/month</small>
                    </div>
                </div>
                <div class="pricing-features">
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Complete student management</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Financial tracking & reports</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Document generation</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Basic support</span>
                    </div>
                </div>
            </div>
            
            <div class="pricing-card featured" data-tier="professional">
                <div class="popular-badge">Most Popular</div>
                <div class="pricing-header">
                    <div class="pricing-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="pricing-title">Professional</h3>
                    <p class="pricing-description">Ideal for growing institutions</p>
                </div>
                <div class="pricing-details">
                    <div class="pricing-range">501 - 1,000 Students</div>
                    <div class="pricing-amount">
                        <span class="currency">UGX</span>
                        <span class="price">2,500</span>
                        <span class="period">per active student</span>
                    </div>
                    <div class="pricing-calculation">
                        <small>Example: 750 students = UGX 1,875,000/month</small>
                    </div>
                </div>
                <div class="pricing-features">
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Everything in Starter</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Advanced analytics</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Custom integrations</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Priority support</span>
                    </div>
                </div>
            </div>
            
            <div class="pricing-card" data-tier="enterprise">
                <div class="pricing-header">
                    <div class="pricing-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3 class="pricing-title">Enterprise</h3>
                    <p class="pricing-description">For large educational institutions</p>
                </div>
                <div class="pricing-details">
                    <div class="pricing-range">1,000+ Students</div>
                    <div class="pricing-amount">
                        <span class="currency">UGX</span>
                        <span class="price">1,500</span>
                        <span class="period">per active student</span>
                    </div>
                    <div class="pricing-calculation">
                        <small>Example: 2,000 students = UGX 3,000,000/month</small>
                    </div>
                </div>
                <div class="pricing-features">
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Everything in Professional</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Dedicated support team</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>Custom development</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check"></i>
                        <span>On-site training</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="pricing-footer">
            <div class="trial-info">
                <div class="trial-badge">
                    <i class="fas fa-gift"></i>
                    <span>30-Day Free Trial</span>
                </div>
                <p>Start with a completely free 30-day trial. No credit card required. Cancel anytime.</p>
            </div>
            <div class="pricing-cta">
                <a href="{{ url('access-system') }}" class="btn btn-primary btn-large">
                    <i class="fas fa-rocket"></i>
                    Start Your Free Trial
                </a>
                <p class="pricing-note">Setup takes less than 5 minutes</p>
            </div>
        </div>
    </div>
</section>

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

// Sample Documents Modal System
function previewDocument(docType, fullUrl) {
    const modal = document.getElementById('documentModal');
    const iframe = document.getElementById('documentFrame');
    const viewFullBtn = document.getElementById('viewFullDocument');
    const modalTitle = document.getElementById('modalTitle');
    
    // Check if elements exist
    if (!modal || !iframe || !viewFullBtn) {
        console.error('Modal elements not found');
        return;
    }
    
    // Set modal title based on document type
    const titles = {
        'admission-form': 'Student Admission Form',
        'admission-letter': 'Admission Letter',
        'fee-structure': 'Fee Structure',
        'receipt': 'Payment Receipt',
        'financial-report': 'Financial Report',
        'demand-notice-list': 'Demand Notice List',
        'demand-notice': 'Individual Demand Notice',
        'student-id-cards': 'Student ID Cards',
        'employee-id-cards': 'Employee ID Cards',
        'batch-report-cards': 'Batch Report Cards',
        'report-card': 'Individual Report Card',
        'exam-assessment': 'Exam Assessment'
    };
    
    modalTitle.textContent = titles[docType] || 'Document Preview';
    
    // Convert Google Drive view URL to embed URL for iframe
    let embedUrl = fullUrl;
    if (fullUrl.includes('drive.google.com')) {
        // Extract file ID from Google Drive URL
        const fileIdMatch = fullUrl.match(/\/d\/([a-zA-Z0-9-_]+)/);
        if (fileIdMatch) {
            const fileId = fileIdMatch[1];
            embedUrl = `https://drive.google.com/file/d/${fileId}/preview`;
        }
    }
    
    // Show modal immediately
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Add loading state
    const modalBody = modal.querySelector('.modal-body');
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'modal-loading';
    loadingDiv.innerHTML = `
        <div class="spinner"></div>
        <p>Loading document preview...</p>
    `;
    modalBody.appendChild(loadingDiv);
    
    // Set up modal content
    iframe.src = embedUrl;
    viewFullBtn.href = fullUrl;
    
    // Handle iframe load
    iframe.onload = function() {
        // Remove loading state
        const loading = modalBody.querySelector('.modal-loading');
        if (loading) {
            loading.remove();
        }
    };
    
    // Handle load errors
    iframe.onerror = function() {
        const loading = modalBody.querySelector('.modal-loading');
        if (loading) {
            loading.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ffc107; margin-bottom: 20px;"></i>
                    <h3 style="color: var(--text-dark); margin-bottom: 10px;">Preview Unavailable</h3>
                    <p style="color: var(--text-light); margin-bottom: 20px;">Unable to load document preview. Please use the button below to view the full document.</p>
                    <a href="${fullUrl}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i>
                        Open Full Document
                    </a>
                </div>
            `;
        }
        console.warn('Failed to load document preview');
    };
    
    // Remove loading state after timeout (fallback)
    setTimeout(() => {
        const loading = modalBody.querySelector('.modal-loading');
        if (loading) {
            loading.remove();
        }
    }, 10000);
}

function closeModal() {
    const modal = document.getElementById('documentModal');
    const iframe = document.getElementById('documentFrame');
    const modalBody = modal?.querySelector('.modal-body');
    
    if (modal && iframe) {
        // Add closing animation
        modal.style.animation = 'fadeOut 0.3s ease';
        modal.querySelector('.modal-content').style.animation = 'slideOut 0.3s ease';
        
        setTimeout(() => {
            modal.style.display = 'none';
            modal.style.animation = '';
            modal.querySelector('.modal-content').style.animation = '';
            document.body.style.overflow = 'auto';
            iframe.src = '';
            
            // Remove any loading states
            const loading = modalBody?.querySelector('.modal-loading');
            if (loading) {
                loading.remove();
            }
        }, 300);
    }
}

// Initialize modal functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('documentModal');
    
    if (modal) {
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'block') {
            closeModal();
        }
    });
    
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
