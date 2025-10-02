@extends('layouts.onboarding')

@section('title', 'Upload Documents - Student Application')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Upload Documents</h2>
        <p class="progress-description">
            Upload required documents (PDF, JPG, PNG - Max 5MB).
        </p>
    </div>
    
    <div class="progress-indicator">
        <div class="progress-step-indicator completed">1</div>
        <div class="progress-step-indicator completed">2</div>
        <div class="progress-step-indicator completed">3</div>
        <div class="progress-step-indicator active">4</div>
        <span>Documents</span>
    </div>
@endsection

@section('content')
<div class="content-title">Upload Required Documents</div>
<div class="content-description">
    Please upload all required documents to proceed.
</div>

<div class="alert alert-info d-flex align-items-start mb-4">
    <i class='bx bx-info-circle fs-4 me-2'></i>
    <span>Accepted formats: PDF, JPG, JPEG, PNG (Max size: 5MB per file)</span>
</div>
    
    @if(!empty($requiredDocuments))
    <div class="form-section">
        <div class="form-section-title">Required Documents</div>
        
        @foreach($requiredDocuments as $index => $doc)
        <div class="document-item" data-document-name="{{ $doc['name'] }}">
            <div class="document-header">
                <h4>
                    {{ $doc['name'] }}
                    @if($doc['required'])
                        <span class="label label-danger">Required</span>
                    @else
                        <span class="label label-default">Optional</span>
                    @endif
                </h4>
            </div>
            
            <div class="document-body">
                @php
                    $uploaded = collect($uploadedDocuments)->firstWhere('document_name', $doc['name']);
                @endphp
                
                @if($uploaded)
                    <!-- File uploaded -->
                    <div class="uploaded-file">
                        <div class="row">
                            <div class="col-md-8">
                                <i class="fa fa-file-pdf-o" style="font-size: 24px; color: #d9534f;"></i>
                                <span style="margin-left: 10px;">
                                    <strong>{{ $uploaded['original_name'] }}</strong><br>
                                    <small class="text-muted">{{ $uploaded['file_size_formatted'] }} - Uploaded {{ $uploaded['uploaded_at'] }}</small>
                                </span>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteDocument('{{ $uploaded['document_id'] }}', this)">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Upload form -->
                    <form class="upload-form" data-document-name="{{ $doc['name'] }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" name="document" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
                                <input type="hidden" name="document_name" value="{{ $doc['name'] }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-upload"></i> Upload
                                </button>
                            </div>
                        </div>
                        <div class="progress" style="display: none; margin-top: 10px;">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="upload-message" style="margin-top: 10px;"></div>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i> No documents are required for this application.
    </div>
    @endif
    
    <!-- Summary -->
    <div class="alert alert-{{ $allRequiredUploaded ? 'success' : 'warning' }}" style="margin-top: 30px;">
        @if($allRequiredUploaded)
            <i class="fa fa-check-circle"></i> All required documents have been uploaded. You can now submit your application.
        @else
            <i class="fa fa-exclamation-triangle"></i> Please upload all required documents before submitting your application.
        @endif
    </div>
    
    <!-- Actions -->
    <div class="row" style="margin-top: 30px;">
        <div class="col-md-6">
            <a href="{{ url('apply/confirmation') }}" class="btn btn-default btn-lg btn-block">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="col-md-6">
            @if($allRequiredUploaded)
                <button type="button" onclick="submitApplication()" class="btn btn-success btn-lg btn-block">
                    <i class="fa fa-check"></i> Submit Application
                </button>
            @else
                <button type="button" class="btn btn-primary btn-lg btn-block" disabled>
                    <i class="fa fa-lock"></i> Complete Required Documents First
                </button>
            @endif
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Submit Application</h4>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to submit your application?</strong></p>
                <p class="text-muted">Once submitted, you will not be able to make changes. Please ensure all information is correct.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmSubmit()">
                    <i class="fa fa-check"></i> Yes, Submit Application
                </button>
            </div>
        </div>
    </div>
</div>

<form id="submitForm" action="{{ url('apply/documents') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" value="submit">
</form>
@endsection

@push('styles')
<style>
    .document-item {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .document-header h4 {
        margin: 0;
        color: var(--school-primary);
        font-size: 16px;
    }
    
    .document-body {
        margin-top: 15px;
    }
    
    .uploaded-file {
        background: white;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .upload-message {
        font-size: 13px;
    }
    
    .upload-message.success {
        color: #00a65a;
    }
    
    .upload-message.error {
        color: #dd4b39;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/student-application.js') }}"></script>
<script>
    // Handle file uploads
    $('.upload-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const fileInput = form.find('input[type="file"]')[0];
        const progressBar = form.find('.progress');
        const progressBarInner = form.find('.progress-bar');
        const messageDiv = form.find('.upload-message');
        const submitBtn = form.find('button[type="submit"]');
        
        if (!fileInput.files[0]) {
            messageDiv.removeClass('success').addClass('error').text('Please select a file');
            return;
        }
        
        const formData = new FormData(this);
        
        submitBtn.prop('disabled', true);
        progressBar.show();
        progressBarInner.css('width', '0%');
        messageDiv.text('');
        
        $.ajax({
            url: '{{ url("apply/documents/upload") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBarInner.css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    messageDiv.removeClass('error').addClass('success').text('File uploaded successfully!');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    messageDiv.removeClass('success').addClass('error').text(response.message || 'Upload failed');
                    submitBtn.prop('disabled', false);
                    progressBar.hide();
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Upload failed. Please try again.';
                messageDiv.removeClass('success').addClass('error').text(message);
                submitBtn.prop('disabled', false);
                progressBar.hide();
            }
        });
    });
    
    // Delete document
    function deleteDocument(documentId, btn) {
        if (!confirm('Are you sure you want to delete this document?')) {
            return;
        }
        
        $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
        
        $.ajax({
            url: '{{ url("apply/documents") }}/' + documentId,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to delete document');
                    $(btn).prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
                }
            },
            error: function() {
                alert('Failed to delete document. Please try again.');
                $(btn).prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
            }
        });
    }
    
    // Submit application
    function submitApplication() {
        $('#submitModal').modal('show');
    }
    
    function confirmSubmit() {
        $('#submitForm').submit();
    }
</script>
@endpush
