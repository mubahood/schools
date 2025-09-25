<?php
//use Utils;
use App\Models\Utils;
?>
<style>
    .item {
        font-size: 1.4rem;
        line-height: 1.4;
    }
    
    .profile-section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #dee2e6;
    }
    
    .section-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 12px 20px;
        margin: -20px -20px 20px -20px;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        font-size: 1.1rem;
        border-bottom: 1px solid #0056b3;
    }
    
    .transaction-item {
        background: white;
        border-radius: 6px;
        padding: 12px 15px;
        margin-bottom: 8px;
        border-left: 4px solid;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .transaction-positive {
        border-left-color: #28a745;
        background: linear-gradient(90deg, #f8fff9, #ffffff);
    }
    
    .transaction-negative {
        border-left-color: #dc3545;
        background: linear-gradient(90deg, #fff8f8, #ffffff);
    }
    
    .transaction-icon {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    
    .transaction-content {
        flex-grow: 1;
        font-size: 0.95rem;
        line-height: 1.3;
    }
    
    .employee-section {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
        border: 1px solid #e9ecef;
    }
    
    .employee-section .item {
        font-size: 1.2rem;
        margin-bottom: 8px;
    }
    
    .no-data {
        text-align: center;
        padding: 30px;
        color: #6c757d;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px dashed #dee2e6;
    }
    
    .no-data i {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #dee2e6;
    }
</style>

@include('admin.dashboard.show-user-profile-header', ['u' => $u, 'attendance_summary' => $attendance_summary ?? null])
<div class="row">
    <div class="col-xs-12 col-md-12">
        @if ($u->user_type == 'student')
            <div class="profile-section">
                <h5 class="section-header">
                    <i class="fas fa-money-bill-wave"></i> School Fees Billing & Payment (This Term)
                </h5>
                
                @if (empty($active_term_transactions))
                    <div class="no-data">
                        <i class="fas fa-receipt"></i>
                        <h6>No Transactions Found</h6>
                        <p class="mb-0">This student has no transactions for the current term.</p>
                    </div>
                @else
                    @foreach ($active_term_transactions as $tra)
                        @php
                            $is_positive = $tra->amount >= 0;
                            $icon_color = $is_positive ? '#28a745' : '#dc3545';
                        @endphp
                        
                        <div class="transaction-item {{ $is_positive ? 'transaction-positive' : 'transaction-negative' }}">
                            <div class="transaction-icon" style="background-color: {{ $icon_color }};"></div>
                            <div class="transaction-content">
                                <strong>{{ Utils::my_date($tra->payment_date) }}</strong> - 
                                <span class="text-{{ $is_positive ? 'success' : 'danger' }}">
                                    <strong>UGX {{ number_format(abs($tra->amount)) }}</strong>
                                </span>
                                {{ $is_positive ? '' : '(Debit)' }}
                                <br>
                                <small class="text-muted">{{ $tra->description }}</small>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif


        @if ($u->user_type == 'employee')
            <div class="profile-section">
                <h5 class="section-header">
                    <i class="fas fa-user-tie"></i> Employee Information
                </h5>
                
                <div class="employee-section">
                    <div class="row">
                        <div class="col-md-6">
                            @if($u->emergency_person_name)
                                <p class="item"><b>Emergency Contact:</b> {{ $u->emergency_person_name }}</p>
                            @endif
                            @if($u->emergency_person_phone)
                                <p class="item"><b>Emergency Phone:</b> {{ $u->emergency_person_phone }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($u->spouse_name)
                                <p class="item"><b>Spouse Name:</b> {{ $u->spouse_name }}</p>
                            @endif
                            @if($u->spouse_phone)
                                <p class="item"><b>Spouse Phone:</b> {{ $u->spouse_phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h5 class="section-header">
                    <i class="fas fa-graduation-cap"></i> Educational Background
                </h5>
                
                <div class="employee-section">
                    <div class="row">
                        <div class="col-md-6">
                            @if($u->primary_school_name)
                                <p class="item"><b>Primary School:</b> {{ $u->primary_school_name }} 
                                    @if($u->primary_school_year_graduated) ({{ $u->primary_school_year_graduated }}) @endif
                                </p>
                            @endif
                            @if($u->seconday_school_name)
                                <p class="item"><b>Secondary School:</b> {{ $u->seconday_school_name }}
                                    @if($u->seconday_school_year_graduated) ({{ $u->seconday_school_year_graduated }}) @endif
                                </p>
                            @endif
                            @if($u->high_school_name)
                                <p class="item"><b>High School:</b> {{ $u->high_school_name }}
                                    @if($u->high_school_year_graduated) ({{ $u->high_school_year_graduated }}) @endif
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($u->degree_university_name)
                                <p class="item"><b>Degree:</b> {{ $u->degree_university_name }}
                                    @if($u->degree_university_year_graduated) ({{ $u->degree_university_year_graduated }}) @endif
                                </p>
                            @endif
                            @if($u->masters_university_name)
                                <p class="item"><b>Master's:</b> {{ $u->masters_university_name }}
                                    @if($u->masters_university_year_graduated) ({{ $u->masters_university_year_graduated }}) @endif
                                </p>
                            @endif
                            @if($u->phd_university_name)
                                <p class="item"><b>PhD:</b> {{ $u->phd_university_name }}
                                    @if($u->phd_university_year_graduated) ({{ $u->phd_university_year_graduated }}) @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
