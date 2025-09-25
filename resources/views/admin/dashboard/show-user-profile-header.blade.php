<style>
    .profile-header {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: 1px solid #dee2e6;
    }
    
    .profile-avatar {
        border-radius: 10px;
        border: 3px solid #007bff;
        padding: 3px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .profile-basic-info {
        padding: 0 15px;
    }
    
    .profile-name {
        color: #2c3e50;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1.2;
    }
    
    .profile-class {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 8px;
    }
    
    .payment-code {
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 15px;
    }
    
    .profile-info-item {
        font-size: 0.95rem;
        line-height: 1.4;
        margin-bottom: 6px;
        padding: 2px 0;
    }
    
    .profile-info-item b {
        color: #495057;
        font-weight: 600;
    }
    
    .summary-card {
        background: white;
        border-radius: 8px;
        padding: 18px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    
    .summary-title {
        color: #007bff;
        font-size: 1rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 8px 0;
        font-size: 0.9rem;
        line-height: 1.3;
    }
    
    .summary-label {
        font-weight: 600;
        color: #495057;
    }
    
    .summary-value {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .summary-divider {
        border-color: #007bff;
        margin: 10px 0;
    }
    
    .attendance-summary-card {
        background: white;
        border-radius: 8px;
        padding: 18px;
        border: 1px solid #28a745;
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.1);
        margin-top: 15px;
    }
    
    .attendance-title {
        color: #28a745;
        font-size: 1rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .attendance-rate-badge {
        font-size: 0.85rem;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
    }
    
    .type-summary {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 8px;
        margin: 5px 0;
        border-left: 3px solid #28a745;
    }
    
    .type-rate {
        font-size: 0.8rem;
        padding: 2px 6px;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
<?php
$avatar = $u->avatar;
$payable = 0;
$paid = 0;
$balance = 0;
if ($u->account != null) {
    if ($u->account->transactions != null) {
        foreach ($u->account->transactions as $key => $v) {
            if ($v->amount < 0) {
                $payable += $v->amount;
            } else {
                $paid += $v->amount;
            }
        }
    }
}
$student_data = null;
if ($u->user_type == 'student') {
    $student_data = $u->get_finances();
}

//$balance = $payable + $paid;
$balance = $u->account->balance;
?>
<div class="profile-header">
<div class="row">
    <div class="col-xs-4 col-md-2 ">
        <img class="img img-fluid " width="130" src="{{ $avatar }}">
    </div>
    <div class="col-xs-8 col-md-6 no-padding pt-0 mt-0">
        <h4 class="no-padding " style="line-height: .8">{{ $u->name }}</h4>
        <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Current Class:</b> {{ $u->current_class->name_text }}
        <h5 class="no-padding " style="line-height: .6"><b>PAYMENT CODE:</b> {{ $u->school_pay_payment_code }}</h5>
        <hr class="border-primary" style="margin-top: 0px; margin-bottom: 8px; ">
        <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Sex:</b> {{ $u->sex }}</p>

        </p>
        @if ($u->date_of_birth != null && strlen($u->date_of_birth) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Date of birth:</b> {{ $u->date_of_birth }}</p>
        @endif

        @if ($u->place_of_birth != null && strlen($u->place_of_birth) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Place of birth:</b> {{ $u->place_of_birth }}</p>
        @endif


        @if ($u->date_of_birth != null && strlen($u->date_of_birth) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Home address:</b> {{ $u->date_of_birth }}</p>
        @endif

        @if ($u->current_address != null && strlen($u->current_address) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Current address:</b> {{ $u->current_address }}</p>
        @endif

        @if ($u->phone_number_1 != null && strlen($u->phone_number_1) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Phone number 1:</b> {{ $u->phone_number_1 }}</p>
        @endif

        @if ($u->phone_number_2 != null && strlen($u->phone_number_2) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Phone number 2:</b> {{ $u->phone_number_2 }}</p>
        @endif

        @if ($u->nationality != null && strlen($u->nationality) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Nationality:</b> {{ $u->nationality }}</p>
        @endif

        @if ($u->religion != null && strlen($u->religion) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Religion:</b> {{ $u->religion }}</p>
        @endif

        @if ($u->father_name != null && strlen($u->father_name) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Father's name:</b> {{ $u->father_name }}</p>
        @endif

        @if ($u->father_phone != null && strlen($u->father_phone) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Father's phone number:</b> {{ $u->father_phone }}
            </p>
        @endif

        @if ($u->mother_name != null && strlen($u->mother_name) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Mother's name:</b> {{ $u->mother_name }}</p>
        @endif

        @if ($u->date_of_birth != null && strlen($u->date_of_birth) > 2)
        @endif

        @if ($u->mother_phone != null && strlen($u->mother_phone) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Mother's phone number:</b> {{ $u->mother_phone }}
            </p>
        @endif

        @if ($u->languages != null && strlen($u->languages) > 2)
            <p class="mb-2 mb-md-3 item" style="line-height: .6"><b>Languages/Dilect:</b> {{ $u->languages }}</p>
        @endif

    </div>
    <div class="col-xs-12 col-md-4 mt-4 mt-md-0">
        <div class="summary-card">
            @if ($student_data != null)
                <h4 class="summary-title">
                    <i class="fas fa-money-bill-wave"></i> School Fees Summary (This Term)
                </h4>
                <div class="summary-item">
                    <span class="summary-label">School Fees:</span>
                    <span class="summary-value">UGX {{ number_format($student_data['fees']) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Services:</span>
                    <span class="summary-value">UGX {{ number_format($student_data['services']) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Previous Balance:</span>
                    <span class="summary-value">UGX {{ number_format($student_data['balance_bf']) }}</span>
                </div>
                <hr class="summary-divider">
                <div class="summary-item">
                    <span class="summary-label">Total Payable:</span>
                    <span class="summary-value text-primary">UGX {{ number_format($student_data['total_payable']) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Paid:</span>
                    <span class="summary-value text-success">UGX {{ number_format($student_data['total_paid']) }}</span>
                </div>
                <hr class="summary-divider">
                <div class="summary-item">
                    <span class="summary-label">Fees Balance:</span>
                    <span class="summary-value {{ $u->account->balance < 0 ? 'text-danger' : 'text-success' }}">
                        UGX {{ number_format($u->account->balance) }}
                    </span>
                </div>
            @endif
            {{--             <h4 class="text-center"><b><u>FEES SUMMARY</u></b></h4>

            <p><b>TOTAL PAID FEES:</b> UGX {{ number_format($paid) }}</p>
            <p><b>FEES BALANCE:</b> UGX {{ number_format($balance) }}</p> --}}
        </div>
        
        @if ($u->user_type == 'student' && isset($attendance_summary))
        <div class="attendance-summary-card">
            <h4 class="attendance-title">
                <i class="fas fa-calendar-check"></i> Attendance Summary (This Term)
            </h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="summary-item">
                        <span class="summary-label">Total Sessions:</span>
                        <span class="summary-value">{{ number_format($attendance_summary['total_sessions']) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Present:</span>
                        <span class="summary-value text-success">{{ number_format($attendance_summary['total_present']) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Absent:</span>
                        <span class="summary-value text-danger">{{ number_format($attendance_summary['total_absent']) }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center">
                        <div class="attendance-rate-badge badge-{{ $attendance_summary['overall_rate'] >= 80 ? 'success' : ($attendance_summary['overall_rate'] >= 60 ? 'warning' : 'danger') }}"
                             style="background-color: {{ $attendance_summary['overall_rate'] >= 80 ? '#28a745' : ($attendance_summary['overall_rate'] >= 60 ? '#ffc107' : '#dc3545') }}; color: white; font-size: 1.2rem; padding: 8px 16px;">
                            {{ number_format($attendance_summary['overall_rate'], 1) }}%
                        </div>
                        <small class="text-muted d-block mt-1">Attendance Rate</small>
                    </div>
                </div>
            </div>
            
            <hr class="summary-divider" style="border-color: #28a745;">
            <h6 class="text-center text-success" style="font-weight: 600; margin-bottom: 10px;">BY TYPE</h6>
            
            @if(!empty($attendance_summary['by_type']))
                @foreach($attendance_summary['by_type'] as $type_data)
                <div class="type-summary">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-weight: 500; font-size: 0.85rem;">{{ $type_data['type_name'] }}</span>
                        <div>
                            <span class="type-rate badge-{{ $type_data['rate'] >= 80 ? 'success' : ($type_data['rate'] >= 60 ? 'warning' : 'danger') }}"
                                  style="background-color: {{ $type_data['rate'] >= 80 ? '#28a745' : ($type_data['rate'] >= 60 ? '#ffc107' : '#dc3545') }}; color: white;">
                                {{ number_format($type_data['rate'], 1) }}%
                            </span>
                            <small class="text-muted ml-1">({{ $type_data['present'] }}/{{ $type_data['total'] }})</small>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-center text-muted" style="font-size: 0.9rem;">No attendance data available</p>
            @endif
        </div>
        @endif
    </div>
</div>
</div>
<!-- End profile-header -->
<hr class="border-primary" style="margin-top: 14px; margin-bottom: 8px; ">
