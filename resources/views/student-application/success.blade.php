@extends('layouts.onboarding')

@section('title', 'Application Submitted - Student Application')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Congratulations!</h2>
        <p class="progress-description">
            Your application has been successfully submitted.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator completed">1</div>
        <div class="progress-step-indicator completed">2</div>
        <div class="progress-step-indicator completed">3</div>
        <div class="progress-step-indicator completed">4</div>
        <div class="progress-step-indicator active">5</div>
        <span>Completed</span>
    </div>
@endsection

@section('content')
<div class="text-center">
    <div class="mb-4" style="font-size: 80px; color: #198754;">
        <i class='bx bx-check-circle'></i>
    </div>
    
    <div class="content-title">Application Submitted Successfully!</div>
    
    <p class="lead mb-4">Thank you for submitting your application to <strong>{{ $school->name }}</strong>.</p>
    
    <div style="background: var(--background-light); padding: 2rem; border-radius: 8px; margin-bottom: 2rem; border: 2px solid var(--primary-color);">
        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Your Application Number</h3>
        <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color); font-family: monospace; margin-bottom: 0.5rem;">
            {{ $application->application_number }}
        </div>
        <p style="color: var(--text-light); margin: 0;">Please save this number for future reference</p>
    </div>
</div>

<div class="application-card">
    <div class="card-header-custom">
        <h3><i class="fa fa-info-circle"></i> What Happens Next?</h3>
    </div>
    
    <div class="row">
        <div class="col-md-4 text-center" style="margin-bottom: 20px;">
            <div style="font-size: 48px; color: var(--school-primary); margin-bottom: 15px;">
                <i class="fa fa-search"></i>
            </div>
            <h4>Review</h4>
            <p class="text-muted">Our admissions team will carefully review your application and all submitted documents.</p>
        </div>
        
        <div class="col-md-4 text-center" style="margin-bottom: 20px;">
            <div style="font-size: 48px; color: var(--school-primary); margin-bottom: 15px;">
                <i class="fa fa-envelope"></i>
            </div>
            <h4>Notification</h4>
            <p class="text-muted">You will receive an email notification at <strong>{{ $application->email }}</strong> once your application has been reviewed.</p>
        </div>
        
        <div class="col-md-4 text-center" style="margin-bottom: 20px;">
            <div style="font-size: 48px; color: var(--school-primary); margin-bottom: 15px;">
                <i class="fa fa-graduation-cap"></i>
            </div>
            <h4>Next Steps</h4>
            <p class="text-muted">If accepted, you will receive further instructions on enrollment procedures.</p>
        </div>
    </div>
</div>

<div class="application-card">
    <div class="card-header-custom">
        <h3><i class="fa fa-question-circle"></i> Need to Track Your Application?</h3>
    </div>
    
    <p>You can check the status of your application anytime using your application number.</p>
    
    <div class="text-center" style="margin-top: 20px;">
        <a href="{{ url('apply/status') }}" class="btn btn-primary btn-lg">
            <i class="fa fa-search"></i> Check Application Status
        </a>
    </div>
</div>

<div class="application-card">
    <div class="card-header-custom">
        <h3><i class="fa fa-phone"></i> Contact Information</h3>
    </div>
    
    <p>If you have any questions about your application, please contact us:</p>
    
    <div class="row">
        @if($school->phone_number)
        <div class="col-md-6">
            <p><i class="fa fa-phone"></i> <strong>Phone:</strong> {{ $school->phone_number }}</p>
        </div>
        @endif
        @if($school->email)
        <div class="col-md-6">
            <p><i class="fa fa-envelope"></i> <strong>Email:</strong> <a href="mailto:{{ $school->email }}">{{ $school->email }}</a></p>
        </div>
        @endif
    </div>
    @if($school->address)
    <p><i class="fa fa-map-marker"></i> <strong>Address:</strong> {{ $school->address }}</p>
    @endif
</div>

<div class="text-center" style="margin-top: 30px;">
    <a href="{{ url('/') }}" class="btn btn-default">
        <i class="fa fa-home"></i> Return to Homepage
    </a>
</div>
@endsection

@push('styles')
<style>
    .well {
        padding: 30px;
        border-radius: 8px;
    }
    
    .lead {
        font-size: 18px;
        line-height: 1.6;
        color: #666;
    }
</style>
@endpush
