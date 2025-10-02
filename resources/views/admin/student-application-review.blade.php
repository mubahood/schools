<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Application Review</h3>
        <div class="box-tools pull-right">
            <a href="{{ admin_url('student-applications') }}" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    
    <div class="box-body">
        <!-- Status Badge -->
        <div class="alert alert-{{ $application->status == 'accepted' ? 'success' : ($application->status == 'rejected' ? 'danger' : 'info') }}">
            <h4>
                <i class="icon fa fa-{{ $application->status == 'accepted' ? 'check' : ($application->status == 'rejected' ? 'ban' : 'info-circle') }}"></i>
                Status: {{ ucwords(str_replace('_', ' ', $application->status)) }}
            </h4>
            <p><strong>Application Number:</strong> {{ $application->application_number }}</p>
            <p><strong>Progress:</strong> {{ $application->progress_percentage }}%</p>
        </div>

        <!-- Personal Information -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Personal Information</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Full Name:</strong><br>
                        {{ $application->full_name }}
                    </div>
                    <div class="col-md-4">
                        <strong>Date of Birth:</strong><br>
                        {{ $application->date_of_birth ? $application->date_of_birth->format('F d, Y') : 'N/A' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Gender:</strong><br>
                        {{ ucfirst($application->gender ?? 'N/A') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Nationality:</strong><br>
                        {{ $application->nationality ?? 'N/A' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Religion:</strong><br>
                        {{ $application->religion ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Contact Information</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        <a href="mailto:{{ $application->email }}">{{ $application->email }}</a>
                    </div>
                    <div class="col-md-6">
                        <strong>Phone Number:</strong><br>
                        {{ $application->phone_number ?? 'N/A' }}
                        @if($application->phone_number_2)
                            / {{ $application->phone_number_2 }}
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Home Address:</strong><br>
                        {{ $application->home_address ?? 'N/A' }}<br>
                        @if($application->district || $application->city || $application->village)
                            <small class="text-muted">
                                {{ collect([$application->village, $application->city, $application->district])->filter()->implode(', ') }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent/Guardian Information -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Parent/Guardian Information</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Parent/Guardian Name:</strong><br>
                        {{ $application->parent_name ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Relationship:</strong><br>
                        {{ ucfirst($application->parent_relationship ?? 'N/A') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Parent Phone:</strong><br>
                        {{ $application->parent_phone ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Parent Email:</strong><br>
                        @if($application->parent_email)
                            <a href="mailto:{{ $application->parent_email }}">{{ $application->parent_email }}</a>
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                @if($application->parent_address)
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Parent Address:</strong><br>
                        {{ $application->parent_address }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Previous School -->
        @if($application->previous_school)
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Previous School Information</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Previous School:</strong><br>
                        {{ $application->previous_school }}
                    </div>
                    <div class="col-md-4">
                        <strong>Previous Class:</strong><br>
                        {{ $application->previous_class ?? 'N/A' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Year Completed:</strong><br>
                        {{ $application->year_completed ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Application Details -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Application Details</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Applying For Class:</strong><br>
                        {{ $application->applying_for_class ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Special Needs:</strong><br>
                        {{ $application->special_needs ?? 'None' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Uploaded Documents -->
        @if(!empty($application->uploaded_documents))
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Uploaded Documents</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Document Name</th>
                            <th>File Size</th>
                            <th>Uploaded At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($application->uploaded_documents as $doc)
                        <tr>
                            <td>{{ $doc['document_name'] }}</td>
                            <td>{{ $doc['file_size_formatted'] }}</td>
                            <td>{{ $doc['uploaded_at'] }}</td>
                            <td>
                                <a href="{{ admin_url('student-applications/'.$application->id.'/document/'.$doc['document_id']) }}" 
                                   target="_blank" 
                                   class="btn btn-xs btn-primary">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Timeline -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Application Timeline</h3>
            </div>
            <div class="box-body">
                <ul class="timeline">
                    @if($application->started_at)
                    <li>
                        <i class="fa fa-play bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fa fa-clock-o"></i> {{ $application->started_at->format('Y-m-d H:i') }}</span>
                            <h3 class="timeline-header">Application Started</h3>
                        </div>
                    </li>
                    @endif

                    @if($application->submitted_at)
                    <li>
                        <i class="fa fa-send bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fa fa-clock-o"></i> {{ $application->submitted_at->format('Y-m-d H:i') }}</span>
                            <h3 class="timeline-header">Application Submitted</h3>
                        </div>
                    </li>
                    @endif

                    @if($application->reviewed_at)
                    <li>
                        <i class="fa fa-eye bg-yellow"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fa fa-clock-o"></i> {{ $application->reviewed_at->format('Y-m-d H:i') }}</span>
                            <h3 class="timeline-header">Application Reviewed</h3>
                            @if($application->reviewer)
                                <div class="timeline-body">
                                    Reviewed by: {{ $application->reviewer->name }}
                                </div>
                            @endif
                        </div>
                    </li>
                    @endif

                    @if($application->completed_at)
                    <li>
                        <i class="fa fa-check bg-{{ $application->status == 'accepted' ? 'green' : 'red' }}"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fa fa-clock-o"></i> {{ $application->completed_at->format('Y-m-d H:i') }}</span>
                            <h3 class="timeline-header">Application {{ ucfirst($application->status) }}</h3>
                            @if($application->rejection_reason)
                                <div class="timeline-body">
                                    <strong>Reason:</strong> {{ $application->rejection_reason }}
                                </div>
                            @endif
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Admin Notes -->
        @if($application->admin_notes)
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Admin Notes</h3>
            </div>
            <div class="box-body">
                <p>{{ $application->admin_notes }}</p>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        @if($application->canReview())
        <div class="box-footer">
            <form id="reviewForm">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Admin Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about this review..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-lg btn-block" onclick="acceptApplication()">
                            <i class="fa fa-check"></i> Accept Application
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-danger btn-lg btn-block" onclick="showRejectModal()">
                            <i class="fa fa-times"></i> Reject Application
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Reject Application</h4>
            </div>
            <form id="rejectForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required 
                                  placeholder="Please provide a clear reason for rejecting this application..."></textarea>
                        <p class="help-block">This reason will be visible to the applicant.</p>
                    </div>
                    <div class="form-group">
                        <label>Internal Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Add any internal notes (not visible to applicant)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="rejectApplication()">
                        <i class="fa fa-times"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function acceptApplication() {
    if (!confirm('Are you sure you want to accept this application? This will create a student account.')) {
        return;
    }
    
    const notes = document.querySelector('[name="notes"]').value;
    
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    
    fetch('{{ admin_url("student-applications/{$application->id}/accept") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => {
                window.location.href = '{{ admin_url("student-applications") }}';
            }, 1500);
        } else {
            toastr.error(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Accept Application';
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Accept Application';
    });
}

function showRejectModal() {
    $('#rejectModal').modal('show');
}

function rejectApplication() {
    const form = document.getElementById('rejectForm');
    const reason = form.querySelector('[name="reason"]').value.trim();
    
    if (!reason) {
        toastr.error('Please provide a reason for rejection.');
        return;
    }
    
    const notes = form.querySelector('[name="notes"]').value;
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    
    fetch('{{ admin_url("student-applications/{$application->id}/reject") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            reason: reason,
            notes: notes 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            $('#rejectModal').modal('hide');
            setTimeout(() => {
                window.location.href = '{{ admin_url("student-applications") }}';
            }, 1500);
        } else {
            toastr.error(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-times"></i> Confirm Rejection';
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-times"></i> Confirm Rejection';
    });
}
</script>
