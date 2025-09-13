@extends('layouts.modern-layout')

@push('styles')
<style>
    .access-hero {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .access-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='m0 40l40-40h-40v40zm40 0v-40h-40l40 40z'/%3E%3C/g%3E%3C/svg%3E") repeat;
        animation: float 20s ease-in-out infinite;
    }
    
    .access-card {
        background: white;
        border-radius: var(--radius-xl);
        padding: var(--space-8);
        box-shadow: var(--shadow-2xl);
        border: 1px solid var(--gray-200);
        transition: all var(--transition-base);
        text-decoration: none;
        color: inherit;
        display: block;
        position: relative;
        overflow: hidden;
    }
    
    .access-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(var(--primary-rgb), 0.1), transparent);
        transition: left 0.5s ease;
    }
    
    .access-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-2xl);
        border-color: var(--primary-color);
        text-decoration: none;
        color: inherit;
    }
    
    .access-card:hover::before {
        left: 100%;
    }
    
    .access-icon {
        width: 80px;
        height: 80px;
        background: var(--primary-gradient);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        margin: 0 auto var(--space-4);
        transition: all var(--transition-base);
    }
    
    .access-card:hover .access-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .feature-highlight {
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        margin-bottom: var(--space-6);
    }
    
    .feature-highlight h6 {
        color: var(--primary-color);
        margin-bottom: var(--space-3);
    }
    
    .feature-list {
        list-style: none;
        padding: 0;
    }
    
    .feature-list li {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-3);
        color: var(--gray-700);
    }
    
    .feature-list i {
        color: var(--accent-color);
        font-size: 1.2rem;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="access-hero">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center min-vh-100">
            <!-- Left Side - Info -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="mb-5">
                    <h1 class="display-4 mb-4">
                        Access Your School Management System
                    </h1>
                    <p class="lead mb-5">
                        Streamline your educational operations with our comprehensive platform 
                        designed for modern schools and institutions.
                    </p>
                    
                    <!-- Features Highlight -->
                    <div class="feature-highlight">
                        <h6>What You Get:</h6>
                        <ul class="feature-list">
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>Complete student information management</span>
                            </li>
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>Automated attendance tracking</span>
                            </li>
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>Financial management & reporting</span>
                            </li>
                            <li>
                                <i class='bx bx-check-circle'></i>
                                <span>Real-time parent communication</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Access Options -->
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="text-center mb-4">
                            <h3 class="h4 mb-2">Choose Your Path</h3>
                            <p class="mb-0 opacity-75">Select how you'd like to get started</p>
                        </div>
                    </div>
                    
                    <!-- New School Option -->
                    <div class="col-md-6">
                        <a href="{{ admin_url('auth/register') }}" class="access-card h-100 text-center">
                            <div class="access-icon">
                                <i class='bx bx-plus-circle'></i>
                            </div>
                            <h4 class="h5 mb-3">New School</h4>
                            <p class="text-muted mb-4">
                                Register your school and start using our comprehensive management system
                            </p>
                            <div class="d-flex align-items-center justify-content-center gap-2 text-primary">
                                <span class="fw-medium">Get Started</span>
                                <i class='bx bx-right-arrow-alt'></i>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Existing School Option -->
                    <div class="col-md-6">
                        <a href="{{ admin_url('auth/login') }}" class="access-card h-100 text-center">
                            <div class="access-icon" style="background: var(--accent-gradient);">
                                <i class='bx bx-log-in-circle'></i>
                            </div>
                            <h4 class="h5 mb-3">Existing School</h4>
                            <p class="text-muted mb-4">
                                Sign in to your school's account to access the dashboard
                            </p>
                            <div class="d-flex align-items-center justify-content-center gap-2 text-accent">
                                <span class="fw-medium">Sign In</span>
                                <i class='bx bx-right-arrow-alt'></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Support Section -->
<section class="section bg-gray">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h3 class="h4 mb-3">Need assistance getting started?</h3>
                <p class="text-muted mb-4 mb-lg-0">
                    Our team is here to help you every step of the way. Contact us for personalized support 
                    and guidance on choosing the right solution for your school.
                </p>
            </div>
            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="200">
                <div class="d-flex flex-column gap-3">
                    <a href="tel:{{ str_replace(' ', '', Utils::get_support_phone()) }}" class="btn btn-outline">
                        <i class='bx bx-phone'></i>
                        Call Support: {{ Utils::get_support_phone() }}
                    </a>
                    <a href="{{ Utils::get_whatsapp_link() }}" class="btn btn-accent" target="_blank">
                        <i class='bx bxl-whatsapp'></i>
                        WhatsApp Chat
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Overview -->
<section class="section">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 mb-4">Why Schools Choose {{ Utils::app_name() }}</h2>
            <p class="lead text-muted">
                Join hundreds of educational institutions that have transformed their operations
            </p>
        </div>
        
        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center">
                    <div class="feature-icon mx-auto mb-4">
                        <i class='bx bxs-rocket'></i>
                    </div>
                    <h5 class="mb-3">Quick Setup</h5>
                    <p class="text-muted">
                        Get your school up and running in minutes with our streamlined onboarding process
                    </p>
                </div>
            </div>
            
            <!-- Feature 2 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center">
                    <div class="feature-icon mx-auto mb-4">
                        <i class='bx bxs-shield-check'></i>
                    </div>
                    <h5 class="mb-3">Secure & Reliable</h5>
                    <p class="text-muted">
                        Your data is protected with enterprise-grade security and regular backups
                    </p>
                </div>
            </div>
            
            <!-- Feature 3 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="text-center">
                    <div class="feature-icon mx-auto mb-4">
                        <i class='bx bxs-support'></i>
                    </div>
                    <h5 class="mb-3">24/7 Support</h5>
                    <p class="text-muted">
                        Our dedicated support team is available round the clock to assist you
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Back to Home -->
<section class="section-sm bg-gray">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <a href="{{ url('/') }}" class="btn btn-ghost">
                <i class='bx bx-arrow-back'></i>
                Back to Homepage
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Enhanced hover effects for access cards
    document.querySelectorAll('.access-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
</script>
@endpush
