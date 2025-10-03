@extends('layouts.onboarding')

@section('title', 'Check Application Status')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Check Your Status</h2>
        <p class="progress-description">
            Track your application progress in real-time.
        </p>
    </div>
@endsection

@section('content')
<div class="content-title">Check Application Status</div>
<div class="content-description">
    Enter your application number or email address to check your status.
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class='bx bx-error-circle me-2'></i>
    <strong>Error:</strong> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class='bx bx-check-circle me-2'></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ url('apply/status') }}" method="POST">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Application Number or Email Address</label>
        <input type="text" name="search" class="form-control form-control-lg" placeholder="Enter application number (e.g., APP-2024-001234) or email" required value="{{ old('search') }}">
    </div>
    
    <button type="submit" class="btn btn-primary btn-lg w-100">
        <i class='bx bx-search'></i> Check Status
    </button>
    </form>
    
    @if(isset($application))
    <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
        <h3 style="color: var(--school-primary); margin-bottom: 20px;">
            <i class="fa fa-file-text"></i> Application Details
        </h3>
        
        <div class="row">
            <div class="col-md-6">
                <p><strong>Application Number:</strong> {{ $application->application_number }}</p>
                <p><strong>Applicant Name:</strong> {{ $application->full_name }}</p>
                <p><strong>Email:</strong> {{ $application->email }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Applied School:</strong> {{ $application->selectedEnterprise->name ?? 'N/A' }}</p>
                <p><strong>Class:</strong> {{ $application->applying_for_class }}</p>
                <p><strong>Submitted On:</strong> {{ $application->submitted_at ? $application->submitted_at->format('F d, Y H:i') : 'Not submitted yet' }}</p>
            </div>
        </div>
        
        <div style="margin-top: 30px;">
            <h4>Application Status</h4>
            
            @php
                $statusColors = [
                    'draft' => 'default',
                    'submitted' => 'primary',
                    'under_review' => 'info',
                    'accepted' => 'success',
                    'rejected' => 'danger',
                    'cancelled' => 'warning'
                ];
                $statusColor = $statusColors[$application->status] ?? 'default';
                $statusLabel = ucwords(str_replace('_', ' ', $application->status));
            @endphp
            
            <div class="alert alert-{{ $statusColor }}" style="font-size: 18px; padding: 20px;">
                <i class="fa fa-{{ $application->status == 'accepted' ? 'check-circle' : ($application->status == 'rejected' ? 'times-circle' : 'info-circle') }}"></i>
                <strong>Status:</strong> {{ $statusLabel }}
            </div>
            
            @if($application->status == 'draft')
                <p>Your application is still in draft mode. Please complete and submit your application.</p>
                @if($application->session_token)
                    <a href="{{ url('apply/resume/'.$application->session_token) }}" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Resume Application
                    </a>
                @endif
            @elseif($application->status == 'submitted')
                <p>Your application has been submitted and is waiting to be reviewed by the admissions team.</p>
            @elseif($application->status == 'under_review')
                <p>Your application is currently being reviewed by the admissions team. You will receive an email notification once the review is complete.</p>
            @elseif($application->status == 'accepted')
                <div class="well" style="background: #d4edda; border: 2px solid #28a745;">
                    <h4 style="color: #155724; margin-top: 0;">🎉 Congratulations!</h4>
                    <p style="color: #155724;">Your application has been accepted! You will receive further instructions via email.</p>
                    @if($application->admin_notes)
                        <p style="color: #155724;"><strong>Note:</strong> {{ $application->admin_notes }}</p>
                    @endif
                    
                    <!-- Download Temporary Admission Letter Button -->
                    <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #c3e6cb;">
                        <a href="{{ url('apply/admission-letter/' . $application->application_number) }}" 
                           target="_blank"
                           class="btn btn-success btn-lg" 
                           style="background: #28a745; border: none; padding: 15px 30px; font-size: 18px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class='bx bxs-download' style="font-size: 20px; vertical-align: middle;"></i>
                            Download Temporary Admission Letter
                        </a>
                        <p style="margin-top: 15px; font-size: 13px; color: #155724;">
                            <i class='bx bx-info-circle'></i> 
                            <strong>Important:</strong> Please download and print this temporary admission letter. 
                            Bring it with you when visiting the school for official registration.
                        </p>
                    </div>
                </div>
            @elseif($application->status == 'rejected')
                <div class="well" style="background: #f8d7da; border: 2px solid #dc3545;">
                    <h4 style="color: #721c24; margin-top: 0;">Application Not Accepted</h4>
                    @if($application->rejection_reason)
                        <p style="color: #721c24;"><strong>Reason:</strong> {{ $application->rejection_reason }}</p>
                    @endif
                    <p style="color: #721c24;">If you have any questions, please contact the school directly.</p>
                </div>
            @endif
            
            <!-- Timeline -->
            <div style="margin-top: 30px;">
                <h4>Application Timeline</h4>
                <ul class="timeline-list">
                    @if($application->started_at)
                    <li>
                        <i class="fa fa-check-circle text-success"></i>
                        <strong>Started:</strong> {{ $application->started_at->format('F d, Y H:i') }}
                    </li>
                    @endif
                    
                    @if($application->submitted_at)
                    <li>
                        <i class="fa fa-check-circle text-success"></i>
                        <strong>Submitted:</strong> {{ $application->submitted_at->format('F d, Y H:i') }}
                    </li>
                    @endif
                    
                    @if($application->reviewed_at)
                    <li>
                        <i class="fa fa-check-circle text-success"></i>
                        <strong>Reviewed:</strong> {{ $application->reviewed_at->format('F d, Y H:i') }}
                    </li>
                    @endif
                    
                    @if($application->completed_at)
                    <li>
                        <i class="fa fa-check-circle text-{{ $application->status == 'accepted' ? 'success' : 'danger' }}"></i>
                        <strong>{{ $application->status == 'accepted' ? 'Accepted' : 'Completed' }}:</strong> {{ $application->completed_at->format('F d, Y H:i') }}
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="text-center" style="margin-top: 30px;">
    <a href="{{ url('apply') }}" class="btn btn-default">
        <i class="fa fa-arrow-left"></i> Back to Application Portal
    </a>
</div>
@endsection

@push('styles')
<style>
    .timeline-list {
        list-style: none;
        padding-left: 0;
    }
    
    .timeline-list li {
        padding: 10px 0;
        border-left: 3px solid #eee;
        padding-left: 20px;
        margin-left: 10px;
    }
    
    .timeline-list li i {
        margin-left: -30px;
        margin-right: 10px;
    }
    
    .well {
        padding: 20px;
        border-radius: 8px;
    }
    
    /* Download Button Hover Effect */
    .btn-success:hover {
        background: #218838 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .btn-success {
            width: 100%;
            font-size: 16px !important;
            padding: 12px 20px !important;
        }
    }
</style>
@endpush
