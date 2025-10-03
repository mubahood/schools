<style>
    .application-detail-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .application-detail-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .application-detail-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        color: white;
    }
    
    .application-detail-header .app-number {
        font-size: 18px;
        opacity: 0.95;
        margin-top: 5px;
    }
    
    .status-badge-large {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge-large.submitted {
        background: #0d6efd;
        color: white;
    }
    
    .status-badge-large.under_review {
        background: #0dcaf0;
        color: white;
    }
    
    .status-badge-large.accepted {
        background: #198754;
        color: white;
    }
    
    .status-badge-large.rejected {
        background: #dc3545;
        color: white;
    }
    
    .status-badge-large.draft {
        background: #6c757d;
        color: white;
    }
    
    .info-section {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .info-section-title {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .info-section-title i {
        font-size: 24px;
        margin-right: 12px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    
    .info-section-title h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .info-section.personal .info-section-title i {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .info-section.contact .info-section-title i {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .info-section.parent .info-section-title i {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .info-section.education .info-section-title i {
        background: #f3e5f5;
        color: #7b1fa2;
    }
    
    .info-section.application .info-section-title i {
        background: #fce4ec;
        color: #c2185b;
    }
    
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #6c757d;
        width: 200px;
        flex-shrink: 0;
    }
    
    .info-value {
        flex: 1;
        color: #2c3e50;
    }
    
    .attachments-table {
        width: 100%;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .attachments-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .attachments-table thead th {
        padding: 15px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }
    
    .attachments-table tbody td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .attachments-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .file-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-right: 12px;
    }
    
    .file-icon.pdf {
        background: #ffebee;
        color: #dc3545;
    }
    
    .file-icon.image {
        background: #e8f5e9;
        color: #198754;
    }
    
    .file-icon.doc {
        background: #e3f2fd;
        color: #0d6efd;
    }
    
    .file-icon.default {
        background: #f5f5f5;
        color: #6c757d;
    }
    
    .btn-view-file {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-view-file:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .timeline {
        position: relative;
        padding-left: 50px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -38px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .timeline-item .date {
        font-weight: 600;
        color: #667eea;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .timeline-item .event {
        color: #2c3e50;
        font-size: 15px;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .action-buttons .btn {
        padding: 12px 24px;
        font-weight: 600;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .no-data {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        font-style: italic;
    }
    
    @media (max-width: 768px) {
        .application-detail-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .info-row {
            flex-direction: column;
        }
        
        .info-label {
            width: 100%;
            margin-bottom: 5px;
        }
    }
</style>

<div class="application-detail-card">
    <div class="application-detail-header">
        <div>
            <h2>
                <i class="fa fa-graduation-cap"></i>
                {{ $application->first_name }} {{ $application->last_name }}
            </h2>
            <div class="app-number">
                <i class="fa fa-barcode"></i> {{ $application->application_number }}
            </div>
        </div>
        <div>
            <span class="status-badge-large {{ $application->status }}">
                @if($application->status === 'submitted')
                    <i class="fa fa-paper-plane"></i> Submitted
                @elseif($application->status === 'under_review')
                    <i class="fa fa-clock-o"></i> Under Review
                @elseif($application->status === 'accepted')
                    <i class="fa fa-check-circle"></i> Accepted
                @elseif($application->status === 'rejected')
                    <i class="fa fa-times-circle"></i> Rejected
                @else
                    <i class="fa fa-file-o"></i> {{ ucfirst($application->status) }}
                @endif
            </span>
        </div>
    </div>
    
    <div style="padding: 30px;">
        <!-- Personal Information -->
        <div class="info-section personal">
            <div class="info-section-title">
                <i class="fa fa-user"></i>
                <h4>Personal Information</h4>
            </div>
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value">{{ $application->first_name }} {{ $application->middle_name }} {{ $application->last_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date of Birth:</div>
                <div class="info-value">
                    {{ $application->date_of_birth ? \Carbon\Carbon::parse($application->date_of_birth)->format('F d, Y') : '-' }}
                    @if($application->date_of_birth)
                        <span class="badge badge-info">{{ \Carbon\Carbon::parse($application->date_of_birth)->age }} years old</span>
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Gender:</div>
                <div class="info-value">
                    <i class="fa fa-{{ strtolower($application->gender ?? '') === 'male' ? 'male' : 'female' }}" 
                       style="color: {{ strtolower($application->gender ?? '') === 'male' ? '#0d6efd' : '#d63384' }};"></i>
                    {{ ucfirst($application->gender ?? '-') }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Nationality:</div>
                <div class="info-value">{{ $application->nationality ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Religion:</div>
                <div class="info-value">{{ $application->religion ?? '-' }}</div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="info-section contact">
            <div class="info-section-title">
                <i class="fa fa-phone"></i>
                <h4>Contact Information</h4>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">
                    @if($application->email)
                        <a href="mailto:{{ $application->email }}">
                            <i class="fa fa-envelope"></i> {{ $application->email }}
                        </a>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone Number:</div>
                <div class="info-value">
                    @if($application->phone_number)
                        <i class="fa fa-phone"></i> {{ $application->phone_number }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Alternative Phone:</div>
                <div class="info-value">{{ $application->phone_number_2 ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Home Address:</div>
                <div class="info-value">{{ $application->home_address ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Location:</div>
                <div class="info-value">
                    {{ collect([$application->village, $application->city, $application->district])->filter()->implode(', ') ?: '-' }}
                </div>
            </div>
        </div>
        
        <!-- Parent/Guardian Information -->
        <div class="info-section parent">
            <div class="info-section-title">
                <i class="fa fa-users"></i>
                <h4>Parent/Guardian Information</h4>
            </div>
            <div class="info-row">
                <div class="info-label">Parent/Guardian Name:</div>
                <div class="info-value">{{ $application->parent_name ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Relationship:</div>
                <div class="info-value">{{ $application->parent_relationship ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Parent Phone:</div>
                <div class="info-value">
                    @if($application->parent_phone)
                        <i class="fa fa-phone"></i> {{ $application->parent_phone }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Parent Email:</div>
                <div class="info-value">
                    @if($application->parent_email)
                        <a href="mailto:{{ $application->parent_email }}">
                            <i class="fa fa-envelope"></i> {{ $application->parent_email }}
                        </a>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Parent Address:</div>
                <div class="info-value">{{ $application->parent_address ?? '-' }}</div>
            </div>
        </div>
        
        <!-- Previous Education -->
        <div class="info-section education">
            <div class="info-section-title">
                <i class="fa fa-graduation-cap"></i>
                <h4>Previous Education</h4>
            </div>
            <div class="info-row">
                <div class="info-label">Previous School:</div>
                <div class="info-value">{{ $application->previous_school ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Previous Class:</div>
                <div class="info-value">{{ $application->previous_class ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Year Completed:</div>
                <div class="info-value">{{ $application->year_completed ?? '-' }}</div>
            </div>
        </div>
        
        <!-- Application Details -->
        <div class="info-section application">
            <div class="info-section-title">
                <i class="fa fa-file-text"></i>
                <h4>Application Details</h4>
            </div>
            <div class="info-row">
                <div class="info-label">Applying For Class:</div>
                <div class="info-value">
                    <span class="badge badge-primary" style="font-size: 14px; padding: 8px 12px;">
                        {{ $application->applying_for_class ?? '-' }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Selected School:</div>
                <div class="info-value">{{ $application->selectedEnterprise->name ?? '-' }}</div>
            </div>
            @if($application->special_needs)
            <div class="info-row">
                <div class="info-label">Special Needs:</div>
                <div class="info-value">
                    <span class="badge badge-warning">
                        <i class="fa fa-exclamation-triangle"></i> {{ $application->special_needs }}
                    </span>
                </div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Progress:</div>
                <div class="info-value">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped {{ $application->progress_percentage >= 100 ? 'bg-success' : 'bg-info' }}" 
                             role="progressbar" 
                             style="width: {{ $application->progress_percentage }}%"
                             aria-valuenow="{{ $application->progress_percentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $application->progress_percentage }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Supporting Documents/Attachments -->
        <div class="info-section">
            <div class="info-section-title">
                <i class="fa fa-paperclip" style="background: #e1f5fe; color: #0277bd;"></i>
                <h4>Supporting Documents</h4>
            </div>
            
            @if($application->attachments && count($application->attachments) > 0)
                <table class="attachments-table">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Document Name</th>
                            <th width="120">File Size</th>
                            <th width="180">Upload Date</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($application->attachments as $index => $doc)
                            @php
                                $type = $doc['type'] ?? '';
                                $iconClass = 'default';
                                $iconName = 'file-o';
                                
                                if (str_contains($type, 'pdf')) {
                                    $iconClass = 'pdf';
                                    $iconName = 'file-pdf-o';
                                } elseif (str_contains($type, 'image')) {
                                    $iconClass = 'image';
                                    $iconName = 'file-image-o';
                                } elseif (str_contains($type, 'word') || str_contains($type, 'doc')) {
                                    $iconClass = 'doc';
                                    $iconName = 'file-word-o';
                                }
                                
                                $fileUrl = isset($doc['path']) ? asset('storage/' . $doc['path']) : '#';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div class="file-icon {{ $iconClass }}">
                                            <i class="fa fa-{{ $iconName }} fa-lg"></i>
                                        </div>
                                        <strong>{{ $doc['name'] ?? 'Unknown Document' }}</strong>
                                    </div>
                                </td>
                                <td>{{ number_format(($doc['size'] ?? 0) / 1024, 2) }} KB</td>
                                <td>
                                    @if(isset($doc['uploaded_at']))
                                        {{ \Carbon\Carbon::parse($doc['uploaded_at'])->format('M d, Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ $fileUrl }}" 
                                       target="_blank" 
                                       class="btn-view-file">
                                        <i class="fa fa-external-link"></i>
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">
                    <i class="fa fa-folder-open fa-3x" style="color: #dee2e6;"></i>
                    <p style="margin-top: 15px;">No documents attached to this application</p>
                </div>
            @endif
        </div>
        
        <!-- Application Timeline -->
        <div class="info-section">
            <div class="info-section-title">
                <i class="fa fa-clock-o" style="background: #fff9c4; color: #f57f17;"></i>
                <h4>Application Timeline</h4>
            </div>
            
            <div class="timeline">
                @if($application->created_at)
                <div class="timeline-item">
                    <div class="date">{{ \Carbon\Carbon::parse($application->created_at)->format('M d, Y H:i') }}</div>
                    <div class="event">
                        <i class="fa fa-plus-circle"></i> Application started
                    </div>
                </div>
                @endif
                
                @if($application->submitted_at)
                <div class="timeline-item">
                    <div class="date">{{ \Carbon\Carbon::parse($application->submitted_at)->format('M d, Y H:i') }}</div>
                    <div class="event">
                        <i class="fa fa-paper-plane"></i> Application submitted
                    </div>
                </div>
                @endif
                
                @if($application->reviewed_at)
                <div class="timeline-item">
                    <div class="date">{{ \Carbon\Carbon::parse($application->reviewed_at)->format('M d, Y H:i') }}</div>
                    <div class="event">
                        <i class="fa fa-eye"></i> Application reviewed
                        @if($application->reviewer)
                            by <strong>{{ $application->reviewer->name }}</strong>
                        @endif
                    </div>
                </div>
                @endif
                
                @if($application->completed_at)
                <div class="timeline-item">
                    <div class="date">{{ \Carbon\Carbon::parse($application->completed_at)->format('M d, Y H:i') }}</div>
                    <div class="event">
                        <i class="fa fa-check-circle"></i> Application completed
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Admin Review Section (if reviewed) -->
        @if($application->reviewed_by)
        <div class="info-section">
            <div class="info-section-title">
                <i class="fa fa-user-md" style="background: #e8eaf6; color: #3f51b5;"></i>
                <h4>Administrative Review</h4>
            </div>
            <div class="info-row">
                <div class="info-label">Reviewed By:</div>
                <div class="info-value">{{ $application->reviewer->name ?? '-' }}</div>
            </div>
            @if($application->admin_notes)
            <div class="info-row">
                <div class="info-label">Admin Notes:</div>
                <div class="info-value">{{ $application->admin_notes }}</div>
            </div>
            @endif
            @if($application->rejection_reason)
            <div class="info-row">
                <div class="info-label">Rejection Reason:</div>
                <div class="info-value">
                    <span class="badge badge-danger" style="font-size: 14px; padding: 8px 12px;">
                        {{ $application->rejection_reason }}
                    </span>
                </div>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ admin_url('student-applications') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            
            @if(in_array($application->status, ['submitted', 'under_review']))
                <button type="button" class="btn btn-success accept-application-btn" data-id="{{ $application->id }}">
                    <i class="fa fa-check-circle"></i> Accept Application
                </button>
                
                <button type="button" class="btn btn-danger reject-application-btn" data-id="{{ $application->id }}">
                    <i class="fa fa-times-circle"></i> Reject Application
                </button>
            @endif
            
            <a href="{{ admin_url('student-applications/' . $application->id . '/edit') }}" class="btn btn-primary">
                <i class="fa fa-edit"></i> Update Application
            </a>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Accept application
    $('.accept-application-btn').on('click', function() {
        var id = $(this).data('id');
        
        swal({
            title: "Accept Application",
            text: "Enter acceptance notes (optional):",
            content: {
                element: "input",
                attributes: {
                    placeholder: "Notes about acceptance...",
                    type: "text",
                },
            },
            buttons: {
                cancel: "Cancel",
                confirm: {
                    text: "Accept Application",
                    closeModal: false,
                }
            },
        }).then(function(notes) {
            if (notes === null) return;
            
            $.ajax({
                url: '{{ admin_url("student-applications") }}/' + id + '/accept',
                type: 'POST',
                data: {
                    notes: notes,
                    _token: LA.token
                },
                success: function(response) {
                    swal({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    var message = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    swal("Error!", message, "error");
                }
            });
        });
    });
    
    // Reject application
    $('.reject-application-btn').on('click', function() {
        var id = $(this).data('id');
        
        swal({
            title: "Reject Application",
            text: "Enter rejection reason (required):",
            content: {
                element: "input",
                attributes: {
                    placeholder: "Reason for rejection...",
                    type: "text",
                },
            },
            buttons: {
                cancel: "Cancel",
                confirm: {
                    text: "Reject Application",
                    closeModal: false,
                }
            },
        }).then(function(reason) {
            if (reason === null) return;
            
            if (!reason || reason.trim().length < 10) {
                swal("Error!", "Please provide a detailed reason (at least 10 characters)", "error");
                return;
            }
            
            $.ajax({
                url: '{{ admin_url("student-applications") }}/' + id + '/reject',
                type: 'POST',
                data: {
                    reason: reason,
                    _token: LA.token
                },
                success: function(response) {
                    swal({
                        title: "Rejected",
                        text: response.message,
                        icon: "success",
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    var message = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    swal("Error!", message, "error");
                }
            });
        });
    });
});
</script>
