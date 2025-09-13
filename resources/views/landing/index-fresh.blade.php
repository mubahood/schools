<?php
use App\Models\Utils;
?>
@extends('layouts.modern-public')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="modern-container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>
                    Transform Your School with 
                    <span class="highlight">{{ Utils::app_name() }}</span>
                </h1>
                <p>
                    A comprehensive school management system designed to streamline administrative tasks, 
                    enhance communication, and improve efficiency in educational institutions.
                </p>
                <div class="hero-buttons">
                    <a href="{{ url('access-system') }}" class="btn btn-primary">
                        <i class='bx bx-rocket'></i>
                        Get Started Free
                    </a>
                    <a href="#features" class="btn btn-outline">
                        <i class='bx bx-play-circle'></i>
                        Learn More
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="{{ Utils::get_logo() }}" alt="{{ Utils::app_name() }}" style="max-width: 300px;">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="section section-light">
    <div class="modern-container">
        <div class="section-title">
            <h2>Powerful Features for Modern Schools</h2>
            <p>Everything you need to manage your school efficiently in one comprehensive platform.</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-user-check'></i>
                </div>
                <h3 class="card-title">Student Management</h3>
                <p class="card-text">Comprehensive student records, enrollment, and academic tracking system with detailed profiles and progress monitoring.</p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-chalkboard'></i>
                </div>
                <h3 class="card-title">Class Management</h3>
                <p class="card-text">Organize classes, subjects, and academic schedules with ease. Create timetables and manage classroom resources efficiently.</p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-line-chart'></i>
                </div>
                <h3 class="card-title">Performance Analytics</h3>
                <p class="card-text">Track academic performance and generate detailed reports with powerful analytics and data visualization tools.</p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-money'></i>
                </div>
                <h3 class="card-title">Financial Management</h3>
                <p class="card-text">Handle fees, payments, and financial records efficiently with automated billing and comprehensive accounting features.</p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-message-dots'></i>
                </div>
                <h3 class="card-title">Communication Hub</h3>
                <p class="card-text">Stay connected with parents, teachers, and students through integrated messaging and notification systems.</p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-shield-check'></i>
                </div>
                <h3 class="card-title">Secure & Reliable</h3>
                <p class="card-text">Enterprise-grade security with reliable data protection, backup systems, and compliance with educational standards.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section">
    <div class="modern-container">
        <div class="section-title">
            <h2>Trusted by Educational Institutions Worldwide</h2>
            <p>Join thousands of schools that have transformed their operations with {{ Utils::app_name() }}.</p>
        </div>
        
        <div class="grid grid-4 text-center">
            <div class="card">
                <h3 style="font-size: 3rem; color: var(--primary-color); margin-bottom: 0.5rem;">500+</h3>
                <h4 class="card-title">Active Schools</h4>
                <p class="card-text">Educational institutions using our platform</p>
            </div>
            
            <div class="card">
                <h3 style="font-size: 3rem; color: var(--accent-color); margin-bottom: 0.5rem;">50K+</h3>
                <h4 class="card-title">Students Managed</h4>
                <p class="card-text">Student records actively maintained</p>
            </div>
            
            <div class="card">
                <h3 style="font-size: 3rem; color: var(--primary-color); margin-bottom: 0.5rem;">5K+</h3>
                <h4 class="card-title">Teachers Connected</h4>
                <p class="card-text">Educators using our teaching tools</p>
            </div>
            
            <div class="card">
                <h3 style="font-size: 3rem; color: var(--accent-color); margin-bottom: 0.5rem;">99.9%</h3>
                <h4 class="card-title">Uptime Guarantee</h4>
                <p class="card-text">Reliable service you can count on</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section section-light">
    <div class="modern-container">
        <div class="section-title">
            <h2>What Schools Say About Us</h2>
            <p>Hear from educational leaders who have transformed their institutions with {{ Utils::app_name() }}.</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card">
                <div class="flex gap-3 mb-4">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                        JS
                    </div>
                    <div>
                        <h5 style="margin: 0; color: var(--text-dark);">John Smith</h5>
                        <small style="color: var(--text-light);">Principal, ABC International School</small>
                    </div>
                </div>
                <p class="card-text">
                    "{{ Utils::app_name() }} has completely transformed how we manage our school operations. 
                    The interface is intuitive and the features are comprehensive. Our administrative efficiency has improved by 300%."
                </p>
            </div>
            
            <div class="card">
                <div class="flex gap-3 mb-4">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                        MJ
                    </div>
                    <div>
                        <h5 style="margin: 0; color: var(--text-dark);">Mary Johnson</h5>
                        <small style="color: var(--text-light);">Administrator, Green Valley Academy</small>
                    </div>
                </div>
                <p class="card-text">
                    "The student management features are outstanding. We can track everything from enrollment to graduation seamlessly. 
                    The parents love the communication features too."
                </p>
            </div>
            
            <div class="card">
                <div class="flex gap-3 mb-4">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                        RB
                    </div>
                    <div>
                        <h5 style="margin: 0; color: var(--text-dark);">Robert Brown</h5>
                        <small style="color: var(--text-light);">Director, Excellence Institute</small>
                    </div>
                </div>
                <p class="card-text">
                    "Excellent support team and regular updates. The financial management module has saved us countless hours 
                    and significantly improved our accounting accuracy."
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%); color: white; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.05\"%3E%3Ccircle cx=\"30\" cy=\"30\" r=\"4\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E') repeat;"></div>
    <div class="modern-container" style="position: relative; z-index: 2;">
        <div class="text-center">
            <h2 style="color: white; margin-bottom: 1rem;">
                Ready to Transform Your School?
            </h2>
            <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 0.9);">
                Join thousands of schools already using {{ Utils::app_name() }} to streamline their operations 
                and enhance educational outcomes.
            </p>
            <div class="flex-center gap-3">
                <a href="{{ url('access-system') }}" class="btn" style="background: white; color: var(--primary-color); padding: 1rem 2.5rem; font-size: 1.1rem;">
                    <i class='bx bx-rocket'></i>
                    Start Your Free Trial
                </a>
                <a href="#" class="btn btn-outline" style="border-color: white; color: white;">
                    <i class='bx bx-phone'></i>
                    Schedule Demo
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
