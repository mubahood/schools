@extends('layouts.onboarding-layout')

@section('content')
<div class="onboarding-card">
    <!-- Explainer Side -->
    <div class="explainer-side">
        <div class="explainer-content">
            <!-- Brand -->
            <div class="brand-logo">
                <div class="brand-icon">
                    <i class='bx bxs-graduation'></i>
                </div>
                <div class="brand-text">
                    <h1>{{ Utils::app_name() }}</h1>
                    <p>{{ Utils::company_name() }}</p>
                </div>
            </div>
            
            <!-- Main Content -->
            <h2 class="explainer-title">Access Your School Management System</h2>
            <p class="explainer-description">
                Streamline your educational operations with our comprehensive platform designed for modern schools and institutions.
            </p>
            
            <!-- Features -->
            <ul class="feature-list">
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class='bx bx-check'></i>
                    </div>
                    <span>Complete student information management</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class='bx bx-check'></i>
                    </div>
                    <span>Automated attendance tracking</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class='bx bx-check'></i>
                    </div>
                    <span>Financial management & reporting</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class='bx bx-check'></i>
                    </div>
                    <span>Real-time parent communication</span>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Action Side -->
    <div class="action-side">
        <!-- Header -->
        <div class="action-header">
            <h2 class="action-title">Get Started</h2>
            <p class="action-subtitle">Choose how you'd like to access the system</p>
        </div>
        
        <!-- Options -->
        <div class="action-options">
            <!-- New School Registration -->
            <a href="{{ admin_url('auth/register') }}" class="option-card">
                <div class="option-header">
                    <div class="option-icon">
                        <i class='bx bx-plus-circle'></i>
                    </div>
                    <div class="option-content">
                        <h3>New School</h3>
                        <p>Register your school and start using the system</p>
                    </div>
                </div>
            </a>
            
            <!-- Existing School Login -->
            <a href="{{ admin_url('auth/login') }}" class="option-card">
                <div class="option-header">
                    <div class="option-icon">
                        <i class='bx bx-log-in-circle'></i>
                    </div>
                    <div class="option-content">
                        <h3>Existing School</h3>
                        <p>Sign in to your school's account</p>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Support Section -->
        <div class="support-section">
            <p class="support-text">Need help getting started?</p>
            <div class="support-actions">
                <a href="tel:{{ str_replace(' ', '', Utils::get_support_phone()) }}" class="support-btn">
                    <i class='bx bx-phone'></i>
                    Call Support
                </a>
                <a href="{{ Utils::get_whatsapp_link() }}" class="support-btn primary" target="_blank">
                    <i class='bx bxl-whatsapp'></i>
                    WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
