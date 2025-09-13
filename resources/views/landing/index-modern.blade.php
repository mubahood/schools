@extends('layouts.modern-layout')

@push('styles')
<style>
    /* Hero Section Custom Styles */
    .hero-section {
        background: linear-gradient(135deg, var(--gray-900) 0%, var(--gray-800) 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='m0 40l40-40h-40v40zm40 0v-40h-40l40 40z'/%3E%3C/g%3E%3C/svg%3E") repeat;
        animation: float 20s ease-in-out infinite;
    }
    
    .hero-visual {
        max-width: 600px;
        margin: 0 auto;
        perspective: 1000px;
    }
    
    .dashboard-mockup {
        transform: rotateY(-15deg) rotateX(10deg);
        transition: transform 0.3s ease;
        box-shadow: 0 50px 100px rgba(0, 0, 0, 0.3);
        border-radius: var(--radius-xl);
        overflow: hidden;
    }
    
    .dashboard-mockup:hover {
        transform: rotateY(-10deg) rotateX(5deg) scale(1.02);
    }
    
    /* Custom testimonial carousel */
    .testimonial-carousel .swiper-slide {
        padding: var(--space-4);
    }
    
    /* School cards */
    .school-card {
        transition: all var(--transition-base);
        border: 1px solid var(--gray-200);
    }
    
    .school-card:hover {
        transform: translateY(-8px);
        border-color: var(--primary-color);
        box-shadow: var(--shadow-xl);
    }
    
    /* CTA Section */
    .cta-section {
        background: var(--primary-gradient);
        position: relative;
        overflow: hidden;
    }
    
    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section" id="home">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <!-- Hero Content -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="hero-content text-white">
                    <h1 class="hero-title">
                        Transform Your School with 
                        <span class="gradient-primary bg-clip-text text-transparent">{{ Utils::app_name() }}</span>
                    </h1>
                    <p class="hero-subtitle">
                        Streamline admissions, academics, financial management, and communication in one powerful platform. 
                        Join hundreds of schools that have revolutionized their operations.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 mt-5">
                        <a href="{{ url('/access-system') }}" class="btn btn-primary btn-lg">
                            <i class='bx bx-log-in-circle'></i>
                            Get Started Now
                        </a>
                        <a href="#features" class="btn btn-outline btn-lg">
                            <i class='bx bx-play-circle'></i>
                            See Features
                        </a>
                    </div>
                    <div class="mt-4 d-flex flex-wrap gap-4 text-sm">
                        <span><i class='bx bx-check text-accent'></i> Free Setup</span>
                        <span><i class='bx bx-check text-accent'></i> 24/7 Support</span>
                        <span><i class='bx bx-check text-accent'></i> Mobile Ready</span>
                    </div>
                </div>
            </div>
            
            <!-- Hero Visual -->
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="hero-visual">
                    <div class="dashboard-mockup">
                        <img src="{{ url('silicon/assets/img/landing/saas-2/hero/layer01.png') }}" 
                             alt="{{ Utils::app_name() }} Dashboard" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section" id="features">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-4">Why Choose {{ Utils::app_name() }}?</h2>
            <p class="lead text-muted max-w-2xl mx-auto">
                Transform your educational institution with cutting-edge technology designed specifically for modern schools.
            </p>
        </div>
        
        <!-- Features Grid -->
        <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon">
                    <i class='bx bxs-user-plus'></i>
                </div>
                <h3 class="feature-title">Effortless Admissions</h3>
                <p class="feature-description">
                    Digitize your entire admissions process with online applications, automated workflows, 
                    and seamless student enrollment management.
                </p>
            </div>
            
            <!-- Feature 2 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon">
                    <i class='bx bxs-graduation'></i>
                </div>
                <h3 class="feature-title">Academic Excellence</h3>
                <p class="feature-description">
                    Manage academic records, generate report cards, track performance, and maintain 
                    comprehensive student academic profiles.
                </p>
            </div>
            
            <!-- Feature 3 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon">
                    <i class='bx bx-money-withdraw'></i>
                </div>
                <h3 class="feature-title">Financial Management</h3>
                <p class="feature-description">
                    Automate fee collection, track payments, generate financial reports, and manage 
                    all school finances with precision and transparency.
                </p>
            </div>
            
            <!-- Feature 4 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon">
                    <i class='bx bxs-message-dots'></i>
                </div>
                <h3 class="feature-title">Smart Communication</h3>
                <p class="feature-description">
                    Keep parents, students, and staff connected with SMS notifications, mobile app, 
                    and real-time updates on school activities.
                </p>
            </div>
            
            <!-- Feature 5 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-icon">
                    <i class='bx bxs-report'></i>
                </div>
                <h3 class="feature-title">Powerful Reports</h3>
                <p class="feature-description">
                    Generate comprehensive reports on academics, finances, attendance, and performance 
                    with intelligent analytics and insights.
                </p>
            </div>
            
            <!-- Feature 6 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-icon">
                    <i class='bx bxs-cog'></i>
                </div>
                <h3 class="feature-title">Complete Ecosystem</h3>
                <p class="feature-description">
                    Beyond basics - manage hostels, transport, library, inventory, visitors, sickbay, 
                    and all aspects of school operations.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Modules Section -->
<section class="section bg-gray" id="modules">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-4">Everything You Need, Integrated</h2>
            <p class="lead text-muted">
                {{ Utils::app_name() }} offers dedicated modules to manage every facet of your school efficiently.
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class='bx bxs-user-plus display-4 text-primary mb-3'></i>
                        <h6 class="mb-0">Admissions Management</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="150">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class='bx bxs-graduation display-4 text-primary mb-3'></i>
                        <h6 class="mb-0">Student Records</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class='bx bx-money-withdraw display-4 text-primary mb-3'></i>
                        <h6 class="mb-0">Fees & Billing</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="250">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class='bx bxs-report display-4 text-primary mb-3'></i>
                        <h6 class="mb-0">Academic Reports</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class='bx bxs-message-dots display-4 text-primary mb-3'></i>
                        <h6 class="mb-0">Communication</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="350">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class='bx bxs-bus display-4 text-primary mb-3'></i>
                        <h6 class="mb-0">Transport</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="400">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class='bx bxs-bank display-4 text-primary mb-3'></i>
                        <h6 class="mb-0">Finance</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="450">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class='bx bxs-book display-4 text-primary mb-3'></i>
                        <h6 class="mb-0">Library</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section" id="testimonials">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-4">What Schools Say About Us</h2>
            <p class="lead text-muted">
                Hear from real schools that have transformed their operations with {{ Utils::app_name() }}.
            </p>
        </div>
        
        <div class="testimonial-carousel swiper" data-aos="fade-up" data-aos-delay="200">
            <div class="swiper-wrapper">
                <!-- Testimonial 1 -->
                <div class="swiper-slide">
                    <div class="testimonial">
                        <div class="testimonial-quote">
                            "{{ Utils::app_name() }} has completely transformed our school operations. From admissions to finance, 
                            everything is automated, saving us time and reducing errors!"
                        </div>
                        <div class="testimonial-author">
                            <img src="{{ url('silicon/assets/img/avatar/1.jpg') }}" alt="John Doe" class="testimonial-avatar">
                            <div>
                                <div class="testimonial-name">Sarah Johnson</div>
                                <div class="testimonial-role">Principal, Greenwood High School</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="swiper-slide">
                    <div class="testimonial">
                        <div class="testimonial-quote">
                            "The parent communication features are amazing. Parents can easily track their child's progress, 
                            fee status, and receive real-time updates. Highly recommended!"
                        </div>
                        <div class="testimonial-author">
                            <img src="{{ url('silicon/assets/img/avatar/2.jpg') }}" alt="Jane Smith" class="testimonial-avatar">
                            <div>
                                <div class="testimonial-name">Michael Brown</div>
                                <div class="testimonial-role">Administrator, St. Mary's College</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="swiper-slide">
                    <div class="testimonial">
                        <div class="testimonial-quote">
                            "The financial management module has revolutionized how we handle school fees. 
                            Everything is transparent, automated, and easy to track."
                        </div>
                        <div class="testimonial-author">
                            <img src="{{ url('silicon/assets/img/avatar/3.jpg') }}" alt="David Wilson" class="testimonial-avatar">
                            <div>
                                <div class="testimonial-name">Emily Davis</div>
                                <div class="testimonial-role">Finance Manager, Victory Academy</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<!-- Schools Section -->
<section class="section bg-gray" id="schools">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 mb-4">Trusted by Leading Schools</h2>
            <p class="lead text-muted">
                Join hundreds of educational institutions that trust {{ Utils::app_name() }} to simplify management and enhance learning.
            </p>
        </div>
        
        <div class="row g-4">
            <!-- School 1 -->
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="flip-left" data-aos-delay="100">
                <div class="card school-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ url('silicon/assets/img/avatar/1.jpg') }}" alt="School" class="rounded-circle mb-3" width="56">
                        <h6 class="mb-0">Bright Future Secondary School</h6>
                        <small class="text-muted">Kaliro District</small>
                    </div>
                </div>
            </div>
            
            <!-- School 2 -->
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="flip-left" data-aos-delay="150">
                <div class="card school-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ url('silicon/assets/img/avatar/2.jpg') }}" alt="School" class="rounded-circle mb-3" width="56">
                        <h6 class="mb-0">St. Joseph Primary School</h6>
                        <small class="text-muted">Kampala</small>
                    </div>
                </div>
            </div>
            
            <!-- School 3 -->
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="flip-left" data-aos-delay="200">
                <div class="card school-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ url('silicon/assets/img/avatar/3.jpg') }}" alt="School" class="rounded-circle mb-3" width="56">
                        <h6 class="mb-0">Victory International College</h6>
                        <small class="text-muted">Entebbe</small>
                    </div>
                </div>
            </div>
            
            <!-- School 4 -->
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="flip-left" data-aos-delay="250">
                <div class="card school-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ url('silicon/assets/img/avatar/4.jpg') }}" alt="School" class="rounded-circle mb-3" width="56">
                        <h6 class="mb-0">Greenwood Academy</h6>
                        <small class="text-muted">Jinja</small>
                    </div>
                </div>
            </div>
            
            <!-- School 5 -->
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="flip-left" data-aos-delay="300">
                <div class="card school-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ url('silicon/assets/img/avatar/5.jpg') }}" alt="School" class="rounded-circle mb-3" width="56">
                        <h6 class="mb-0">Al-Bushra Islamic School</h6>
                        <small class="text-muted">Bwaise</small>
                    </div>
                </div>
            </div>
            
            <!-- School 6 -->
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="flip-left" data-aos-delay="350">
                <div class="card school-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ url('silicon/assets/img/avatar/6.jpg') }}" alt="School" class="rounded-circle mb-3" width="56">
                        <h6 class="mb-0">Royal Hills College</h6>
                        <small class="text-muted">Mukono</small>
                    </div>
                </div>
            </div>
            
            <!-- School 7 -->
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="flip-left" data-aos-delay="400">
                <div class="card school-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ url('silicon/assets/img/avatar/7.jpg') }}" alt="School" class="rounded-circle mb-3" width="56">
                        <h6 class="mb-0">Unity Secondary School</h6>
                        <small class="text-muted">Masaka</small>
                    </div>
                </div>
            </div>
            
            <!-- School 8 -->
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="flip-left" data-aos-delay="450">
                <div class="card school-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ url('silicon/assets/img/avatar/8.jpg') }}" alt="School" class="rounded-circle mb-3" width="56">
                        <h6 class="mb-0">Hope Foundation School</h6>
                        <small class="text-muted">Mbarara</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="section" id="contact">
    <div class="container">
        <div class="row align-items-center">
            <!-- Contact Info -->
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="display-4 mb-4">Get in Touch</h2>
                <p class="lead text-muted mb-5">
                    Ready to transform your school? Contact us for a personalized demonstration 
                    and see how {{ Utils::app_name() }} can benefit your institution.
                </p>
                
                <div class="d-flex flex-column gap-4">
                    <!-- Phone -->
                    <div class="d-flex align-items-center gap-3">
                        <div class="feature-icon" style="width: 48px; height: 48px; font-size: 24px;">
                            <i class='bx bx-phone'></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Phone Support</h6>
                            <a href="tel:{{ str_replace(' ', '', Utils::get_support_phone()) }}" class="text-muted">
                                {{ Utils::get_support_phone() }}
                            </a>
                        </div>
                    </div>
                    
                    <!-- WhatsApp -->
                    <div class="d-flex align-items-center gap-3">
                        <div class="feature-icon" style="width: 48px; height: 48px; font-size: 24px; background: var(--accent-gradient);">
                            <i class='bx bxl-whatsapp'></i>
                        </div>
                        <div>
                            <h6 class="mb-1">WhatsApp</h6>
                            <a href="{{ Utils::get_whatsapp_link() }}" class="text-muted" target="_blank">
                                Chat with us instantly
                            </a>
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="d-flex align-items-center gap-3">
                        <div class="feature-icon" style="width: 48px; height: 48px; font-size: 24px;">
                            <i class='bx bx-map'></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Location</h6>
                            <p class="text-muted mb-0">{{ Utils::get_company_address() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Visual -->
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="text-center">
                    <img src="{{ url('silicon/assets/img/landing/saas-2/hero/layer02.png') }}" 
                         alt="Contact Us" class="img-fluid rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section section text-white">
    <div class="container text-center position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <h2 class="display-4 mb-4">Ready to Transform Your School?</h2>
                <p class="lead mb-5">
                    Join hundreds of schools already using {{ Utils::app_name() }} to streamline their operations 
                    and enhance educational excellence.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="{{ url('/access-system') }}" class="btn btn-white btn-lg">
                        <i class='bx bx-log-in-circle text-primary'></i>
                        Access The System
                    </a>
                    <a href="{{ Utils::get_whatsapp_link() }}" class="btn btn-outline btn-lg" target="_blank" style="border-color: white; color: white;">
                        <i class='bx bxl-whatsapp'></i>
                        Contact Support
                    </a>
                </div>
                <div class="mt-4 d-flex flex-wrap justify-content-center gap-4">
                    <span><i class='bx bx-shield-check'></i> Secure & Reliable</span>
                    <span><i class='bx bx-mobile'></i> Mobile Ready</span>
                    <span><i class='bx bx-support'></i> 24/7 Support</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Initialize Swiper for testimonials
    const swiper = new Swiper('.testimonial-carousel', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 1,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 30,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 40,
            },
        },
    });
    
    // Smooth hero animations
    const heroTitle = document.querySelector('.hero-title');
    const heroSubtitle = document.querySelector('.hero-subtitle');
    
    if (heroTitle && heroSubtitle) {
        setTimeout(() => {
            heroTitle.classList.add('animate-fade-in-up');
        }, 300);
        
        setTimeout(() => {
            heroSubtitle.classList.add('animate-fade-in-up');
        }, 600);
    }
</script>
@endpush
