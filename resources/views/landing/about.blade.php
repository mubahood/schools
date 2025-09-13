<?php
use App\Models\Utils;
?>
@extends('layouts.modern-public')

@section('title', 'About Our School Management System - Our Partners')
@section('meta_description', 'Learn about our school management system and our mission to revolutionize school management in Uganda. See our story and impact.')

@section('content')
<style>
    .video-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0, 180, 250, 0.15);
    }
    
    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .story-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .story-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, var(--primary-color) 0%, var(--accent-color) 100%);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        background: var(--primary-color);
        border-radius: 50%;
        transform: translateX(-50%);
        box-shadow: 0 0 0 4px white, 0 0 0 6px var(--primary-color);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }
    
    .stat-card {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
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
                About {{ Utils::app_name() }}
            </h1>
            <p style="font-size: 1.4rem; margin-bottom: 2rem; max-width: 700px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 1); text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); font-weight: 500;">
                Empowering Ugandan schools with innovative technology solutions that streamline operations 
                and enhance educational outcomes.
            </p>
             
        </div>
    </div>
</section>

<!-- Company Story Section -->
<section class="section section-light">
    <div class="modern-container">
        <div class="grid grid-2" style="align-items: center; gap: 4rem;">
            <div>
                <h2 style="color: var(--text-dark); margin-bottom: 2rem;">Our Story</h2>
                <div class="story-timeline">
                    <div class="timeline-item">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">The Problem</h3>
                        <p style="color: var(--text-light); line-height: 1.8;">
                            We saw Ugandan schools struggling with manual processes, disorganized records, 
                            and inefficient communication systems. Administrators were drowning in paperwork 
                            instead of focusing on education.
                        </p>
                    </div>
                    
                    <div class="timeline-item">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">The Solution</h3>
                        <p style="color: var(--text-light); line-height: 1.8;">
                            {{ Utils::app_name() }} was born from the vision to digitize and streamline 
                            school management across Uganda. We built a comprehensive platform that handles 
                            everything from student enrollment to fee collection.
                        </p>
                    </div>
                    
                    <div class="timeline-item">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">The Impact</h3>
                        <p style="color: var(--text-light); line-height: 1.8;">
                            Today, over 76 schools across Uganda trust {{ Utils::app_name() }} to manage 
                            their operations efficiently, allowing educators to focus on what matters most - 
                            providing quality education.
                        </p>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 3rem; position: relative; max-width: 600px; margin-left: auto; margin-right: auto;">
                <div style="position: relative; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; z-index: 5;">
                        <a href="https://www.youtube.com/watch?v=-4j5okWNORg" 
                           target="_blank"
                           style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s ease; backdrop-filter: blur(10px);"
                           onmouseover="this.style.transform='scale(1.1)'; this.style.background='rgba(255, 255, 255, 1)';"
                           onmouseout="this.style.transform='scale(1)'; this.style.background='rgba(255, 255, 255, 0.9)';">
                            <i class='bx bx-play' style="font-size: 2rem; color: var(--primary-color); margin-left: 4px;"></i>
                        </a>
                    </div>
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.3);"></div>
                    <img src="{{ asset('assets/01-TUSOME.png') }}" alt="{{ Utils::app_name() }} Platform Demo" style="width: 100%; height: auto; display: block;">
                </div>
                <p style="text-align: center; margin-top: 1rem; color: rgba(255, 255, 255, 0.8); font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);">
                    Watch our platform in action
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="section">
    <div class="modern-container">
        <div class="grid grid-2" style="gap: 4rem;">
            <div class="card" style="padding: 3rem; background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.3); z-index: 1;"></div>
                <div style="text-align: center; position: relative; z-index: 2;">
                    <i class='bx bx-target-lock' style="font-size: 4rem; margin-bottom: 2rem; opacity: 1; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);"></i>
                    <h2 style="margin-bottom: 2rem; color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); font-weight: 700;">Our Mission</h2>
                    <p style="font-size: 1.2rem; line-height: 1.8; color: rgba(255, 255, 255, 1); text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5); font-weight: 500;">
                        To revolutionize education management in Uganda by providing schools with innovative, 
                        user-friendly technology solutions that streamline operations and enhance learning experiences.
                    </p>
                </div>
            </div>
            
            <div class="card" style="padding: 3rem; background: white; border: 2px solid var(--primary-color);">
                <div style="text-align: center;">
                    <i class='bx bx-bulb' style="font-size: 4rem; margin-bottom: 2rem; color: var(--accent-color);"></i>
                    <h2 style="margin-bottom: 2rem; color: var(--text-dark);">Our Vision</h2>
                    <p style="font-size: 1.2rem; line-height: 1.8; color: var(--text-light);">
                        A future where every school in Uganda operates efficiently with digital tools, 
                        enabling educators to focus entirely on delivering quality education and nurturing student success.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Impact Statistics -->
<section class="section section-light">
    <div class="modern-container">
        <div class="section-title text-center">
            <h2>Our Impact in Numbers</h2>
            <p>See how {{ Utils::app_name() }} is making a difference in Ugandan education</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">76+</div>
                <h3 style="color: var(--text-dark); margin-bottom: 0.5rem;">Schools</h3>
                <p style="color: var(--text-light);">Across Uganda trust our platform</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">50K+</div>
                <h3 style="color: var(--text-dark); margin-bottom: 0.5rem;">Students</h3>
                <p style="color: var(--text-light);">Records managed effectively</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">5K+</div>
                <h3 style="color: var(--text-dark); margin-bottom: 0.5rem;">Teachers</h3>
                <p style="color: var(--text-light);">Connected and empowered</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">99.9%</div>
                <h3 style="color: var(--text-dark); margin-bottom: 0.5rem;">Uptime</h3>
                <p style="color: var(--text-light);">Reliable service guarantee</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Values -->
<section class="section">
    <div class="modern-container">
        <div class="section-title text-center">
            <h2>Our Core Values</h2>
            <p>The principles that guide everything we do</p>
        </div>
        
        <div class="grid grid-3">
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-heart' style="color: #e74c3c;"></i>
                </div>
                <h3 class="card-title">Education First</h3>
                <p class="card-text">
                    We believe quality education is the foundation of progress. Every feature we build 
                    is designed to enhance the learning experience.
                </p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-cog' style="color: var(--accent-color);"></i>
                </div>
                <h3 class="card-title">Simplicity</h3>
                <p class="card-text">
                    Complex problems deserve simple solutions. We make powerful tools that are 
                    intuitive and easy to use for everyone.
                </p>
            </div>
            
            <div class="card text-center">
                <div class="card-icon">
                    <i class='bx bx-group' style="color: var(--primary-color);"></i>
                </div>
                <h3 class="card-title">Community</h3>
                <p class="card-text">
                    We're not just a software company - we're partners in Uganda's educational 
                    journey, committed to local success.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="hero-section">
    <div class="modern-container text-center">
        <h2 style="color: white; margin-bottom: 1rem; font-size: 2.5rem;">
            Ready to Join Our Mission?
        </h2>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 0.9);">
            Let's work together to transform your school's operations and create a better learning environment for students.
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
