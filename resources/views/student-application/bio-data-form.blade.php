@extends('layouts.onboarding')

@section('title', 'Personal Information - Student Application')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Personal Information</h2>
        <p class="progress-description">
            Fill in your details carefully. Auto-save enabled.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator completed">1</div>
        <div class="progress-step-indicator active">2</div>
        <span>Bio Data</span>
    </div>
@endsection

@section('content')
<div class="content-title">Personal Information Form</div>
<div class="content-description">
    Applying to <strong>{{ $school->name ?? 'School' }}</strong>
    <span id="autoSaveStatus" class="ms-2 text-success small" style="display: none;"></span>
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

<form id="bioDataForm" action="{{ url('apply/bio-data') }}" method="POST">
    @csrf
    
    <!-- Personal Information Section -->
    <div class="form-section mb-4">
        <h5 class="fw-semibold mb-3 pb-2 border-bottom">Student Information</h5>
            
        <div class="row g-3">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label required-field">First Name</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $application->first_name) }}" required>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror" value="{{ old('middle_name', $application->middle_name) }}">
                    @error('middle_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label required-field">Last Name</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $application->last_name) }}" required>
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label required-field">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $application->date_of_birth ? (is_string($application->date_of_birth) ? $application->date_of_birth : $application->date_of_birth->format('Y-m-d')) : '') }}" required>
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label required-field">Gender</label>
                    <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', $application->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $application->gender) == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" class="form-control @error('nationality') is-invalid @enderror" value="{{ old('nationality', $application->nationality) }}">
                    @error('nationality')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Religion</label>
                    <input type="text" name="religion" class="form-control" value="{{ old('religion', $application->religion) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Special Needs (if any)</label>
                    <input type="text" name="special_needs" class="form-control" value="{{ old('special_needs', $application->special_needs) }}" placeholder="e.g., Allergies, disabilities, etc.">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contact Information Section -->
    <div class="form-section mb-4">
        <h5 class="fw-semibold mb-3 pb-2 border-bottom">Contact Information</h5>
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label required-field">Email Address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $application->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label required-field">Phone Number</label>
                    <input type="tel" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $application->phone_number) }}" required placeholder="+256...">
                    @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Alternative Phone Number</label>
                    <input type="tel" name="phone_number_2" class="form-control" value="{{ old('phone_number_2', $application->phone_number_2) }}" placeholder="+256...">
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label required-field">Home Address</label>
                    <textarea name="home_address" class="form-control @error('home_address') is-invalid @enderror" rows="2" required>{{ old('home_address', $application->home_address) }}</textarea>
                    @error('home_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Village</label>
                    <input type="text" name="village" class="form-control" value="{{ old('village', $application->village) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">City/Town</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $application->city) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control" value="{{ old('district', $application->district) }}">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Parent/Guardian Information Section -->
    <div class="form-section mb-4">
        <h5 class="fw-semibold mb-3 pb-2 border-bottom">Parent/Guardian Information</h5>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label required-field">Parent/Guardian Full Name</label>
                    <input type="text" name="parent_name" class="form-control @error('parent_name') is-invalid @enderror" value="{{ old('parent_name', $application->parent_name) }}" required>
                    @error('parent_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label required-field">Relationship</label>
                    <select name="parent_relationship" class="form-control @error('parent_relationship') is-invalid @enderror" required>
                        <option value="">Select Relationship</option>
                        <option value="father" {{ old('parent_relationship', $application->parent_relationship) == 'father' ? 'selected' : '' }}>Father</option>
                        <option value="mother" {{ old('parent_relationship', $application->parent_relationship) == 'mother' ? 'selected' : '' }}>Mother</option>
                        <option value="guardian" {{ old('parent_relationship', $application->parent_relationship) == 'guardian' ? 'selected' : '' }}>Guardian</option>
                        <option value="other" {{ old('parent_relationship', $application->parent_relationship) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('parent_relationship')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label required-field">Parent/Guardian Phone</label>
                    <input type="tel" name="parent_phone" class="form-control @error('parent_phone') is-invalid @enderror" value="{{ old('parent_phone', $application->parent_phone) }}" required placeholder="+256...">
                    @error('parent_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Parent/Guardian Email</label>
                    <input type="email" name="parent_email" class="form-control @error('parent_email') is-invalid @enderror" value="{{ old('parent_email', $application->parent_email) }}">
                    @error('parent_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Parent/Guardian Address</label>
                    <textarea name="parent_address" class="form-control" rows="2">{{ old('parent_address', $application->parent_address) }}</textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Previous School Information Section -->
    <div class="form-section mb-4">
        <h5 class="fw-semibold mb-3 pb-2 border-bottom">Previous School Information (Optional)</h5>
            
            
        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Previous School Name</label>
                    <input type="text" name="previous_school" class="form-control" value="{{ old('previous_school', $application->previous_school) }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Previous Class</label>
                    <input type="text" name="previous_class" class="form-control" value="{{ old('previous_class', $application->previous_class) }}" placeholder="e.g., P.7">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Year Completed</label>
                    <input type="number" name="year_completed" class="form-control" value="{{ old('year_completed', $application->year_completed) }}" placeholder="2024" min="1990" max="{{ date('Y') }}">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Class Application Section -->
    <div class="form-section mb-4">
        <h5 class="fw-semibold mb-3 pb-2 border-bottom">Class Application</h5>
        
        <div class="row g-3">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label required-field">Applying For Class</label>
                    <input type="text" name="applying_for_class" class="form-control @error('applying_for_class') is-invalid @enderror" value="{{ old('applying_for_class', $application->applying_for_class) }}" required placeholder="e.g., S.1, P.1">
                    @error('applying_for_class')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="row g-3 mt-4">
        <div class="col-md-6">
            <a href="{{ url('apply/school-selection') }}" class="btn btn-outline-secondary btn-lg w-100">
                <i class='bx bx-arrow-back'></i> Back
            </a>
        </div>
        <div class="col-md-6">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                Continue <i class='bx bx-arrow-forward'></i>
            </button>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
    .required-field::after {
        content: " *";
        color: #dc3545;
    }
    
    .form-section {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/student-application.js') }}"></script>
<script>
    // Auto-save functionality
    let autoSaveTimer;
    
    document.querySelectorAll('#bioDataForm input, #bioDataForm select, #bioDataForm textarea').forEach(element => {
        element.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                autoSaveForm();
            }, 2000); // Save 2 seconds after user stops typing
        });
        
        element.addEventListener('change', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                autoSaveForm();
            }, 2000);
        });
    });
    
    function autoSaveForm() {
        const formData = new FormData(document.getElementById('bioDataForm'));
        const statusEl = document.getElementById('autoSaveStatus');
        
        statusEl.classList.remove('saved');
        statusEl.classList.add('saving');
        statusEl.style.display = 'inline';
        statusEl.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
        
        fetch('{{ url("apply/session/save") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            statusEl.classList.remove('saving');
            statusEl.classList.add('saved');
            statusEl.innerHTML = '<i class="bx bx-check-circle"></i> Saved';
            
            setTimeout(function() {
                statusEl.style.display = 'none';
            }, 2000);
        })
        .catch(error => {
            statusEl.style.display = 'none';
        });
    }
</script>
@endpush
