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
@if($applicationFee > 0)
<div class="alert alert-info d-flex align-items-center mb-4">
    <i class='bx bx-money fs-4 me-2'></i>
    <div>
        <strong>Application Fee:</strong> UGX {{ number_format($applicationFee, 2) }}
        <br><small>This fee will be required before submission of your application.</small>
    </div>
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

<form action="{{ url('apply/start') }}" method="POST" class="d-grid gap-3">
    @csrf
    <button type="submit" class="btn btn-primary btn-lg">
        <i class='bx bx-rocket'></i> Start Application
    </button>
    
    <a href="{{ url('apply/status') }}" class="btn btn-outline-secondary">
        <i class='bx bx-search'></i> Check Application Status
    </a>
</form>

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
</style>
@endpush
