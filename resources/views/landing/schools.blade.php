<?php
use App\Models\Utils;
?>
@extends('layouts.modern-public')

@section('title', 'Schools Using Our System - Our Partners')
@section('meta_description', 'See the growing list of schools across Uganda using our school management system to streamline their operations and improve education management.')

@section('content')
<style>
    .school-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .school-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(0, 180, 250, 0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.6s ease;
        opacity: 0;
    }
    
    .school-card:hover::before {
        opacity: 1;
        animation: shimmer 2s ease-in-out infinite;
    }
    
    .school-card:hover {
        transform: translateY(-10px);
        border-color: var(--primary-color);
        box-shadow: 0 20px 40px rgba(0, 180, 250, 0.2);
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    .school-logo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 1.5rem auto;
        border: 4px solid var(--primary-color);
        position: relative;
        z-index: 2;
    }
    
    .school-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 2;
    }
    
    .school-location {
        color: var(--text-light);
        font-size: 0.9rem;
        position: relative;
        z-index: 2;
    }
    
    .schools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }
    
    .featured-schools {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        border-radius: 25px;
        padding: 4rem 2rem;
        text-align: center;
        color: white;
        margin: 3rem 0;
        position: relative;
    }
    
    .featured-schools::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 25px;
        z-index: 1;
    }
    
    .featured-schools > * {
        position: relative;
        z-index: 2;
    }
    
    .featured-schools h3 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        font-weight: 700;
    }
    
    .featured-schools p {
        font-size: 1.2rem;
        opacity: 1;
        max-width: 600px;
        margin: 0 auto;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        font-weight: 500;
    }
    
    .stats-banner {
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        margin: 2rem 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 2rem;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .stat-item h4 {
        font-size: 2.5rem;
        color: white;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        font-weight: 700;
    }
    
    .stat-item p {
        color: rgba(255, 255, 255, 1);
        margin: 0;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        font-weight: 500;
    }
    
    .region-section {
        margin: 4rem 0;
    }
    
    .region-title {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        padding: 1rem 2rem;
        border-radius: 15px;
        display: inline-block;
        margin-bottom: 2rem;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .schools-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="floating-element floating-1"></div>
    <div class="floating-element floating-2"></div>
    <div class="floating-element floating-3"></div>
    
    <div class="modern-container">
        <div class="hero-content text-center">
            <h1 style="color: white; margin-bottom: 1.5rem; font-size: 3.5rem;">
                Who's Using {{ Utils::app_name() }}?
            </h1>
            <p style="font-size: 1.4rem; margin-bottom: 2rem; max-width: 700px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 0.9);">
                Trusted by Schools Everywhere - Empowering Over 76 Schools Across Uganda to Simplify Management and Enhance Learning.
            </p>
            
            <div class="stats-banner">
                <div class="stat-item">
                    <h4>76+</h4>
                    <p>Schools</p>
                </div>
                <div class="stat-item">
                    <h4>50K+</h4>
                    <p>Students</p>
                </div>
                <div class="stat-item">
                    <h4>5K+</h4>
                    <p>Teachers</p>
                </div>
                <div class="stat-item">
                    <h4>15+</h4>
                    <p>Districts</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Message -->
<section class="section section-light">
    <div class="modern-container">
        <div class="featured-schools">
            <h3>Join a Growing Community</h3>
            <p>
                We're proud to partner with educational institutions across Uganda, from primary schools to secondary schools, 
                helping them modernize their operations and focus on what matters most - quality education.
            </p>
        </div>
    </div>
</section>

<!-- Schools by Region -->
<section class="section">
    <div class="modern-container">
        <!-- Central Region -->
        <div class="region-section">
            <div class="region-title">
                <i class='bx bx-map' style="margin-right: 0.5rem;"></i>
                Central Region Schools
            </div>
            <div class="schools-grid">
                <div class="school-card">
                    <img src="{{ url('silicon/assets/img/avatar/2.jpg') }}" alt="Al-bushra Islamic Junior School" class="school-logo">
                    <div class="school-name">Al-bushra Islamic Junior School</div>
                    <div class="school-location">Kivebulaya Road, Kampala</div>
                </div>
                
                <div class="school-card">
                    <img src="{{ url('silicon/assets/img/avatar/3.jpg') }}" alt="KIRA Junior School" class="school-logo">
                    <div class="school-name">KIRA Junior School Kito</div>
                    <div class="school-location">Kira, Wakiso</div>
                </div>
                
                <div class="school-card">
                    <img src="{{ url('silicon/assets/img/avatar/4.jpg') }}" alt="Lukman Primary School" class="school-logo">
                    <div class="school-name">Lukman Primary School</div>
                    <div class="school-location">Entebbe, Wakiso</div>
                </div>
                
                <div class="school-card">
                    <img src="{{ url('silicon/assets/img/avatar/5.jpg') }}" alt="Bilal Islamic Secondary School" class="school-logo">
                    <div class="school-name">Bilal Islamic Secondary School</div>
                    <div class="school-location">Bwaise, Kampala</div>
                </div>
                
                <div class="school-card">
                    <img src="{{ url('silicon/assets/img/avatar/6.jpg') }}" alt="Anwar Muslim Secondary School" class="school-logo">
                    <div class="school-name">Anwar Muslim Secondary School</div>
                    <div class="school-location">Mpererwe, Kampala</div>
                </div>
                
                <div class="school-card">
                    <img src="{{ url('silicon/assets/img/avatar/8.jpg') }}" alt="Tasneem Junior School" class="school-logo">
                    <div class="school-name">Tasneem Junior School</div>
                    <div class="school-location">Nsanji, Wakiso</div>
                </div>
            </div>
        </div>
        
        <!-- Eastern Region -->
        <div class="region-section">
            <div class="region-title">
                <i class='bx bx-map' style="margin-right: 0.5rem;"></i>
                Eastern Region Schools
            </div>
            <div class="schools-grid">
                <div class="school-card">
                    <img src="{{ url('silicon/assets/img/avatar/1.jpg') }}" alt="Bright Future Secondary School" class="school-logo">
                    <div class="school-name">Bright Future Secondary School</div>
                    <div class="school-location">Kaliro District</div>
                </div>
            </div>
        </div>
        
        <!-- Western Region -->
        <div class="region-section">
            <div class="region-title">
                <i class='bx bx-map' style="margin-right: 0.5rem;"></i>
                Western Region Schools
            </div>
            <div class="schools-grid">
                <div class="school-card">
                    <img src="{{ url('silicon/assets/img/avatar/7.jpg') }}" alt="Queen of Peace Noble's School" class="school-logo">
                    <div class="school-name">Queen of Peace Noble's School</div>
                    <div class="school-location">Kyegegwa District</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Success Metrics -->
<section class="section section-light">
    <div class="modern-container">
        <div class="section-title text-center">
            <h2>Making a Real Impact</h2>
            <p>See how our partner schools are transforming education management</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-time' style="color: var(--accent-color);"></i>
                </div>
                <h3 class="card-title">90% Time Saved</h3>
                <p class="card-text">
                    On administrative tasks, allowing staff to focus more on education and student development.
                </p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-trending-up' style="color: var(--primary-color);"></i>
                </div>
                <h3 class="card-title">45% Faster Collections</h3>
                <p class="card-text">
                    School fee collection improved significantly with automated billing and payment tracking.
                </p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-group' style="color: var(--accent-color);"></i>
                </div>
                <h3 class="card-title">98% Parent Satisfaction</h3>
                <p class="card-text">
                    Parents love the improved communication and transparency in their children's education.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Join Our Community -->
<section class="section">
    <div class="modern-container">
        <div class="grid grid-2" style="align-items: center; gap: 4rem;">
            <div>
                <h2 style="color: var(--text-dark); margin-bottom: 2rem;">
                    Ready to Join This Community?
                </h2>
                <p style="color: var(--text-light); line-height: 1.8; font-size: 1.1rem; margin-bottom: 2rem;">
                    These schools have already transformed their operations with {{ Utils::app_name() }}. 
                    From small primary schools to large secondary institutions, we provide solutions that scale with your needs.
                </p>
                <div class="flex gap-2" style="margin-bottom: 2rem;">
                    <div style="background: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: 25px; font-size: 0.9rem;">
                        ✓ Quick Setup
                    </div>
                    <div style="background: var(--accent-color); color: white; padding: 0.5rem 1rem; border-radius: 25px; font-size: 0.9rem;">
                        ✓ Local Support
                    </div>
                    <div style="background: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: 25px; font-size: 0.9rem;">
                        ✓ Affordable Pricing
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ url('access-system') }}" class="btn" style="background: var(--primary-color); color: white; padding: 1rem 2rem;">
                        <i class='bx bx-rocket'></i>
                        Access The System
                    </a>
                    <a href="{{ url('contact') }}" class="btn btn-outline" style="border-color: var(--primary-color); color: var(--primary-color);">
                        <i class='bx bx-phone'></i>
                        Contact Us
                    </a>
                </div>
            </div>
            
            <div>
                <div class="card" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; padding: 3rem; text-align: center;">
                    <i class='bx bx-award' style="font-size: 4rem; margin-bottom: 2rem;"></i>
                    <h3 style="margin-bottom: 1rem; color: white;">Trusted Partner</h3>
                    <p style="opacity: 0.95; line-height: 1.6;">
                        Join 76+ schools across Uganda who trust {{ Utils::app_name() }} to manage their operations efficiently. 
                        We're not just a software provider - we're your education technology partner.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="hero-section">
    <div class="modern-container text-center">
        <h2 style="color: white; margin-bottom: 1rem; font-size: 2.5rem;">
            Your School Could Be Next
        </h2>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 0.9);">
            Join the growing community of schools using {{ Utils::app_name() }} to streamline operations and enhance education.
        </p>
        <div class="flex-center gap-3">
            <a href="{{ url('access-system') }}" class="btn" style="background: white; color: var(--primary-color); padding: 1rem 2.5rem; font-size: 1.1rem;">
                <i class='bx bx-rocket'></i>
                Get Started Today
            </a>
            <a href="{{ url('testimonials') }}" class="btn btn-outline" style="border-color: white; color: white;">
                <i class='bx bx-message-dots'></i>
                Read Success Stories
            </a>
        </div>
    </div>
</section>
@endsection
