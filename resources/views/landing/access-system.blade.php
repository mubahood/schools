<?php
use App\Models\Utils;
?>
@extends('layouts.modern-public')

@section('title', ($company ? $company->name : 'Newline Technologies') . ' - Access School System')
@section('description', 'Choose your access method to get started with our comprehensive school management platform')

@section('head-styles')
<style>
    :root {
        --primary-color: {{ $company && $company->primary_color ? $company->primary_color : '#01AEF0' }};
        --accent-color: {{ $company && $company->accent_color ? $company->accent_color : '#39CA78' }};
    }
</style>
@endsection

@section('content')
<!-- Access System Section -->
<section class="access-section">
    <div class="container">
        <div class="access-header">
            <h1>Access Your School System</h1>
            <p>Choose how you want to get started with {{ $company ? $company->name : 'Newline Technologies' }} School Management System</p>
        </div>
        
        <div class="access-options">
            <div class="access-card">
                <div class="access-icon primary">
                    <i class="fas fa-school"></i>
                </div>
                <h3>Register New School</h3>
                <p>Start your journey with us by registering your educational institution and setting up your management system.</p>
                <a href="{{ url('enterprises/create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Register School
                </a>
            </div>
            
            <div class="access-card">
                <div class="access-icon accent">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <h3>Existing School Login</h3>
                <p>Already registered? Access your school's dashboard and manage your educational operations seamlessly.</p>
                <a href="{{ route('public.login') }}" class="btn btn-accent">
                    <i class="fas fa-lock-open"></i>
                    Login to System
                </a>
            </div>
        </div>
        
        <div class="access-features">
            <div class="feature-grid">
                <div class="feature-item">
                    <i class="fas fa-clock"></i>
                    <span>Quick Setup</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure Platform</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-headset"></i>
                    <span>24/7 Support</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Mobile Ready</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
