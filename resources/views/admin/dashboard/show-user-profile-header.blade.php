<style>
/* ── Student Profile Card ──────────────────────────────────────────── */
.sp {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #d0d7e2;
    overflow: hidden;
    margin-bottom: 18px;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
}

/* Identity strip */
.sp-top {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: linear-gradient(100deg, #1b4332 0%, #2d6a4f 100%);
}
.sp-avatar {
    width: 72px;
    height: 72px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.3);
    flex-shrink: 0;
    background: #0d2b1d;
}
.sp-name {
    color: #fff;
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 6px;
    line-height: 1.15;
}
.sp-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.sp-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 11px;
    border-radius: 20px;
    font-size: .82rem;
    font-weight: 600;
    letter-spacing: .1px;
}
.sp-tag-class { background: rgba(255,255,255,.18); color:#fff; }
.sp-tag-code  { background: rgba(40,167,69,.6);   color:#fff; font-family:monospace; }
.sp-tag-sex-f { background: rgba(232,62,140,.45);  color:#fff; }
.sp-tag-sex-m { background: rgba(0,123,255,.4);   color:#fff; }

/* ── Body: Bootstrap grid row ───────────────────────────────────────── */
.sp-body {
    border-top: 1px solid #e3e8ee;
    padding: 0;
}

/* Each section */
.sp-section {
    padding: 16px 18px;
    border-right: 1px solid #e3e8ee;
    height: 100%;
}
.sp-section:last-child { border-right: none; }

.sp-sec-title {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #8a9ab0;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    border-bottom: 1px solid #f0f3f7;
    padding-bottom: 8px;
}

/* ── Bio table ──────────────────────────────────────────────────────── */
.sp-bio-table {
    width: 100%;
    font-size: .9rem;
    border-collapse: collapse;
}
.sp-bio-table tr td {
    padding: 4px 0;
    vertical-align: top;
    line-height: 1.5;
}
.sp-bio-table td:first-child {
    color: #8a9ab0;
    font-weight: 600;
    white-space: nowrap;
    padding-right: 14px;
    width: 1%;
}
.sp-bio-table td:last-child {
    color: #2c3e50;
    word-break: break-word;
}

/* ── Fees grid ──────────────────────────────────────────────────────── */
.sp-fees-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 7px;
}
.sp-stat {
    background: #f5f7fa;
    border-radius: 7px;
    padding: 9px 12px;
    border-left: 3px solid #d1d8e0;
}
.sp-stat-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: #8a9ab0;
    margin-bottom: 3px;
    white-space: nowrap;
}
.sp-stat-value {
    font-size: .97rem;
    font-weight: 700;
    color: #2c3e50;
    font-variant-numeric: tabular-nums;
    line-height: 1.2;
}
.sp-stat--fees     { border-left-color: #6c757d; }
.sp-stat--services { border-left-color: #6f42c1; }
.sp-stat--bf       { border-left-color: #fd7e14; }
.sp-stat--payable  { border-left-color: #007bff; }
.sp-stat--paid     { border-left-color: #28a745; }
.sp-stat--ok       { border-left-color: #28a745; background: #f0faf3; }
.sp-stat--debt     { border-left-color: #dc3545; background: #fff5f5; }

/* ── Attendance ─────────────────────────────────────────────────────── */
.sp-att-overview {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 14px;
}
.sp-rate-badge {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    text-align: center;
    line-height: 1.15;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}
.sp-att-counts table {
    font-size: .88rem;
    border-collapse: collapse;
}
.sp-att-counts td {
    padding: 2px 6px 2px 0;
    line-height: 1.6;
}
.sp-att-counts td:first-child { color: #8a9ab0; font-weight: 600; }
.sp-att-counts td:last-child  { font-weight: 700; color: #2c3e50; padding-left: 4px; }

.sp-bar-wrap {
    margin-top: 4px;
}
.sp-bar-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 0;
}
.sp-bar-name {
    font-size: .83rem;
    color: #555;
    width: 116px;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sp-bar-track {
    flex: 1;
    height: 7px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    min-width: 40px;
}
.sp-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width .4s ease;
}
.sp-bar-pct {
    font-size: .82rem;
    font-weight: 700;
    width: 40px;
    text-align: right;
    flex-shrink: 0;
}
</style>

<?php
$avatar       = $u->avatar;
$acctBalance  = optional($u->account)->balance ?? 0;
$student_data = null;
if ($u->user_type == 'student') {
    $student_data = $u->get_finances();
}
?>

<div class="sp">

    {{-- ── Identity strip ──────────────────────────────────────────── --}}
    <div class="sp-top">
        <img class="sp-avatar" src="{{ $avatar }}" alt="{{ $u->name }}">
        <div style="flex:1;min-width:0">
            <div class="sp-name">{{ $u->name }}</div>
            <div class="sp-tags">
                @if(optional($u->current_class)->name_text)
                    <span class="sp-tag sp-tag-class">
                        <i class="fa fa-graduation-cap"></i> {{ $u->current_class->name_text }}
                    </span>
                @endif
                <span class="sp-tag sp-tag-code">
                    <i class="fa fa-barcode"></i> {{ $u->school_pay_payment_code }}
                </span>
                @if($u->sex)
                    <span class="sp-tag {{ $u->sex == 'Female' ? 'sp-tag-sex-f' : 'sp-tag-sex-m' }}">
                        <i class="fa fa-{{ $u->sex == 'Female' ? 'venus' : 'mars' }}"></i> {{ $u->sex }}
                    </span>
                @endif
                @if($u->phone_number_1 && strlen($u->phone_number_1) > 2)
                    <span class="sp-tag sp-tag-class">
                        <i class="fa fa-phone"></i> {{ $u->phone_number_1 }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Body — Bootstrap grid row ───────────────────────────────── --}}
    <div class="sp-body">
        <div class="row" style="margin:0">

            {{-- Bio details ─ col-md-4 ──────────────────────────────── --}}
            <div class="col-md-4 col-sm-12" style="padding:0;border-right:1px solid #e3e8ee">
                <div class="sp-section">
                    <div class="sp-sec-title"><i class="fa fa-id-card-o"></i> Bio Details</div>
                    <table class="sp-bio-table">
                        @if($u->date_of_birth && strlen($u->date_of_birth) > 2)
                        <tr><td>Date of birth</td><td>{{ $u->date_of_birth }}</td></tr>
                        @endif
                        @if($u->place_of_birth && strlen($u->place_of_birth) > 2)
                        <tr><td>Place of birth</td><td>{{ $u->place_of_birth }}</td></tr>
                        @endif
                        @if($u->current_address && strlen($u->current_address) > 2)
                        <tr><td>Address</td><td>{{ $u->current_address }}</td></tr>
                        @endif
                        @if($u->nationality && strlen($u->nationality) > 2)
                        <tr><td>Nationality</td><td>{{ $u->nationality }}</td></tr>
                        @endif
                        @if($u->religion && strlen($u->religion) > 2)
                        <tr><td>Religion</td><td>{{ $u->religion }}</td></tr>
                        @endif
                        @if($u->languages && strlen($u->languages) > 2)
                        <tr><td>Languages</td><td>{{ $u->languages }}</td></tr>
                        @endif
                        @if($u->father_name && strlen($u->father_name) > 2)
                        <tr>
                            <td>Father</td>
                            <td>{{ $u->father_name }}
                                @if($u->father_phone && strlen($u->father_phone) > 2)
                                    <br><span style="color:#8a9ab0;font-size:.83rem">{{ $u->father_phone }}</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if($u->mother_name && strlen($u->mother_name) > 2)
                        <tr>
                            <td>Mother</td>
                            <td>{{ $u->mother_name }}
                                @if($u->mother_phone && strlen($u->mother_phone) > 2)
                                    <br><span style="color:#8a9ab0;font-size:.83rem">{{ $u->mother_phone }}</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if($u->phone_number_2 && strlen($u->phone_number_2) > 2)
                        <tr><td>Phone 2</td><td>{{ $u->phone_number_2 }}</td></tr>
                        @endif
                    </table>
                    @if(!$u->date_of_birth && !$u->place_of_birth && !$u->nationality && !$u->father_name && !$u->mother_name)
                        <p style="color:#aaa;font-size:.88rem;margin:0">No bio details on record.</p>
                    @endif
                </div>
            </div>

            {{-- Fees summary ─ col-md-4 ─────────────────────────────── --}}
            @if($student_data)
            <div class="col-md-4 col-sm-6" style="padding:0;border-right:1px solid #e3e8ee">
                <div class="sp-section">
                    <div class="sp-sec-title"><i class="fa fa-money"></i> School Fees — This Term</div>
                    <div class="sp-fees-grid">
                        <div class="sp-stat sp-stat--fees">
                            <div class="sp-stat-label">School Fees</div>
                            <div class="sp-stat-value">{{ number_format($student_data['fees']) }}</div>
                        </div>
                        <div class="sp-stat sp-stat--services">
                            <div class="sp-stat-label">Services</div>
                            <div class="sp-stat-value">{{ number_format($student_data['services']) }}</div>
                        </div>
                        <div class="sp-stat sp-stat--bf">
                            <div class="sp-stat-label">Prev. Balance</div>
                            <div class="sp-stat-value">{{ number_format($student_data['balance_bf']) }}</div>
                        </div>
                        <div class="sp-stat sp-stat--payable">
                            <div class="sp-stat-label">Total Payable</div>
                            <div class="sp-stat-value" style="color:#007bff">{{ number_format($student_data['total_payable']) }}</div>
                        </div>
                        <div class="sp-stat sp-stat--paid">
                            <div class="sp-stat-label">Total Paid</div>
                            <div class="sp-stat-value" style="color:#28a745">{{ number_format($student_data['total_paid']) }}</div>
                        </div>
                        @php $isDebt = $acctBalance < 0; @endphp
                        <div class="sp-stat {{ $isDebt ? 'sp-stat--debt' : 'sp-stat--ok' }}">
                            <div class="sp-stat-label">Balance</div>
                            <div class="sp-stat-value" style="color:{{ $isDebt ? '#dc3545' : '#28a745' }}">
                                @if($isDebt)
                                    <i class="fa fa-exclamation-triangle" style="font-size:.78rem"></i>
                                    {{ number_format(abs($acctBalance)) }} owed
                                @else
                                    <i class="fa fa-check-circle" style="font-size:.78rem"></i>
                                    {{ number_format($acctBalance) }} clear
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Attendance ─ col-md-4 ───────────────────────────────── --}}
            @if($u->user_type == 'student' && !empty($attendance_summary))
            <div class="col-md-4 col-sm-6" style="padding:0">
                <div class="sp-section">
                    <div class="sp-sec-title"><i class="fa fa-calendar-check-o"></i> Attendance — This Term</div>
                    @php
                        $rate      = $attendance_summary['overall_rate'];
                        $rateColor = $rate >= 80 ? '#28a745' : ($rate >= 60 ? '#e6a817' : '#dc3545');
                    @endphp
                    <div class="sp-att-overview">
                        <div class="sp-rate-badge" style="background:{{ $rateColor }}">
                            {{ number_format($rate, 1) }}%
                        </div>
                        <div class="sp-att-counts">
                            <table>
                                <tr>
                                    <td>Sessions</td>
                                    <td>{{ $attendance_summary['total_sessions'] }}</td>
                                </tr>
                                <tr>
                                    <td>Present</td>
                                    <td style="color:#28a745">{{ $attendance_summary['total_present'] }}</td>
                                </tr>
                                <tr>
                                    <td>Absent</td>
                                    <td style="color:#dc3545">{{ $attendance_summary['total_absent'] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @if(!empty($attendance_summary['by_type']))
                    <div class="sp-bar-wrap">
                        @foreach($attendance_summary['by_type'] as $td)
                        @php
                            $tr = $td['rate'];
                            $tc = $tr >= 80 ? '#28a745' : ($tr >= 60 ? '#e6a817' : '#dc3545');
                        @endphp
                        <div class="sp-bar-row">
                            <span class="sp-bar-name" title="{{ $td['type_name'] }}">{{ $td['type_name'] }}</span>
                            <div class="sp-bar-track">
                                <div class="sp-bar-fill" style="width:{{ min($tr,100) }}%;background:{{ $tc }}"></div>
                            </div>
                            <span class="sp-bar-pct" style="color:{{ $tc }}">{{ number_format($tr,1) }}%</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>{{-- /.row --}}
    </div>
</div>
<hr style="margin:0 0 16px;border-color:#d0d7e2;">
