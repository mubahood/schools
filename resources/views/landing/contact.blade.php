<?php
use App\Models\Utils;
?>
@extends('layouts.modern-public')

@section('title', 'Contact Our Team - Reach Out to Us')
@section('meta_description', 'Get in touch with our team. We are here to help you transform your school management. Contact us for support, demos, or questions about our system.')

@section('content')
<style>
    .contact-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        text-align: center;
        border: 2px solid transparent;
    }
    
    .contact-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-color);
        box-shadow: 0 20px 40px rgba(0, 180, 250, 0.15);
    }
    
    .contact-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
        color: white;
        font-size: 2rem;
    }
    
    .contact-form {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
    }
    
    .form-group {
        margin-bottom: 2rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
        font-weight: 600;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
        background: #f8f9fa;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary-color);
        background: white;
    }
    
    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }
    
    .contact-info {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        border-radius: 20px;
        padding: 3rem;
        color: white;
        margin-top: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .contact-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }
    
    .contact-info > * {
        position: relative;
        z-index: 2;
    }
    
    .contact-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        gap: 1rem;
    }
    
    .contact-info-item:last-child {
        margin-bottom: 0;
    }
    
    .contact-info-icon {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
    }
    
        .contact-info-text h4 {
        margin: 0 0 0.5rem 0;
        color: white;
        font-size: 1.1rem;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        font-weight: 600;
    }
    
    .contact-info-text p {
        margin: 0;
        color: rgba(255, 255, 255, 1);
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        font-weight: 500;
    }
    
    .office-hours {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 2rem;
        margin-top: 2rem;
        backdrop-filter: blur(10px);
    }
    
    .map-container {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
        height: 400px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-light);
        font-size: 1.1rem;
    }
    
    .support-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }
    
        .submit-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 15px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0, 180, 250, 0.3);
    }
    
    @media (max-width: 768px) {
        .contact-form,
        .contact-info {
            padding: 2rem;
        }
        
        .support-options {
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
                Reach Out to Us
            </h1>
            <p style="font-size: 1.4rem; margin-bottom: 2rem; max-width: 700px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 0.9);">
                We're here to help you transform your school's operations. Get in touch for support, demos, or any questions about {{ Utils::app_name() }}.
            </p>
        </div>
    </div>
</section>

<!-- Contact Methods -->
<section class="section section-light">
    <div class="modern-container">
        <div class="section-title text-center">
            <h2>Get in Touch</h2>
            <p>Choose the best way to reach our team</p>
        </div>
        
        <div class="support-options">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class='bx bx-phone'></i>
                </div>
                <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Call Us</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    Speak directly with our support team for immediate assistance.
                </p>
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--primary-color);">+256 700 000 000</strong>
                </div>
                <small style="color: var(--text-light);">Mon - Fri: 8:00 AM - 6:00 PM</small>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class='bx bx-envelope'></i>
                </div>
                <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Email Us</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    Send us detailed questions and we'll respond within 24 hours.
                </p>
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--primary-color);">support@8technologies.com</strong>
                </div>
                <small style="color: var(--text-light);">Response within 24 hours</small>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class='bx bx-chat'></i>
                </div>
                <h3 style="color: var(--text-dark); margin-bottom: 1rem;">WhatsApp</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    Quick questions and support via WhatsApp messaging.
                </p>
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--primary-color);">+256 700 000 000</strong>
                </div>
                <small style="color: var(--text-light);">Fast response during business hours</small>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class='bx bx-video'></i>
                </div>
                <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Book a Demo</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    Schedule a personalized demo to see {{ Utils::app_name() }} in action.
                </p>
                <div style="margin-bottom: 1rem;">
                    <a href="https://forms.gle/NP8RXx7YcpPbfi6b8" target="_blank" class="btn" style="background: var(--primary-color); color: white; padding: 0.5rem 1.5rem; text-decoration: none; border-radius: 8px;">
                        Schedule Demo
                    </a>
                </div>
                <small style="color: var(--text-light);">Free 30-minute consultation</small>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Info -->
<section class="section">
    <div class="modern-container">
        <div class="grid grid-2" style="gap: 4rem; align-items: start;">
            <!-- Contact Form -->
            <div>
                <h2 style="color: var(--text-dark); margin-bottom: 2rem;">Send Us a Message</h2>
                <form class="contact-form" action="#" method="POST">
                    @csrf
                    <div class="grid grid-2" style="gap: 1rem;">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="school_name">School Name</label>
                        <input type="text" id="school_name" name="school_name">
                    </div>
                    
                    <div class="form-group">
                        <label for="inquiry_type">Type of Inquiry</label>
                        <select id="inquiry_type" name="inquiry_type">
                            <option value="">Select an option</option>
                            <option value="demo">Request a Demo</option>
                            <option value="support">Technical Support</option>
                            <option value="pricing">Pricing Information</option>
                            <option value="partnership">Partnership Inquiry</option>
                            <option value="general">General Question</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message *</label>
                        <textarea id="message" name="message" placeholder="Tell us how we can help you..." required></textarea>
                    </div>
                    
                    <a href="#" class="submit-btn" onclick="alert('Contact form submitted! We will get back to you soon.'); return false;">
                        <i class='bx bx-send' style="margin-right: 0.5rem;"></i>
                        Send Message
                    </a>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div>
                <div class="contact-info">
                    <h3 style="color: white; margin-bottom: 2rem; font-size: 1.8rem;">Contact Information</h3>
                    
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class='bx bx-map'></i>
                        </div>
                        <div class="contact-info-text">
                            <h4>Our Office</h4>
                            <p>Kampala, Uganda<br>East Africa</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class='bx bx-phone'></i>
                        </div>
                        <div class="contact-info-text">
                            <h4>Phone Numbers</h4>
                            <p>Primary: +256 700 000 000<br>WhatsApp: +256 700 000 000</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class='bx bx-envelope'></i>
                        </div>
                        <div class="contact-info-text">
                            <h4>Email Addresses</h4>
                            <p>Support: support@8technologies.com<br>Sales: sales@8technologies.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class='bx bxl-twitter'></i>
                        </div>
                        <div class="contact-info-text">
                            <h4>Follow Us</h4>
                            <p>Twitter: @8TechConsults<br>Stay updated with our latest news</p>
                        </div>
                    </div>
                    
                    <div class="office-hours">
                        <h4 style="color: white; margin-bottom: 1rem;">Office Hours</h4>
                        <p style="margin-bottom: 0.5rem;"><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM</p>
                        <p style="margin-bottom: 0.5rem;"><strong>Saturday:</strong> 9:00 AM - 2:00 PM</p>
                        <p style="margin-bottom: 0;"><strong>Sunday:</strong> Closed</p>
                        <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">
                            <i class='bx bx-info-circle'></i> Emergency support available 24/7 for critical issues
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="section section-light">
    <div class="modern-container">
        <div class="section-title text-center">
            <h2>Find Us</h2>
            <p>Located in the heart of Kampala, Uganda</p>
        </div>
        
        <div class="map-container">
            <div style="text-align: center;">
                <i class='bx bx-map' style="font-size: 3rem; margin-bottom: 1rem; color: var(--primary-color);"></i>
                <h3 style="color: var(--text-dark); margin-bottom: 1rem;">We're Located in Kampala</h3>
                <p style="color: var(--text-light); margin-bottom: 2rem;">
                    Our team is based in Uganda and understands the local education landscape.
                </p>
                <a href="mailto:support@8technologies.com" class="btn" style="background: var(--primary-color); color: white; padding: 1rem 2rem; text-decoration: none; border-radius: 10px;">
                    <i class='bx bx-envelope'></i>
                    Contact Us for Directions
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section">
    <div class="modern-container">
        <div class="section-title text-center">
            <h2>Frequently Asked Questions</h2>
            <p>Quick answers to common questions</p>
        </div>
        
        <div class="grid grid-2" style="gap: 3rem;">
            <div>
                <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem;">How quickly can we get started?</h4>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Most schools are up and running within 1-2 weeks. We provide full setup support and training to ensure a smooth transition.
                    </p>
                </div>
                
                <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Do you provide training?</h4>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Yes! We provide comprehensive training for all users, from administrators to teachers, ensuring everyone knows how to use the system effectively.
                    </p>
                </div>
            </div>
            
            <div>
                <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Is ongoing support included?</h4>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Absolutely! We provide ongoing support via phone, email, and WhatsApp. Our team is always ready to help with any questions or issues.
                    </p>
                </div>
                
                <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Can we customize the system?</h4>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Yes, {{ Utils::app_name() }} can be customized to fit your school's specific needs and processes. Contact us to discuss your requirements.
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
            Ready to Get Started?
        </h2>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; color: rgba(255, 255, 255, 0.9);">
            Don't wait - transform your school's operations today with {{ Utils::app_name() }}.
        </p>
        <div class="flex-center gap-3">
            <a href="{{ url('access-system') }}" class="btn" style="background: white; color: var(--primary-color); padding: 1rem 2.5rem; font-size: 1.1rem;">
                <i class='bx bx-rocket'></i>
                Access The System
            </a>
            <a href="https://forms.gle/NP8RXx7YcpPbfi6b8" target="_blank" class="btn btn-outline" style="border-color: white; color: white;">
                <i class='bx bx-video'></i>
                Request Demo
            </a>
        </div>
    </div>
</section>
@endsection
