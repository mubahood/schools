@extends('admin::layouts.content')

@section('content')
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Support Messages</h3>
        <div class="box-tools pull-right">
            <span class="label label-info">{{ $messages->total() }} Total</span>
            @if($unreadCount > 0)
                <span class="label label-danger">{{ $unreadCount }} Unread</span>
            @endif
        </div>
    </div>
    
    <div class="box-body table-responsive no-padding">
        @if($messages->count() > 0)
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $message)
                        <tr class="{{ $message->is_new ? 'bg-warning' : '' }}">
                            <td>#{{ $message->id }}</td>
                            <td>{{ $message->name }}</td>
                            <td>{{ $message->email }}</td>
                            <td>{{ Str::limit($message->subject, 50) }}</td>
                            <td>
                                <span class="label label-{{ $message->status_color }}">
                                    {{ ucfirst($message->status) }}
                                </span>
                            </td>
                            <td>{{ $message->formatted_date }}</td>
                            <td>
                                <a href="{{ route('admin.support.show', $message->id) }}" 
                                   class="btn btn-xs btn-primary">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center" style="padding: 50px;">
                <h4>No support messages found</h4>
                <p class="text-muted">Support messages will appear here when users contact you.</p>
            </div>
        @endif
    </div>
    
    @if($messages->hasPages())
        <div class="box-footer">
            {{ $messages->links() }}
        </div>
    @endif
</div>
@endsection