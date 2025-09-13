<?php
use App\Models\Utils;
?>
@extends('layouts.onboarding')

@section('title', 'Access Your School System - ' . Utils::app_name())
@section('meta_description', 'Choose your access method to get started with ' . Utils::app_name() . ' - the most comprehensive school management platform.')

@section('progress-info')
    <div class="progress-step">
        <h2 class="progress-title">Welcome to {{ Utils::app_name() }}</h2>
        <p class="progress-description">
            Choose your access method to get started.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator active">1</div>
        <span style="color: rgba(255, 255, 255, 0.8); font-size: 0.9rem;">Access Method</span>
    </div>
@endsection

@section('content')
    <div class="content-title">Access Your School System</div>
    <div class="content-description">
        Choose your option below.
    </div>
    
    <div class="access-options">
        <a href="{{ url('enterprises/create') }}" class="access-card">
            <div class="access-icon primary">
                <i class='bx bx-building'></i>
            </div>
            <h3>Register Your School</h3>
            <p>
                New school? Get started with registration.
            </p>
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
            <p>
                Existing school? Access your dashboard.
            </p>
            <div class="btn btn-accent">
                <i class='bx bx-lock-open'></i>
                Login
            </div>
        </a>
    </div>
@endsection
