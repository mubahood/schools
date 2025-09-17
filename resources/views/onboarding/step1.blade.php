<?php
use App\Models\Utils;
?>
@extends('layouts.onboarding')

@section('title', 'Welcome to ' . Utils::app_name() . ' - School Registration')
@section('meta_description', 'Welcome to ' . Utils::app_name() . ' - Start your school management journey today.')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Welcome to {{ Utils::app_name() }}</h2>
        <p class="progress-description">
            Let's get your school registered in just 5 simple steps.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator active">1</div>
        <span>Introduction</span>
    </div>
@endsection

@section('content')
    <div class="content-title">Start Your School Management Journey</div>
    <div class="content-description">
        Register your school with our comprehensive management platform.
    </div>
    
    <div class="intro-content">
        <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.1rem;">What is School Registration?</h3>
            <p style="color: var(--text-light); font-size: 0.9rem; line-height: 1.4; margin-bottom: 1rem;">
                School registration creates your dedicated management portal where you can handle all aspects of your educational institution.
            </p>
            
            <h4 style="color: var(--text-dark); margin-bottom: 0.75rem; font-size: 1rem;">You'll get access to:</h4>
            <ul style="color: var(--text-light); font-size: 0.85rem; line-height: 1.4; margin-left: 1rem;">
                <li>Student enrollment and management</li>
                <li>Staff administration and payroll</li>
                <li>Academic records and grading</li>
                <li>Financial management and billing</li>
                <li>Parent communication portal</li>
            </ul>
        </div>
        
        <div style="background: var(--background-light); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <h4 style="color: var(--text-dark); margin-bottom: 0.75rem; font-size: 1rem;">Registration Process:</h4>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="display: flex; align-items: center; color: var(--text-light); font-size: 0.85rem;">
                    <span style="background: var(--primary-color); color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; font-size: 0.7rem;">1</span>
                    Introduction (current step)
                </div>
                <div style="display: flex; align-items: center; color: var(--text-light); font-size: 0.85rem;">
                    <span style="background: var(--border-color); color: var(--text-light); width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; font-size: 0.7rem;">2</span>
                    Your personal information
                </div>
                <div style="display: flex; align-items: center; color: var(--text-light); font-size: 0.85rem;">
                    <span style="background: var(--border-color); color: var(--text-light); width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; font-size: 0.7rem;">3</span>
                    School details and information
                </div>
                <div style="display: flex; align-items: center; color: var(--text-light); font-size: 0.85rem;">
                    <span style="background: var(--border-color); color: var(--text-light); width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; font-size: 0.7rem;">4</span>
                    Review and confirmation
                </div>
                <div style="display: flex; align-items: center; color: var(--text-light); font-size: 0.85rem;">
                    <span style="background: var(--border-color); color: var(--text-light); width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; font-size: 0.7rem;">5</span>
                    Welcome and next steps
                </div>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 1rem;">
        <a href="{{ url('/') }}" class="btn btn-secondary" style="flex: 1;">
            <i class='bx bx-arrow-back'></i>
            Back to Home
        </a>
        <a href="{{ route('onboarding.step2') }}" class="btn btn-primary" style="flex: 2;">
            Get Started
            <i class='bx bx-arrow-right'></i>
        </a>
    </div>
@endsection
