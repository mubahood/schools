<?php
use App\Models\Utils;
?>
@extends('layouts.onboarding')

@section('title', 'Access Your School System - ' . Utils::app_name())
@section('meta_description', 'Choose your access method to get started with ' . Utils::app_name() . ' - the most comprehensive school management platform.')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Get Started</h2>
        <p class="progress-description">
            Choose how you want to access the system
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator active">1</div>
        <span>Access Method</span>
    </div>
    
    <!-- Quick Stats for Motivation -->
    <div class="access-quick-stats">
        <div class="quick-stat">
            <i class='bx bx-check-circle'></i>
            <span>Quick Setup</span>
        </div>
        <div class="quick-stat">
            <i class='bx bx-shield'></i>
            <span>Secure Platform</span>
        </div>
        <div class="quick-stat">
            <i class='bx bx-support'></i>
            <span>24/7 Support</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="access-header">
        <h1 class="access-title">Access Your School System</h1>
        <p class="access-subtitle">Choose your option below</p>
    </div>
    
    <div class="access-options">
        <a href="{{ url('enterprises/create') }}" class="access-card">
            <div class="access-icon primary">
                <i class='bx bx-building'></i>
            </div>
            <h3>Register Your School</h3>
            <p>New school? Get started with registration.</p>
            <div class="btn btn-primary">
                <i class='bx bx-plus-circle'></i>
                Register
            </div>
        </a>
        
        <a href="{{ url('auth/login') }}" class="access-card">
            <div class="access-icon accent">
                <i class='bx bx-log-in'></i>
            </div>
            <h3>School Login</h3>
            <p>Existing school? Access your dashboard.</p>
            <div class="btn btn-accent">
                <i class='bx bx-lock-open'></i>
                Login
            </div>
        </a>
    </div>
@endsection
