<style>
    .lp-card {
        border-radius: 8px;
        border: 1px solid #e3e8ef;
        background: #ffffff;
        padding: 14px;
        margin-bottom: 12px;
        min-height: 92px;
    }
    .lp-kpi-title {
        font-size: 11px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: #6b7785;
        margin-bottom: 6px;
        font-weight: 700;
    }
    .lp-kpi-value {
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
        color: #1f2d3d;
    }
    .lp-actions {
        margin-bottom: 12px;
    }
    .lp-actions .btn { margin-right: 8px; }
    .lp-table th {
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .2px;
    }
</style>

<div class="lp-actions">
    <a href="{{ admin_url('lesson-plans') }}" class="btn btn-primary btn-sm"><i class="fa fa-list"></i> All Lesson Plans</a>
    <a href="{{ admin_url('lesson-plans/create') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> New Lesson Plan</a>
</div>

<div class="row">
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="lp-card">
            <div class="lp-kpi-title">{{ $isPrivileged ? 'Total Plans' : 'My Total' }}</div>
            <div class="lp-kpi-value">{{ $stats['my_total'] }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="lp-card">
            <div class="lp-kpi-title">{{ $isPrivileged ? 'Draft' : 'My Draft' }}</div>
            <div class="lp-kpi-value">{{ $stats['my_draft'] }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="lp-card">
            <div class="lp-kpi-title">{{ $isPrivileged ? 'Submitted' : 'My Submitted' }}</div>
            <div class="lp-kpi-value">{{ $stats['my_submitted'] }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="lp-card">
            <div class="lp-kpi-title">Changes Requested</div>
            <div class="lp-kpi-value">{{ $stats['my_changes'] }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="lp-card">
            <div class="lp-kpi-title">{{ $isPrivileged ? 'Approved' : 'My Approved' }}</div>
            <div class="lp-kpi-value">{{ $stats['my_approved'] }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="lp-card">
            <div class="lp-kpi-title">Pending Review</div>
            <div class="lp-kpi-value">{{ $stats['to_review'] }}</div>
        </div>
    </div>
</div>

<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-clock-o"></i> Pending Reviews</h3>
    </div>
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover lp-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingReviews as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td>{{ $p->plan_date ? date('d M Y', strtotime($p->plan_date)) : '-' }}</td>
                        <td>{{ optional($p->academic_class)->name ?? '-' }}</td>
                        <td>{{ $p->template_type === 'nursery' ? ($p->learning_area ?: '-') : (optional($p->subject)->subject_name ?? '-') }}</td>
                        <td>{{ optional($p->teacher)->name ?? '-' }}</td>
                        <td>{{ $p->submitted_at ? date('d M Y H:i', strtotime($p->submitted_at)) : '-' }}</td>
                        <td>
                            <a href="{{ admin_url('lesson-plans/' . $p->id) }}" class="btn btn-xs btn-info">View</a>
                            <a href="{{ admin_url('lesson-plans/' . $p->id . '/review?action=approve') }}" class="btn btn-xs btn-success" onclick="return confirm('Approve this lesson plan?')">Approve</a>
                            <a href="{{ admin_url('lesson-plans/' . $p->id . '/review?action=changes') }}" class="btn btn-xs btn-danger" onclick="return confirm('Request changes from teacher?')">Request Changes</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding:16px;">No pending lesson plans for review.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-history"></i> {{ $isPrivileged ? 'All Recent Lesson Plans' : 'My Recent Lesson Plans' }}</h3>
    </div>
    <div class="box-body table-responsive no-padding">
        <table class="table table-striped lp-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    @if($isPrivileged)<th>Teacher</th>@endif
                    <th>Title</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentMine as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td>{{ $p->plan_date ? date('d M Y', strtotime($p->plan_date)) : '-' }}</td>
                        @if($isPrivileged)<td>{{ optional($p->teacher)->name ?? '-' }}</td>@endif
                        <td>{{ $p->topic ?: ($p->theme ?: '-') }}</td>
                        <td>
                            @if($p->status === 'Approved')
                                <span class="label label-success">Approved</span>
                            @elseif($p->status === 'Submitted')
                                <span class="label label-warning">Submitted</span>
                            @elseif($p->status === 'Changes Requested')
                                <span class="label label-danger">Changes Requested</span>
                            @else
                                <span class="label label-default">Draft</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ admin_url('lesson-plans/' . $p->id . '/edit') }}" class="btn btn-xs btn-primary">Edit</a>
                            @if($p->teacher_id == $currentUserId && in_array($p->status, ['Draft', 'Changes Requested']))
                                <a href="{{ admin_url('lesson-plans/' . $p->id . '/submit') }}" class="btn btn-xs btn-warning" onclick="return confirm('Submit this lesson plan for review?')">Submit</a>
                            @endif
                            @if(($isPrivileged || $p->supervisor_id == $currentUserId) && $p->status === 'Submitted')
                                <a href="{{ admin_url('lesson-plans/' . $p->id . '/review?action=approve') }}" class="btn btn-xs btn-success" onclick="return confirm('Approve this lesson plan?')">Approve</a>
                                <a href="{{ admin_url('lesson-plans/' . $p->id . '/review?action=changes') }}" class="btn btn-xs btn-danger" onclick="return confirm('Request changes?')">Changes</a>
                            @endif
                            <a href="{{ admin_url('lesson-plans/' . $p->id . '/print') }}" target="_blank" class="btn btn-xs btn-default">Print</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isPrivileged ? 6 : 5 }}" class="text-center text-muted" style="padding:16px;">No lesson plans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
