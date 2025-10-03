@extends('layouts.onboarding')

@section('title', 'Online Student Application - ' . ($schoolName ?? 'School'))

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Student Application Portal</h2>
        <p class="progress-description">
            Welcome to {{ $schoolName ?? 'our school' }} online application system.
        </p>
    </div>
@endsection

@section('content')
<div class="content-title">Application Information</div>
<div class="content-description">
    Please review the requirements before starting your application.
</div>

@if(!$acceptsApplications)
    <div class="alert alert-warning">
        <i class='bx bx-info-circle'></i>
        @if($customMessage)
            {!! nl2br(e($customMessage)) !!}
        @else
            Online applications are currently closed. Please check back later.
        @endif
    </div>
@else
@if($applicationInstructions)
<div class="mb-4">
    <h5 class="fw-semibold mb-3">Instructions</h5>
    <div class="text-muted">
        {!! $applicationInstructions !!}
    </div>
</div>
@endif

<div class="mb-4">
    <h5 class="fw-semibold mb-3">Application Process</h5>
    <div class="steps-list">
        <div class="step-item">
            <span class="step-number">1</span>
            <div class="step-content">
                <strong>Select Your School</strong>
                <p class="text-muted mb-0">Choose the school you wish to apply to</p>
            </div>
        </div>
        <div class="step-item">
            <span class="step-number">2</span>
            <div class="step-content">
                <strong>Fill Personal Information</strong>
                <p class="text-muted mb-0">Provide your bio data and parent/guardian information</p>
            </div>
        </div>
        <div class="step-item">
            <span class="step-number">3</span>
            <div class="step-content">
                <strong>Review & Confirm</strong>
                <p class="text-muted mb-0">Double-check all information before proceeding</p>
            </div>
        </div>
        <div class="step-item">
            <span class="step-number">4</span>
            <div class="step-content">
                <strong>Upload Documents</strong>
                <p class="text-muted mb-0">Submit required supporting documents</p>
            </div>
        </div>
        <div class="step-item">
            <span class="step-number">5</span>
            <div class="step-content">
                <strong>Submit Application</strong>
                <p class="text-muted mb-0">Receive your application number and track status</p>
            </div>
        </div>
    </div>
</div>

@if(!empty($requiredDocuments))
<div class="mb-4">
    <h5 class="fw-semibold mb-3">Required Documents</h5>
    <ul class="list-unstyled">
        @foreach($requiredDocuments as $doc)
        <li class="mb-2">
            <i class='bx bx-file'></i> {{ $doc['name'] }}
            @if($doc['required'])
                <span class="badge bg-danger ms-2">Required</span>
            @else
                <span class="badge bg-secondary ms-2">Optional</span>
            @endif
        </li>
        @endforeach
    </ul>
</div>
@endif
 

@if($applicationDeadline)
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class='bx bx-calendar fs-4 me-2'></i>
    <div>
        <strong>Application Deadline:</strong> {{ $applicationDeadline->format('F d, Y') }}
        <br><small>All applications must be submitted before this date.</small>
    </div>
</div>
@endif

<div class="action-buttons-container">
    <form action="{{ url('apply/start') }}" method="POST" class="action-button-form">
        @csrf
        <button type="submit" class="btn btn-action btn-action-primary">
            <span class="btn-icon">
                <i class='bx bx-rocket'></i>
            </span>
            <span class="btn-content">
                <span class="btn-title">Start Application</span>
                <span class="btn-subtitle">Begin your journey with us</span>
            </span>
            <span class="btn-arrow">
                <i class='bx bx-right-arrow-alt'></i>
            </span>
        </button>
    </form>
    
    <a href="{{ url('apply/status') }}" class="btn btn-action btn-action-success">
        <span class="btn-icon">
            <i class='bx bx-search-alt'></i>
        </span>
        <span class="btn-content">
            <span class="btn-title">Check Application Status</span>
            <span class="btn-subtitle">Track your application progress</span>
        </span>
        <span class="btn-arrow">
            <i class='bx bx-right-arrow-alt'></i>
        </span>
    </a>
</div>

<div class="mt-4 pt-4 border-top">
    <h6 class="fw-semibold mb-3">Need Help?</h6>
    <div class="text-muted small">
        @if($schoolPhone)
            <p class="mb-1"><i class='bx bx-phone'></i> <strong>Phone:</strong> {{ $schoolPhone }}</p>
        @endif
        @if($schoolEmail)
            <p class="mb-1"><i class='bx bx-envelope'></i> <strong>Email:</strong> <a href="mailto:{{ $schoolEmail }}">{{ $schoolEmail }}</a></p>
        @endif
        @if($schoolAddress)
            <p class="mb-1"><i class='bx bx-map'></i> <strong>Address:</strong> {{ $schoolAddress }}</p>
        @endif
        <p class="mt-3 mb-0">
            <i class='bx bx-time'></i> Applications are typically reviewed within <strong>5-7 business days</strong>.
        </p>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .steps-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .step-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .step-number {
        width: 32px;
        height: 32px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }
    
    .step-content {
        flex: 1;
    }
    
    .step-content strong {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--gray-800);
    }
    
    .step-content p {
        font-size: 0.875rem;
    }
    
    /* Enhanced Action Buttons */
    .action-buttons-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .action-button-form {
        width: 100%;
    }
    
    .btn-action {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        border: none;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .btn-action::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 0;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transition: width 0.3s ease;
        z-index: 0;
    }
    
    .btn-action:hover::before {
        width: 100%;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }
    
    .btn-action:active {
        transform: translateY(0);
    }
    
    /* Primary Button (Blue) */
    .btn-action-primary {
        background: linear-gradient(135deg, #01AEF0 0%, #0199D6 100%);
        color: white;
    }
    
    .btn-action-primary:hover {
        background: linear-gradient(135deg, #0199D6 0%, #0185BC 100%);
        color: white;
    }
    
    /* Success Button (Green) */
    .btn-action-success {
        background: linear-gradient(135deg, #39CA78 0%, #2eb865 100%);
        color: white;
    }
    
    .btn-action-success:hover {
        background: linear-gradient(135deg, #2eb865 0%, #28a557 100%);
        color: white;
    }
    
    .btn-icon {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 24px;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover .btn-icon {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }
    
    .btn-content {
        flex: 1;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        position: relative;
        z-index: 1;
    }
    
    .btn-title {
        font-size: 1.125rem;
        font-weight: 600;
        display: block;
        line-height: 1.3;
    }
    
    .btn-subtitle {
        font-size: 0.875rem;
        opacity: 0.9;
        display: block;
        font-weight: 400;
        line-height: 1.3;
    }
    
    .btn-arrow {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover .btn-arrow {
        transform: translateX(4px);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .btn-action {
            padding: 1rem 1.25rem;
            gap: 0.875rem;
        }
        
        .btn-icon {
            width: 44px;
            height: 44px;
            font-size: 22px;
        }
        
        .btn-title {
            font-size: 1rem;
        }
        
        .btn-subtitle {
            font-size: 0.813rem;
        }
        
        .btn-arrow {
            width: 28px;
            height: 28px;
            font-size: 20px;
        }
    }
    
    @media (max-width: 480px) {
        .btn-action {
            padding: 0.875rem 1rem;
            gap: 0.75rem;
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
            font-size: 20px;
            border-radius: 8px;
        }
        
        .btn-title {
            font-size: 0.938rem;
        }
        
        .btn-subtitle {
            font-size: 0.75rem;
        }
        
        .btn-arrow {
            width: 24px;
            height: 24px;
            font-size: 18px;
        }
    }
</style>
@endpush
