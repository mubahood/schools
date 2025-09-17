@extends('admin::layouts.content')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Support Message #{{ $message->id }}</h3>
                <div class="box-tools pull-right">
                    <span class="label label-{{ $message->status_color }}">{{ ucfirst($message->status) }}</span>
                </div>
            </div>
            
            <div class="box-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="150">Name:</th>
                        <td>{{ $message->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $message->email }}</td>
                    </tr>
                    <tr>
                        <th>Subject:</th>
                        <td>{{ $message->subject }}</td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td>{{ $message->formatted_date }}</td>
                    </tr>
                    <tr>
                        <th>IP Address:</th>
                        <td>{{ $message->ip_address }}</td>
                    </tr>
                </table>
                
                <div style="margin-top: 20px;">
                    <h4>Message:</h4>
                    <div class="well">
                        {{ $message->message }}
                    </div>
                </div>
                
                @if($message->admin_reply)
                    <div style="margin-top: 20px;">
                        <h4>Reply Sent:</h4>
                        <div class="alert alert-info">
                            {{ $message->admin_reply }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Actions</h3>
            </div>
            
            <div class="box-body">
                @if($message->status !== 'replied')
                    <form action="{{ route('admin.support.reply', $message->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Send Reply:</label>
                            <textarea name="reply" 
                                      class="form-control" 
                                      rows="8" 
                                      placeholder="Enter your reply to the user..."
                                      required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-send"></i> Send Reply
                        </button>
                    </form>
                @else
                    <div class="alert alert-success">
                        <i class="fa fa-check"></i> Reply has been sent to the user.
                    </div>
                @endif
                
                <hr>
                
                <div class="btn-group-vertical btn-block">
                    @if($message->status === 'unread')
                        <button onclick="markAsRead({{ $message->id }})" class="btn btn-warning">
                            <i class="fa fa-eye"></i> Mark as Read
                        </button>
                    @endif
                    
                    @if($message->status !== 'closed')
                        <button onclick="closeTicket({{ $message->id }})" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Close Ticket
                        </button>
                    @endif
                    
                    <a href="{{ route('admin.support.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        toastr.success('{{ session('success') }}');
    </script>
@endif

@if($errors->any())
    <script>
        @foreach($errors->all() as $error)
            toastr.error('{{ $error }}');
        @endforeach
    </script>
@endif

<script>
function markAsRead(id) {
    // This would need a route and method to mark as read
    // For now, just reload the page
    location.reload();
}

function closeTicket(id) {
    if(confirm('Are you sure you want to close this ticket?')) {
        // This would need a route and method to close ticket
        // For now, just reload the page
        location.reload();
    }
}
</script>
@endsection