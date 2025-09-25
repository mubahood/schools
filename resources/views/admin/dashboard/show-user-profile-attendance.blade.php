<?php
use App\Models\Utils;
?>

<style>
    .attendance-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }
    
    .attendance-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .stat-card {
        background: white;
        border-radius: 6px;
        padding: 15px;
        border-left: 4px solid;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-card.present {
        border-left-color: #28a745;
    }
    
    .stat-card.absent {
        border-left-color: #dc3545;
    }
    
    .stat-card.rate {
        border-left-color: #17a2b8;
    }
    
    .stat-card.total {
        border-left-color: #6c757d;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0;
    }
    
    .attendance-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .table-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 15px 20px;
        margin: 0;
        font-weight: 600;
    }
    
    .attendance-table table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    
    .attendance-table th {
        background: #f8f9fa;
        padding: 12px 15px;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        text-align: left;
    }
    
    .attendance-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .attendance-table tr:hover {
        background: #f8f9fa;
    }
    
    .attendance-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .badge-present {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .badge-absent {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .type-badge {
        padding: 3px 6px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 500;
        background: #e9ecef;
        color: #495057;
    }
    
    .filter-section {
        background: white;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }
    
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }
    
    .filter-group label {
        font-weight: 500;
        margin-bottom: 5px;
        display: block;
        color: #495057;
    }
    
    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    
    .btn-filter {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .btn-filter:hover {
        background: #0056b3;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }
</style>

@include('admin.dashboard.show-user-profile-header', ['u' => $u])

<div class="attendance-container">
    <h4 class="mb-4"><i class="fas fa-calendar-check text-primary"></i> Attendance Overview</h4>
    
    @if(isset($attendance_summary) && !empty($attendance_summary))
    <div class="attendance-stats-grid">
        <div class="stat-card total">
            <div class="stat-number text-secondary">{{ number_format($attendance_summary['total_sessions']) }}</div>
            <p class="stat-label">Total Sessions</p>
        </div>
        
        <div class="stat-card present">
            <div class="stat-number text-success">{{ number_format($attendance_summary['total_present']) }}</div>
            <p class="stat-label">Present</p>
        </div>
        
        <div class="stat-card absent">
            <div class="stat-number text-danger">{{ number_format($attendance_summary['total_absent']) }}</div>
            <p class="stat-label">Absent</p>
        </div>
        
        <div class="stat-card rate">
            <div class="stat-number text-info">{{ number_format($attendance_summary['overall_rate'], 1) }}%</div>
            <p class="stat-label">Attendance Rate</p>
        </div>
    </div>
    @endif
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form method="GET" action="{{ request()->url() }}" id="attendanceFilterForm">
        <div class="filter-grid">
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" name="att_start_date" value="{{ request('att_start_date', now()->subMonths(3)->format('Y-m-d')) }}">
            </div>
            
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" name="att_end_date" value="{{ request('att_end_date', now()->format('Y-m-d')) }}">
            </div>
            
            <div class="filter-group">
                <label>Attendance Type</label>
                <select name="att_type">
                    <option value="">All Types</option>
                    <option value="CLASS_ATTENDANCE" {{ request('att_type') == 'CLASS_ATTENDANCE' ? 'selected' : '' }}>Class Attendance</option>
                    <option value="THEOLOGY_ATTENDANCE" {{ request('att_type') == 'THEOLOGY_ATTENDANCE' ? 'selected' : '' }}>Theology Classes</option>
                    <option value="STUDENT_REPORT" {{ request('att_type') == 'STUDENT_REPORT' ? 'selected' : '' }}>Student Report</option>
                    <option value="STUDENT_MEAL" {{ request('att_type') == 'STUDENT_MEAL' ? 'selected' : '' }}>Meal Session</option>
                    <option value="ACTIVITY_ATTENDANCE" {{ request('att_type') == 'ACTIVITY_ATTENDANCE' ? 'selected' : '' }}>Activities</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Status</label>
                <select name="att_status">
                    <option value="">All Status</option>
                    <option value="1" {{ request('att_status') == '1' ? 'selected' : '' }}>Present</option>
                    <option value="0" {{ request('att_status') == '0' ? 'selected' : '' }}>Absent</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Attendance Records Table -->
<div class="attendance-table">
    <h5 class="table-header">
        <i class="fas fa-list"></i> Attendance Records 
        @if(isset($attendance_records))
            ({{ $attendance_records->count() }} records)
        @endif
    </h5>
    
    @if(isset($attendance_records) && $attendance_records->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Day</th>
                <th>Type</th>
                <th>Class/Session</th>
                <th>Status</th>
                <th>Time Recorded</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendance_records as $index => $record)
            <tr>
                <td><strong>{{ $index + 1 }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($record->created_at)->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($record->created_at)->format('l') }}</td>
                <td>
                    <span class="type-badge">
                        @switch($record->type)
                            @case('CLASS_ATTENDANCE')
                                Class
                                @break
                            @case('THEOLOGY_ATTENDANCE')
                                Theology
                                @break
                            @case('STUDENT_REPORT')
                                Report
                                @break
                            @case('STUDENT_MEAL')
                                Meal
                                @break
                            @case('ACTIVITY_ATTENDANCE')
                                Activity
                                @break
                            @default
                                {{ $record->type }}
                        @endswitch
                    </span>
                </td>
                <td>
                    @if(isset($record->academic_class_name) && $record->academic_class_name)
                        {{ $record->academic_class_name }}
                    @else
                        General Session
                    @endif
                </td>
                <td>
                    @if($record->is_present == 1)
                        <span class="attendance-badge badge-present">
                            <i class="fas fa-check"></i> Present
                        </span>
                    @else
                        <span class="attendance-badge badge-absent">
                            <i class="fas fa-times"></i> Absent
                        </span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($record->created_at)->format('g:i A') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h5>No Attendance Records Found</h5>
        <p>No attendance records match the selected criteria or date range.</p>
    </div>
    @endif
</div>

<script>
    // Auto-submit form when filters change
    document.querySelectorAll('#attendanceFilterForm select, #attendanceFilterForm input').forEach(element => {
        element.addEventListener('change', function() {
            document.getElementById('attendanceFilterForm').submit();
        });
    });
</script>