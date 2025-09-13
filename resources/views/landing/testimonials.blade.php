<?php
use App\Models\Utils;
?>
@extends('layouts.modern-public')

@section('title', 'What Schools Say About Our System - Testimonials')
@section('meta_description', 'Read testimonials from schools across Uganda using our school management system to streamline their operations and improve education management.')

@section('content')
<style>
    .testimonial-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        margin-bottom: 2rem;
    }
    
    .testimonial-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 180, 250, 0.15);
    }
    
    .testimonial-card::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 2rem;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .quote-icon {
        position: absolute;
        top: -5px;
        left: 2.75rem;
        color: white;
        font-size: 1.5rem;
        z-index: 2;
    }
    
    .testimonial-text {
        font-size: 1.1rem;
        line-height: 1.8;
        color: var(--text-light);
        margin: 2rem 0 1.5rem 0;
        font-style: italic;
    }
    
    .testimonial-author {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .author-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary-color);
    }
    
    .author-info h4 {
        margin: 0;
        color: var(--text-dark);
        font-size: 1.1rem;
    }
    
    .author-info p {
        margin: 0;
        color: var(--text-light);
        font-size: 0.9rem;
    }
    
    .star-rating {
        display: flex;
        gap: 0.25rem;
        margin: 1rem 0;
    }
    
    .star-rating i {
        color: #ffc107;
        font-size: 1.2rem;
    }
    
    .testimonial-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }
    
    .featured-testimonial {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        text-align: center;
        padding: 4rem 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .featured-testimonial::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }
    
    .featured-testimonial > * {
        position: relative;
        z-index: 2;
    }
    
    .featured-testimonial .testimonial-text {
        color: rgba(255, 255, 255, 1);
        font-size: 1.4rem;
        max-width: 800px;
        margin: 0 auto 2rem auto;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        font-weight: 500;
    }
    
    .featured-testimonial .author-info h4,
    .featured-testimonial .author-info p {
        color: white;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .testimonial-grid {
            grid-template-columns: 1fr;
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
                What People Say About {{ Utils::app_name() }}
            </h1>
            <p style="font-size: 1.4rem; margin-bottom: 2rem; max-width: 700px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 0.9);">
                Hear from educational leaders across Uganda who have transformed their institutions with our platform.
            </p>
        </div>
    </div>
</section>

<!-- Featured Testimonial -->
<section class="section section-light">
    <div class="modern-container">
        <div class="featured-testimonial testimonial-card">
            <i class='bx bxs-quote-left quote-icon'></i>
            <div class="testimonial-text">
                "{{ Utils::app_name() }} has completely transformed our school operations. From admissions to finance, 
                everything is automated, saving us time and reducing errors. It's exactly what Ugandan schools needed!"
            </div>
            <div class="testimonial-author" style="justify-content: center;">
                <img src="{{ url('silicon/assets/img/avatar/1.jpg') }}" alt="School Administrator" class="author-avatar">
                <div class="author-info">
                    <h4>School Administrator</h4>
                    <p>BRIGHT FUTURE SECONDARY SCHOOL - Kaliro</p>
                </div>
            </div>
            <div class="star-rating" style="justify-content: center;">
                <i class='bx bxs-star'></i>
                <i class='bx bxs-star'></i>
                <i class='bx bxs-star'></i>
                <i class='bx bxs-star'></i>
                <i class='bx bxs-star'></i>
            </div>
        </div>
    </div>
</section>

<!-- All Testimonials -->
<section class="section">
    <div class="modern-container">
        <div class="section-title text-center">
            <h2>Stories from Our School Community</h2>
            <p>Real experiences from administrators, teachers, and staff using {{ Utils::app_name() }}</p>
        </div>
        
        <div class="testimonial-grid">
            <!-- Testimonial 1 -->
            <div class="testimonial-card">
                <i class='bx bxs-quote-left quote-icon'></i>
                <div class="testimonial-text">
                    "Managing student records and communication used to be a nightmare. With {{ Utils::app_name() }}, 
                    parents, teachers, and administrators are always in sync!"
                </div>
                <div class="star-rating">
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                </div>
                <div class="testimonial-author">
                    <img src="{{ url('silicon/assets/img/avatar/2.jpg') }}" alt="Head Teacher" class="author-avatar">
                    <div class="author-info">
                        <h4>Head Teacher</h4>
                        <p>LUKMAN PRIMARY SCHOOL - Entebbe</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="testimonial-card">
                <i class='bx bxs-quote-left quote-icon'></i>
                <div class="testimonial-text">
                    "School fees tracking has never been easier! Our collections have improved significantly since we 
                    started using {{ Utils::app_name() }}'s automated billing system."
                </div>
                <div class="star-rating">
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bx-star text-muted'></i>
                </div>
                <div class="testimonial-author">
                    <img src="{{ url('silicon/assets/img/avatar/3.jpg') }}" alt="Finance Manager" class="author-avatar">
                    <div class="author-info">
                        <h4>Finance Manager</h4>
                        <p>Kira Junior School - Kito</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 3 -->
            <div class="testimonial-card">
                <i class='bx bxs-quote-left quote-icon'></i>
                <div class="testimonial-text">
                    "The mobile app is a game-changer! Parents can check student progress, fees, and updates in real time, 
                    improving engagement and satisfaction."
                </div>
                <div class="star-rating">
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                </div>
                <div class="testimonial-author">
                    <img src="{{ url('silicon/assets/img/avatar/4.jpg') }}" alt="Communications Director" class="author-avatar">
                    <div class="author-info">
                        <h4>Communications Director</h4>
                        <p>Bilal Islamic Secondary School - Bwaise</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 4 -->
            <div class="testimonial-card">
                <i class='bx bxs-quote-left quote-icon'></i>
                <div class="testimonial-text">
                    "{{ Utils::app_name() }} is a lifesaver! Managing admissions and fees used to take days, now it takes minutes. 
                    The support team is also incredibly responsive."
                </div>
                <div class="star-rating">
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                </div>
                <div class="testimonial-author">
                    <img src="{{ url('silicon/assets/img/avatar/5.jpg') }}" alt="School Bursar" class="author-avatar">
                    <div class="author-info">
                        <h4>School Bursar</h4>
                        <p>ANWAR MUSLIM SECONDARY SCHOOL - Mpererwe</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 5 -->
            <div class="testimonial-card">
                <i class='bx bxs-quote-left quote-icon'></i>
                <div class="testimonial-text">
                    "Communication with parents is so much easier now. Sending updates via SMS and the app keeps everyone 
                    informed instantly. {{ Utils::app_name() }} really understands school needs."
                </div>
                <div class="star-rating">
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                </div>
                <div class="testimonial-author">
                    <img src="{{ url('silicon/assets/img/avatar/6.jpg') }}" alt="Deputy Head Teacher" class="author-avatar">
                    <div class="author-info">
                        <h4>Deputy Head Teacher</h4>
                        <p>QUEEN OF PEACE NOBLE'S SCHOOL - KYEGEGWA</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 6 -->
            <div class="testimonial-card">
                <i class='bx bxs-quote-left quote-icon'></i>
                <div class="testimonial-text">
                    "The financial module is fantastic. Tracking school fees and managing School Pay transactions is seamless. 
                    {{ Utils::app_name() }} provides great value for money."
                </div>
                <div class="star-rating">
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bx-star text-muted'></i>
                </div>
                <div class="testimonial-author">
                    <img src="{{ url('silicon/assets/img/avatar/7.jpg') }}" alt="Accounts Manager" class="author-avatar">
                    <div class="author-info">
                        <h4>Accounts Manager</h4>
                        <p>Tasneem Junior School - Nsanji</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section section-light">
    <div class="modern-container">
        <div class="section-title text-center">
            <h2>Trusted by Schools Everywhere</h2>
            <p>Join the growing community of satisfied educational institutions</p>
        </div>
        
        <div class="grid grid-4 text-center">
            <div class="card">
                <h3 style="font-size: 3rem; color: var(--primary-color); margin-bottom: 0.5rem;">98%</h3>
                <h4 class="card-title">Satisfaction Rate</h4>
                <p class="card-text">Schools rate us as excellent</p>
            </div>
            
            <div class="card">
                <h3 style="font-size: 3rem; color: var(--accent-color); margin-bottom: 0.5rem;">76+</h3>
                <h4 class="card-title">Schools Using</h4>
                <p class="card-text">Across Uganda trust our platform</p>
            </div>
            
            <div class="card">
                <h3 style="font-size: 3rem; color: var(--primary-color); margin-bottom: 0.5rem;">24/7</h3>
                <h4 class="card-title">Support</h4>
                <p class="card-text">Always here when you need us</p>
            </div>
            
            <div class="card">
                <h3 style="font-size: 3rem; color: var(--accent-color); margin-bottom: 0.5rem;">5★</h3>
                <h4 class="card-title">Average Rating</h4>
                <p class="card-text">From our school partners</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="hero-section">
    <div class="modern-container text-center">
        <h2 style="color: white; margin-bottom: 1rem; font-size: 2.5rem;">
            Ready to Join These Success Stories?
        </h2>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 0.9);">
            Transform your school's operations and create your own success story with {{ Utils::app_name() }}.
        </p>
        <div class="flex-center gap-3">
            <a href="{{ url('access-system') }}" class="btn" style="background: white; color: var(--primary-color); padding: 1rem 2.5rem; font-size: 1.1rem;">
                <i class='bx bx-rocket'></i>
                Access The System
            </a>
            <a href="{{ url('contact') }}" class="btn btn-outline" style="border-color: white; color: white;">
                <i class='bx bx-phone'></i>
                Contact Us
            </a>
        </div>
    </div>
</section>
@endsection
