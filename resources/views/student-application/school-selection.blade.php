@extends('layouts.onboarding')

@section('title', 'Select School - Student Application')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Choose Your School</h2>
        <p class="progress-description">
            Select the school you wish to apply to.
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator active">1</div>
        <span>Select School</span>
    </div>
@endsection

@section('content')
<div class="content-title">Available Schools</div>
<div class="content-description">
    Select a school to continue your application.
</div>

<div class="schools-list" id="schoolsGrid">
    @forelse($schools as $school)
    <div class="school-item" 
         data-school-id="{{ $school->id }}"
         data-school-name="{{ $school->name }}"
         onclick="selectSchool(this)">
        <div class="school-check">
            <div class="check-icon">
                <i class='bx bx-check'></i>
            </div>
        </div>
        
        <div class="school-logo">
            @if($school->logo)
                <img src="{{ asset('storage/'.$school->logo) }}" alt="{{ $school->name }}">
            @else
                <div class="logo-placeholder" style="background-color: {{ $school->color ?? '#3c8dbc' }};">
                    {{ strtoupper(substr($school->name, 0, 2)) }}
                </div>
            @endif
        </div>
        
        <div class="school-info">
            <h5 class="school-name">{{ $school->name }}</h5>
            @if($school->motto)
                <p class="school-motto">{{ $school->motto }}</p>
            @endif
            
            <div class="school-details">
                @if($school->address)
                    <span class="detail-item">
                        <i class='bx bx-map'></i> {{ Str::limit($school->address, 40) }}
                    </span>
                @endif
                @if($school->phone_number)
                    <span class="detail-item">
                        <i class='bx bx-phone'></i> {{ $school->phone_number }}
                    </span>
                @endif
            </div>
        </div>
        
        <div class="school-action">
            <i class='bx bx-chevron-right'></i>
        </div>
    </div>
    @empty
    <div class="alert alert-warning">
        <i class='bx bx-error-circle'></i> No schools are currently accepting applications.
        Please check back later or contact the school administrator.
    </div>
    @endforelse
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmSchoolModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm School Selection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You have selected:</p>
                <h4 id="selectedSchoolName" class="text-primary fw-bold"></h4>
                <p class="text-muted">You will be applying to this school. Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmSchoolSelection()">
                    <i class='bx bx-check'></i> Yes, Continue
                </button>
            </div>
        </div>
    </div>
</div>

<form id="schoolSelectionForm" action="{{ url('apply/school-selection/confirm') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="enterprise_id" id="selectedSchoolId">
</form>
@endsection

@push('styles')
<style>
    .schools-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .school-item {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .school-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateX(4px);
    }
    
    .school-item.selected {
        border-color: var(--primary-color);
        background: linear-gradient(to right, rgba(var(--primary-color-rgb, 0, 123, 255), 0.05), white);
        box-shadow: 0 4px 12px rgba(var(--primary-color-rgb, 0, 123, 255), 0.15);
    }
    
    .school-item.selected .school-check .check-icon {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        transform: scale(1);
    }
    
    .school-check {
        flex-shrink: 0;
    }
    
    .check-icon {
        width: 24px;
        height: 24px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: transparent;
        transition: all 0.2s ease;
        background: white;
        transform: scale(0.8);
    }
    
    .school-logo {
        flex-shrink: 0;
        width: 60px;
        height: 60px;
    }
    
    .school-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
        background: #f9fafb;
        padding: 0.5rem;
    }
    
    .logo-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.25rem;
    }
    
    .school-info {
        flex: 1;
        min-width: 0;
    }
    
    .school-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 0.25rem 0;
    }
    
    .school-motto {
        font-size: 0.813rem;
        color: #6b7280;
        font-style: italic;
        margin: 0 0 0.5rem 0;
    }
    
    .school-details {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        font-size: 0.813rem;
        color: #6b7280;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .detail-item i {
        font-size: 1rem;
        color: #9ca3af;
    }
    
    .school-action {
        flex-shrink: 0;
        color: #9ca3af;
        font-size: 1.5rem;
        transition: all 0.2s ease;
    }
    
    .school-item:hover .school-action {
        color: var(--primary-color);
        transform: translateX(4px);
    }
    
    .school-item.selected .school-action {
        color: var(--primary-color);
    }
    
    /* Mobile responsive */
    @media (max-width: 640px) {
        .school-item {
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .school-info {
            width: 100%;
            order: 3;
        }
        
        .school-details {
            flex-direction: column;
            gap: 0.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/student-application.js') }}"></script>
<script>
    let selectedSchoolId = null;
    let selectedSchoolName = null;
    let confirmModal = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        confirmModal = new bootstrap.Modal(document.getElementById('confirmSchoolModal'));
    });
    
    function selectSchool(item) {
        selectedSchoolId = item.getAttribute('data-school-id');
        selectedSchoolName = item.getAttribute('data-school-name');
        
        // Remove selected class from all items
        document.querySelectorAll('.school-item').forEach(schoolItem => {
            schoolItem.classList.remove('selected');
        });
        
        // Add selected class to clicked item
        item.classList.add('selected');
        
        // Update modal
        document.getElementById('selectedSchoolName').textContent = selectedSchoolName;
        
        // Show confirmation modal
        confirmModal.show();
    }
    
    function confirmSchoolSelection() {
        if (!selectedSchoolId) {
            alert('Please select a school first.');
            return;
        }
        
        // Disable confirm button and show loading
        const confirmBtn = event.target;
        const originalText = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        
        // Save school selection via AJAX first
        fetch('{{ url("apply/school-selection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                enterprise_id: selectedSchoolId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set form value and submit to confirm route
                document.getElementById('selectedSchoolId').value = selectedSchoolId;
                document.getElementById('schoolSelectionForm').submit();
            } else {
                alert(data.message || 'Failed to save school selection.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        });
    }
</script>
@endpush
