<?php
//use Utils model
use App\Models\Utils;
?>
@extends('layouts.base-layout')

{{-- Custom CSS for Access System Page --}}
<style>
    /* Enhanced animations and styling */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .access-card {
        animation: slideInUp 0.8s ease-out;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .access-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }

    .option-card {
        animation: fadeInScale 0.6s ease-out;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .option-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s;
    }

    .option-card:hover::before {
        left: 100%;
    }

    .option-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.8rem 2rem rgba(0, 0, 0, 0.15);
    }

    .option-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 1.5rem;
        transition: all 0.3s ease;
    }

    .option-card:hover .option-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .hero-gradient {
        background: linear-gradient(135deg, var(--si-primary) 0%, var(--si-info) 100%);
    }

    .bg-pattern {
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0);
        background-size: 20px 20px;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin: 0 auto 1rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .option-icon {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }
    }
</style>

@section('content')
    <!-- Hero Section -->
    <section class="hero-gradient bg-pattern position-relative overflow-hidden">
        <div class="container position-relative zindex-5 py-5">
            <div class="row justify-content-center text-center py-5">
                <div class="col-xl-8 col-lg-9 col-md-10">
                    <div class="access-card rounded-4 p-4 p-md-5 mx-auto" style="max-width: 600px;">
                        <div class="mb-4">
                            <img src="{{ Utils::get_logo() }}" alt="{{ Utils::app_name() }}" class="mb-3" style="height: 60px;">
                            <h1 class="h2 mb-3">Welcome to <span class="text-primary">{{ Utils::app_name() }}</span></h1>
                            <p class="lead text-muted mb-4">
                                Choose how you'd like to access our comprehensive school management platform
                            </p>
                        </div>

                        <!-- Access Options -->
                        <div class="row g-4 mb-4">
                            <!-- New School Registration -->
                            <div class="col-md-6">
                                <a href="{{ Utils::get_demo_form_link() }}" class="text-decoration-none">
                                    <div class="option-card card h-100 border-0 shadow-sm p-4 text-center">
                                        <div class="option-icon bg-primary text-white">
                                            <i class="bx bx-building-house"></i>
                                        </div>
                                        <h4 class="h5 mb-3">Register New School</h4>
                                        <p class="text-muted mb-0">
                                            Start your journey with {{ Utils::app_name() }}. Register your institution 
                                            and get a customized demo.
                                        </p>
                                        <div class="mt-3">
                                            <span class="btn btn-primary btn-sm">Get Started</span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Existing School Login -->
                            <div class="col-md-6">
                                <a href="{{ admin_url('/') }}" class="text-decoration-none">
                                    <div class="option-card card h-100 border-0 shadow-sm p-4 text-center">
                                        <div class="option-icon bg-success text-white">
                                            <i class="bx bx-log-in-circle"></i>
                                        </div>
                                        <h4 class="h5 mb-3">Login to Existing School</h4>
                                        <p class="text-muted mb-0">
                                            Already have an account? Access your school's 
                                            {{ Utils::app_name() }} dashboard here.
                                        </p>
                                        <div class="mt-3">
                                            <span class="btn btn-success btn-sm">Login Now</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="text-center">
                            <p class="small text-muted mb-3">
                                Need help deciding? <a href="#how-it-works" class="text-decoration-none">Learn how it works</a>
                            </p>
                            <div class="d-flex justify-content-center gap-4 text-muted small">
                                <span><i class="bx bx-shield-check me-1"></i>Secure & Reliable</span>
                                <span><i class="bx bx-mobile me-1"></i>Mobile Ready</span>
                                <span><i class="bx bx-support me-1"></i>24/7 Support</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Background decoration -->
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(255,255,255,.05);"></div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5" id="how-it-works">
        <div class="container py-md-4">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-xl-6 col-lg-7">
                    <h2 class="h1 mb-4">How {{ Utils::app_name() }} Works</h2>
                    <p class="lead text-muted">
                        Getting started with {{ Utils::app_name() }} is simple and straightforward
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Step 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="text-center">
                        <div class="step-number bg-primary text-white">1</div>
                        <h4 class="h5 mb-3">Choose Your Path</h4>
                        <p class="text-muted">
                            Select whether you're registering a new school or logging into an existing account.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="text-center">
                        <div class="step-number bg-success text-white">2</div>
                        <h4 class="h5 mb-3">Get Set Up</h4>
                        <p class="text-muted">
                            For new schools, we'll schedule a demo and help with setup. Existing users can login immediately.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="text-center">
                        <div class="step-number bg-info text-white">3</div>
                        <h4 class="h5 mb-3">Start Managing</h4>
                        <p class="text-muted">
                            Begin managing students, staff, finances, and academics with our comprehensive platform.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Section -->
    <section class="bg-secondary py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="h4 mb-2">Need assistance getting started?</h3>
                    <p class="mb-lg-0 text-muted">
                        Our team is here to help you every step of the way. Contact us for personalized support.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-lg-end">
                        <a href="tel:{{ str_replace(' ', '', Utils::get_support_phone()) }}" class="btn btn-outline-primary">
                            <i class="bx bx-phone me-2"></i>Call Support
                        </a>
                        <a href="{{ Utils::get_whatsapp_link() }}" 
                           class="btn btn-primary" target="_blank">
                            <i class="bx bxl-whatsapp me-2"></i>WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Back to Home -->
    <section class="py-4 border-top">
        <div class="container">
            <div class="text-center">
                <a href="{{ url('/') }}" class="btn btn-link text-muted">
                    <i class="bx bx-arrow-back me-2"></i>Back to Homepage
                </a>
            </div>
        </div>
    </section>
@endsection
