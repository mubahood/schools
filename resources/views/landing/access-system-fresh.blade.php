<?php
use App\Models\Utils;
?>
@extends('layouts.modern-public')

@section('content')
<!-- Hero Section -->
<section class="hero-section" style="min-height: 70vh;">
    <div class="modern-container">
        <div class="hero-content" style="grid-template-columns: 1fr; text-align: center;">
            <div class="hero-text">
                <h1>
                    Access Your 
                    <span class="highlight">School System</span>
                </h1>
                <p>
                    Choose your access method to get started with {{ Utils::app_name() }} - 
                    the most comprehensive school management platform.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Access Options Section -->
<section class="section section-light">
    <div class="modern-container">
        <div class="section-title">
            <h2>How would you like to access the system?</h2>
            <p>Select the option that best describes your role and get instant access to your dashboard.</p>
        </div>
        
        <div class="grid grid-2" style="max-width: 800px; margin: 0 auto;">
            <a href="#" class="access-card">
                <div class="access-icon primary">
                    <i class='bx bx-building'></i>
                </div>
                <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Register Your School</h3>
                <p style="color: var(--text-light); line-height: 1.6; margin-bottom: 2rem;">
                    New to {{ Utils::app_name() }}? Register your educational institution and get started 
                    with our comprehensive school management system. Setup takes less than 10 minutes.
                </p>
                <div class="btn btn-primary" style="pointer-events: none;">
                    <i class='bx bx-plus-circle'></i>
                    Register New School
                </div>
            </a>
            
            <a href="#" class="access-card">
                <div class="access-icon accent">
                    <i class='bx bx-log-in'></i>
                </div>
                <h3 style="color: var(--text-dark); margin-bottom: 1rem;">School Login</h3>
                <p style="color: var(--text-light); line-height: 1.6; margin-bottom: 2rem;">
                    Already registered? Access your school's dashboard to manage students, teachers, 
                    and all administrative functions. Welcome back!
                </p>
                <div class="btn btn-accent" style="pointer-events: none;">
                    <i class='bx bx-lock-open'></i>
                    Login to Dashboard
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="modern-container">
        <div class="section-title">
            <h2>Why Choose {{ Utils::app_name() }}?</h2>
            <p>Discover the features that make us the preferred choice for educational institutions worldwide.</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-zap'></i>
                </div>
                <h4 class="card-title">Quick Setup</h4>
                <p class="card-text">Get your school up and running in minutes with our intuitive setup wizard and guided onboarding process.</p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-shield-check'></i>
                </div>
                <h4 class="card-title">Secure & Compliant</h4>
                <p class="card-text">Bank-level security with GDPR compliance, data encryption, and regular security audits to protect sensitive information.</p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-support'></i>
                </div>
                <h4 class="card-title">24/7 Support</h4>
                <p class="card-text">Round-the-clock support from our dedicated education specialists to help you succeed every step of the way.</p>
            </div>
        </div>
    </div>
</section>

<!-- Support Section -->
<section class="section section-light">
    <div class="modern-container">
        <div class="section-title">
            <h2>Need Help Getting Started?</h2>
            <p>Our support team is here to assist you every step of the way. Choose the support option that works best for you.</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-phone'></i>
                </div>
                <h4 class="card-title">Phone Support</h4>
                <p class="card-text">Call us for immediate assistance with setup, training, or any technical questions you may have.</p>
                <a href="tel:{{ \App\Models\Utils::get_support_phone() }}" class="btn btn-outline mt-3">
                    <i class='bx bx-phone'></i>
                    Call Now
                </a>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-envelope'></i>
                </div>
                <h4 class="card-title">Email Support</h4>
                <p class="card-text">Send us your questions anytime and we'll respond within 24 hours with detailed solutions.</p>
                <a href="mailto:{{ \App\Models\Utils::get_support_email() }}" class="btn btn-outline mt-3">
                    <i class='bx bx-envelope'></i>
                    Email Us
                </a>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-book-open'></i>
                </div>
                <h4 class="card-title">Documentation</h4>
                <p class="card-text">Comprehensive guides, tutorials, and video resources to help you master every feature.</p>
                <a href="#" class="btn btn-outline mt-3">
                    <i class='bx bx-book-open'></i>
                    Browse Docs
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Quick Start Section -->
<section class="section" style="background: var(--dark-color); color: white;">
    <div class="modern-container">
        <div class="text-center">
            <h2 style="color: white; margin-bottom: 1rem;">Ready to Get Started?</h2>
            <p style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 2rem; color: rgba(255, 255, 255, 0.8);">
                Join over 500+ schools worldwide that trust {{ Utils::app_name() }} for their educational management needs.
            </p>
            <div class="flex-center gap-3">
                <a href="#" class="btn btn-primary">
                    <i class='bx bx-plus-circle'></i>
                    Register Your School
                </a>
                <a href="#" class="btn btn-accent">
                    <i class='bx bx-log-in'></i>
                    School Login
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
