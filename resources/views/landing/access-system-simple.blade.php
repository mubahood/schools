<?php
use App\Models\Utils;
?>
@extends('layouts.base-layout')

@section('main-content')
<!-- Hero Section -->
<section style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); min-height: 60vh; display: flex; align-items: center; color: white; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,%3Csvg width=\"40\" height=\"40\" viewBox=\"0 0 40 40\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.03\"%3E%3Cpath d=\"m0 40l40-40h-40v40zm40 0v-40h-40l40 40z\"/%3E%3C/g%3E%3C/svg%3E') repeat;"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-12 text-center">
                <h1 style="font-size: 3rem; font-weight: 700; line-height: 1.2; margin-bottom: 1rem;">
                    Access Your 
                    <span style="background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        School System
                    </span>
                </h1>
                <p style="font-size: 1.2rem; opacity: 0.9; line-height: 1.6;">
                    Choose your access method to get started with {{ Utils::app_name() }}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Access Options Section -->
<section style="padding: 4rem 0; background: #f8fafc;">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-5">
                <a href="#" style="background: white; border-radius: 1rem; padding: 2.5rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; text-decoration: none; display: block; color: inherit;">
                    <div style="width: 5rem; height: 5rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; margin: 0 auto 1.5rem;">
                        <i class="bx bx-building"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b; text-align: center;">Register Your School</h3>
                    <p style="color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; text-align: center;">
                        New to {{ Utils::app_name() }}? Register your educational institution and get started with our comprehensive school management system.
                    </p>
                    <div style="text-align: center;">
                        <span style="padding: 0.75rem 1.5rem; font-weight: 600; border-radius: 0.5rem; background: linear-gradient(135deg, #00B4FA 0%, #0099D4 100%); color: white; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="bx bx-plus-circle"></i>
                            Register Now
                        </span>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 col-lg-5">
                <a href="#" style="background: white; border-radius: 1rem; padding: 2.5rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; text-decoration: none; display: block; color: inherit;">
                    <div style="width: 5rem; height: 5rem; background: linear-gradient(135deg, #36CA78 0%, #2BB86A 100%); border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; margin: 0 auto 1.5rem;">
                        <i class="bx bx-log-in"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b; text-align: center;">School Login</h3>
                    <p style="color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; text-align: center;">
                        Already registered? Access your school's dashboard, manage students, teachers, and all administrative functions.
                    </p>
                    <div style="text-align: center;">
                        <span style="padding: 0.75rem 1.5rem; font-weight: 600; border-radius: 0.5rem; background: linear-gradient(135deg, #36CA78 0%, #2BB86A 100%); color: white; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="bx bx-lock-open"></i>
                            Login
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Support Section -->
<section style="padding: 4rem 0; background: white;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="font-size: 2.5rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">
                Need Help Getting Started?
            </h2>
            <p style="font-size: 1.1rem; color: #64748b;">
                Our support team is here to assist you every step of the way
            </p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div style="background: #f8fafc; border-radius: 0.75rem; padding: 2rem; text-align: center; transition: all 0.3s ease;">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-phone"></i>
                    </div>
                    <h4 style="font-size: 1.2rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Phone Support</h4>
                    <p style="color: #64748b; margin: 0;">Call us for immediate assistance</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div style="background: #f8fafc; border-radius: 0.75rem; padding: 2rem; text-align: center; transition: all 0.3s ease;">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-envelope"></i>
                    </div>
                    <h4 style="font-size: 1.2rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Email Support</h4>
                    <p style="color: #64748b; margin: 0;">Send us your questions anytime</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div style="background: #f8fafc; border-radius: 0.75rem; padding: 2rem; text-align: center; transition: all 0.3s ease;">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #00B4FA 0%, #36CA78 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; margin: 0 auto 1rem;">
                        <i class="bx bx-book-open"></i>
                    </div>
                    <h4 style="font-size: 1.2rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Documentation</h4>
                    <p style="color: #64748b; margin: 0;">Comprehensive guides and tutorials</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
