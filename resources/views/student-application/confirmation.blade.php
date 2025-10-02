@extends('layouts.onboarding')

@section('title', 'Confirm Information - Student Application')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Review & Confirm</h2>
        <p class="progress-description">
            Review your information carefully before proceeding.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator completed">1</div>
        <div class="progress-step-indicator completed">2</div>
        <div class="progress-step-indicator active">3</div>
        <span>Confirmation</span>
    </div>
@endsection

@section('content')
<div class="content-title">Confirm Your Information</div>
<div class="content-description">
    Review all details before continuing to document upload.
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class='bx bx-error-circle me-2'></i>
    <strong>Error:</strong> {{ session('error') }}
    @if($errors->any())
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
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

<div class="alert alert-info d-flex align-items-start mb-4">
    <i class='bx bx-info-circle fs-4 me-2'></i>
    <span>Please review all information carefully before proceeding. You can go back to edit if needed.</span>
</div>


<!-- Personal Information -->
<div class="review-section mb-4">
    <h5 class="fw-semibold mb-3 pb-2 border-bottom">Student Information</h5>
    <div class="row g-3">
        <div class="col-md-6"><strong>Full Name:</strong> {{ $application->full_name }}</div>
        <div class="col-md-6"><strong>Date of Birth:</strong> 
            @if($application->date_of_birth)
                {{ is_string($application->date_of_birth) ? \Carbon\Carbon::parse($application->date_of_birth)->format('F d, Y') : $application->date_of_birth->format('F d, Y') }}
            @else
                N/A
            @endif
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-6"><strong>Gender:</strong> {{ ucfirst($application->gender ?? 'N/A') }}</div>
        <div class="col-md-6"><strong>Nationality:</strong> {{ $application->nationality ?? 'N/A' }}</div>
    </div>
    @if($application->religion || $application->special_needs)
    <div class="row g-3 mt-2">
        @if($application->religion)
        <div class="col-md-6"><strong>Religion:</strong> {{ $application->religion }}</div>
        @endif
        @if($application->special_needs)
        <div class="col-md-6"><strong>Special Needs:</strong> {{ $application->special_needs }}</div>
        @endif
    </div>
    @endif
</div>

<!-- Contact Information -->
<div class="review-section mb-4">
    <h5 class="fw-semibold mb-3 pb-2 border-bottom">Contact Information</h5>
    <div class="row g-3">
        <div class="col-md-6"><strong>Email:</strong> {{ $application->email }}</div>
        <div class="col-md-6"><strong>Phone:</strong> {{ $application->phone_number }}</div>
    </div>
    @if($application->phone_number_2)
    <div class="row g-3 mt-2">
        <div class="col-md-6"><strong>Alternative Phone:</strong> {{ $application->phone_number_2 }}</div>
    </div>
    @endif
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <strong>Address:</strong> {{ $application->home_address }}
            @if($application->village || $application->city || $application->district)
                <br><small class="text-muted">{{ collect([$application->village, $application->city, $application->district])->filter()->implode(', ') }}</small>
            @endif
        </div>
    </div>
</div>

<!-- Parent Information -->
<div class="review-section mb-4">
    <h5 class="fw-semibold mb-3 pb-2 border-bottom">Parent/Guardian Information</h5>
    <div class="row g-3">
        <div class="col-md-6"><strong>Name:</strong> {{ $application->parent_name }}</div>
        <div class="col-md-6"><strong>Relationship:</strong> {{ ucfirst($application->parent_relationship) }}</div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-6"><strong>Phone:</strong> {{ $application->parent_phone }}</div>
        @if($application->parent_email)
        <div class="col-md-6"><strong>Email:</strong> {{ $application->parent_email }}</div>
        @endif
    </div>
    @if($application->parent_address)
    <div class="row g-3 mt-2">
        <div class="col-md-12"><strong>Address:</strong> {{ $application->parent_address }}</div>
    </div>
    @endif
</div>

<!-- Previous School -->
@if($application->previous_school)
<div class="review-section mb-4">
    <h5 class="fw-semibold mb-3 pb-2 border-bottom">Previous School Information</h5>
    <div class="row g-3">
        <div class="col-md-6"><strong>School:</strong> {{ $application->previous_school }}</div>
        @if($application->previous_class)
        <div class="col-md-3"><strong>Class:</strong> {{ $application->previous_class }}</div>
        @endif
        @if($application->year_completed)
        <div class="col-md-3"><strong>Year:</strong> {{ $application->year_completed }}</div>
        @endif
    </div>
</div>
@endif

<!-- Application Details -->
<div class="review-section mb-4">
    <h5 class="fw-semibold mb-3 pb-2 border-bottom">Application Details</h5>
    <div class="row g-3">
        <div class="col-md-6"><strong>Applying For Class:</strong> {{ $application->applying_for_class }}</div>
        <div class="col-md-6"><strong>Selected School:</strong> {{ $school->name }}</div>
    </div>
</div>

<!-- Actions -->
<form action="{{ url('apply/confirmation') }}" method="POST">
    @csrf
    
    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input @error('confirm') is-invalid @enderror" type="checkbox" name="confirm" id="confirmCheckbox" value="1" {{ old('confirm') ? 'checked' : '' }} required>
            <label class="form-check-label" for="confirmCheckbox">
                <strong>I confirm that all the information provided above is correct and accurate.</strong>
            </label>
            @error('confirm')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="row g-3 mt-4">
        <div class="col-md-6">
            <a href="{{ url('apply/bio-data') }}" class="btn btn-outline-secondary btn-lg w-100">
                <i class='bx bx-edit'></i> Edit Information
            </a>
        </div>
        <div class="col-md-6">
            <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                Confirm & Continue <i class='bx bx-arrow-forward'></i>
            </button>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
    .review-section {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
    }
    
    .form-check {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px solid #dee2e6;
    }
    
    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.15rem;
    }
    
    .form-check-label {
        margin-left: 0.5rem;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('confirmCheckbox');
        const submitBtn = document.getElementById('submitBtn');
        
        // Initially disable button if checkbox is not checked
        if (checkbox && submitBtn) {
            submitBtn.disabled = !checkbox.checked;
            
            // Enable/disable button based on checkbox state
            checkbox.addEventListener('change', function() {
                submitBtn.disabled = !this.checked;
            });
        }
    });
</script>
@endpush
