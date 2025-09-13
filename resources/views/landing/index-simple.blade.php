<?php
use App\Models\Utils;
?>
@extends('layouts.base-layout')

@section('main-content')
<!-- Hero Section -->
<section style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); min-height: 100vh; display: flex; align-items: center; color: white; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,%3Csvg width=\"40\" height=\"40\" viewBox=\"0 0 40 40\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.03\"%3E%3Cpath d=\"m0 40l40-40h-40v40zm40 0v-40h-40l40 40z\"/%3E%3C/g%3E%3C/svg%3E') repeat;"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 style="font-size: 3.5rem; font-weight: 700; line-height: 1.2; margin-bottom: 1.5rem;">
                    Transform Your School with 
                    <span style="background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        {{ Utils::app_name() }}
                    </span>
                </h1>
                <p style="font-size: 1.25rem; opacity: 0.9; line-height: 1.6; margin-bottom: 2rem;">
                    A comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in educational institutions.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ url('access-system') }}" style="padding: 0.875rem 2rem; font-weight: 600; border-radius: 0.5rem; background: linear-gradient(135deg, #00B4FA 0%, #0099D4 100%); color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 14px 0 rgba(0, 180, 250, 0.39); transition: all 0.3s ease;">
                        <i class="bx bx-rocket"></i>
                        Get Started
                    </a>
                    <a href="#features" style="padding: 0.875rem 2rem; font-weight: 600; border-radius: 0.5rem; background: transparent; color: white; border: 2px solid rgba(255, 255, 255, 0.3); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                        <i class="bx bx-play-circle"></i>
                        Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="{{ Utils::get_logo() }}" alt="{{ Utils::app_name() }}" style="max-width: 400px; width: 100%; height: auto; filter: drop-shadow(0 20px 40px rgba(0, 180, 250, 0.3));">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" style="padding: 5rem 0; background: #f8fafc;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="font-size: 2.5rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">
                Powerful Features for Modern Schools
            </h2>
            <p style="font-size: 1.1rem; color: #64748b; max-width: 600px; margin: 0 auto;">
                Everything you need to manage your school efficiently in one comprehensive platform.
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; text-align: center;">
                    <div style="width: 4rem; height: 4rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-user-check"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 1rem;">Student Management</h3>
                    <p style="color: #64748b; line-height: 1.6;">Comprehensive student records, enrollment, and academic tracking system.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; text-align: center;">
                    <div style="width: 4rem; height: 4rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-chalkboard"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 1rem;">Class Management</h3>
                    <p style="color: #64748b; line-height: 1.6;">Organize classes, subjects, and academic schedules with ease.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; text-align: center;">
                    <div style="width: 4rem; height: 4rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-line-chart"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 1rem;">Performance Analytics</h3>
                    <p style="color: #64748b; line-height: 1.6;">Track academic performance and generate detailed reports.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; text-align: center;">
                    <div style="width: 4rem; height: 4rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-money"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 1rem;">Financial Management</h3>
                    <p style="color: #64748b; line-height: 1.6;">Handle fees, payments, and financial records efficiently.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; text-align: center;">
                    <div style="width: 4rem; height: 4rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-message-dots"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 1rem;">Communication Hub</h3>
                    <p style="color: #64748b; line-height: 1.6;">Stay connected with parents, teachers, and students.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; text-align: center;">
                    <div style="width: 4rem; height: 4rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-shield-check"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 1rem;">Secure & Reliable</h3>
                    <p style="color: #64748b; line-height: 1.6;">Enterprise-grade security with reliable data protection.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); padding: 5rem 0; color: white; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.05\"%3E%3Ccircle cx=\"30\" cy=\"30\" r=\"4\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E') repeat;"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="text-center">
            <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
                Ready to Transform Your School?
            </h2>
            <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Join thousands of schools already using {{ Utils::app_name() }} to streamline their operations.
            </p>
            <a href="{{ url('access-system') }}" style="padding: 1rem 2.5rem; font-size: 1.1rem; font-weight: 600; border-radius: 0.5rem; background: white; color: #00B4FA; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); transition: all 0.3s ease;">
                <i class="bx bx-rocket"></i>
                Start Your Free Trial
            </a>
        </div>
    </div>
</section>

@endsection
