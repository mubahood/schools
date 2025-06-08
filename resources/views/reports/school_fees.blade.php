@php
    // This view is designed for PDF rendering.
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Termly School Fees Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #333; line-height: 1.3; }
        .w-100 { width: 100%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-uppercase { text-transform: uppercase; }
        .fw-bold { font-weight: bold; }
        .m-0 { margin: 0; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mt-4 { margin-top: 1.5rem; }
        .report-header .logo { width: 90px; }
        .report-header .school-name { font-size: 20px; font-weight: bold; color: {{ $ent->color ?? '#000' }}; }
        .report-header .school-details { font-size: 9px; line-height: 1.2; }
        .header-line { height: 2px; background-color: {{ $ent->color ?? '#000' }}; border: none; margin: 8px 0; }
        .report-title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 4px; text-decoration: underline; }
        .report-date { text-align: right; font-size: 10px; margin-bottom: 15px; }
        .kpi-card-container { width: 100%; border-spacing: 8px; border-collapse: separate; margin-bottom: 20px; }
        .kpi-card { background-color: #f9f9f9; border: 1px solid #e0e0e0; padding: 8px; border-radius: 4px; height: 60px; }
        .kpi-card .title { font-size: 10px; font-weight: bold; margin-bottom: 6px; color: #555; }
        .kpi-card .value { font-size: 16px; font-weight: bold; color: #111; }
        .kpi-card .value .small-text { font-size: 11px; }
        .section-title { font-size: 14px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-top: 20px; margin-bottom: 10px; }
        .student-section-title { font-size: 12px; font-weight: bold; background-color: #e9ecef; padding: 5px; margin-top: 15px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: middle; }
        .data-table thead th { background-color: {{ $ent->color ?? '#005b96' }}; color: #fff; font-weight: bold; text-align: center; font-size: 9px; }
        .data-table tbody tr:nth-child(even) { background-color: #f6f6f6; }
        .data-table tfoot th { font-weight: bold; background-color: #e9ecef; text-align: right; }
        .data-table .currency { text-align: right; }
        .data-table .count { text-align: center; }
    </style>
</head>
<body>
    <table class="w-100 report-header">
        <tr>
            <td style="width: 20%; text-align: left;"><img class="logo" src="{{ public_path('storage/' . $ent->logo) }}" alt="Logo"></td>
            <td style="width: 60%;" class="text-center">
                <p class="m-0 school-name text-uppercase">{{ $ent->name }}</p>
                <p class="m-0"><i>"{{ $ent->motto }}"</i></p>
                <p class="m-0 school-details">{{ $ent->p_o_box ? $ent->p_o_box . ',' : '' }} {{ $ent->address }} | TEL: {{ $ent->phone_number }}</p>
            </td>
            <td style="width: 20%;"></td>
        </tr>
    </table>
    <hr class="header-line">
    <p class="report-title">TERMLY SCHOOL FEES REPORT - {{ $term->name }}</p>
    <p class="report-date">Generated On: {{ $date }}</p>

    {{-- KPI CARDS --}}
    <table class="kpi-card-container">
        <tr>
            <td style="width: 33.3%;"><div class="kpi-card"><p class="title">Total Billed</p><p class="value"><span class="small-text">UGX</span> {{ $summary['totalFeesBilled'] }}</p></div></td>
            <td style="width: 33.3%;"><div class="kpi-card"><p class="title">Total Collected</p><p class="value"><span class="small-text">UGX</span> {{ $summary['totalFeesCollected'] }}</p></div></td>
            <td style="width: 33.3%;"><div class="kpi-card"><p class="title">Total Outstanding</p><p class="value"><span class="small-text">UGX</span> {{ $summary['totalOutstanding'] }}</p></div></td>
        </tr>
        <tr>
            <td><div class="kpi-card"><p class="title">Collections via SCHOOL_PAY</p><p class="value"><span class="small-text">UGX</span> {{ $summary['totalSchoolPay'] }}</p></div></td>
            <td><div class="kpi-card"><p class="title">Collections via MANUAL_ENTRY</p><p class="value"><span class="small-text">UGX</span> {{ $summary['totalManualEntry'] }}</p></div></td>
            <td><div class="kpi-card"><p class="title">System Generated Bills</p><p class="value"><span class="small-text">UGX</span> {{ $summary['totalGenerated'] }}</p></div></td>
        </tr>
    </table>

    {{-- CLASS-LEVEL BREAKDOWN --}}
    <h2 class="section-title">Class-Level Breakdown</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 34%;">Class</th>
                <th style="width: 11%;"># Students</th>
                <th style="width: 17%;">Fees Bill / Student</th>
                <th style="width: 17%;">Total Billed (UGX)</th>
                <th style="width: 17%;">Total Collected (UGX)</th>
                <th style="width: 17%;">Total Outstanding (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($classBreakdown as $class)
                <tr>
                    <td>{{ $class['name'] ?? 'N/A' }}</td>
                    <td class="count">{{ $class['students'] }}</td>
                    <td class="currency">{{ $class['fees_bill_per_student'] }}</td>
                    <td class="currency">{{ $class['billed'] }}</td>
                    <td class="currency">{{ $class['collected'] }}</td>
                    <td class="currency">{{ $class['outstanding'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- STUDENT-LEVEL DETAIL (GROUPED BY CLASS) --}}
    <h2 class="section-title">Student-Level Payment Details</h2>
    @forelse ($studentsByClass as $classId => $studentList)
        <div class="student-section-title">{{ $studentList->first()['class_name'] }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th class="text-left" style="width: 40%;">Student Name</th>
                    <th style="width: 18.3%;">Billed (UGX)</th>
                    <th style="width: 18.3%;">Paid (UGX)</th>
                    <th style="width: 18.3%;">Outstanding (UGX)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($studentList as $student)
                <tr>
                    <td class="count">{{ $loop->iteration }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td class="currency">{{ number_format($student['raw_expected']) }}</td>
                    <td class="currency">{{ number_format($student['raw_paid']) }}</td>
                    <td class="currency">{{ number_format($student['raw_outstanding']) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-left" colspan="2">CLASS TOTALS</th>
                    <th class="currency">{{ number_format($studentList->sum('raw_expected')) }}</th>
                    <th class="currency">{{ number_format($studentList->sum('raw_paid')) }}</th>
                    <th class="currency">{{ number_format($studentList->sum('raw_outstanding')) }}</th>
                </tr>
            </tfoot>
        </table>
    @empty
        <p>No student payment data available to display.</p>
    @endforelse
</body>
</html>