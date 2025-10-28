@php
    // Session Report PDF Template - Daily/Weekly Attendance Record
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title ?? 'Attendance Report' }}</title>
    <style>
        @page {
            margin: 0.8cm;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
        }
        
        .report-container {
            border: 3px solid #000;
            padding: 15px;
            min-height: 27cm;
        }
        
        /* School Header */
        .school-header {
            text-align: center;
            margin-bottom: 12px;
        }
        
        .school-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 2px 0;
        }
        
        .report-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 3px 0;
        }
        
        /* Date and Week Section */
        .info-section {
            margin: 10px 0 12px 0;
        }
        
        .info-row {
            width: 100%;
            margin-bottom: 5px;
            overflow: hidden;
        }
        
        .info-label {
            font-weight: bold;
            float: left;
            width: 50px;
        }
        
        .info-value {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 250px;
            padding-bottom: 1px;
        }
        
        .week-section {
            float: right;
        }
        
        .week-label {
            font-weight: bold;
            display: inline-block;
            margin-right: 5px;
        }
        
        .week-value {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
            padding-bottom: 1px;
            text-align: right;
        }
        
        /* Teachers Section */
        .teachers-section {
            margin: 10px 0;
        }
        
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .teacher-row {
            margin-bottom: 4px;
            width: 100%;
            overflow: hidden;
        }
        
        .teacher-number {
            float: left;
            width: 20px;
            font-weight: bold;
        }
        
        .teacher-name {
            float: left;
            border-bottom: 1px solid #000;
            width: 450px;
            padding-bottom: 1px;
        }
        
        .sign-section {
            float: right;
        }
        
        .sign-label {
            font-weight: bold;
            display: inline-block;
            margin-right: 5px;
        }
        
        .sign-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 120px;
            padding-bottom: 1px;
        }
        
        /* Head of Week */
        .head-section {
            margin: 10px 0 15px 0;
        }
        
        .head-row {
            width: 100%;
            overflow: hidden;
        }
        
        .head-label {
            float: left;
            width: 50px;
            font-weight: bold;
        }
        
        .head-name {
            float: left;
            border-bottom: 1px solid #000;
            width: 440px;
            padding-bottom: 1px;
        }
        
        /* Attendance Table */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .attendance-table th,
        .attendance-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-size: 10px;
        }
        
        .attendance-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            line-height: 1.2;
        }
        
        .attendance-table .class-col {
            text-align: left;
            font-weight: bold;
            width: 12%;
            padding-left: 8px;
        }
        
        .attendance-table .number-col {
            width: 9%;
        }
        
        .attendance-table .percent-col {
            width: 9%;
            font-size: 9px;
        }
        
        .attendance-table .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        
        .percentage {
            color: #333;
            font-style: italic;
        }
        
        /* Absentees Section */
        .absentees-section {
            margin-top: 20px;
        }
        
        .absentees-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .absentees-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .absentees-table th,
        .absentees-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10px;
        }
        
        .absentees-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        
        .absentees-table .sn-col {
            width: 6%;
            text-align: center;
        }
        
        .absentees-table .name-col {
            width: 44%;
            text-align: left;
        }
        
        .absentees-table .class-col {
            width: 44%;
            text-align: left;
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        
        /* Clear fix */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="report-container">
        {{-- School Header --}}
        <div class="school-header">
            <p class="school-name">{{ strtoupper($ent->name) }}</p>
            <p class="report-title">DAILY ATTENDANCE RECORD - {{ strtoupper(str_replace('_', ' ', $report->type ?? 'STUDENT REPORT')) }}</p>
        </div>
        
        {{-- Date and Week Information --}}
        <div class="info-section">
            <div class="info-row clearfix">
                <span class="info-label">DATE:</span>
                <span class="info-value">
                    {{ \Carbon\Carbon::parse($report->start_date)->format('l, F j, Y') }}
                    @if($report->start_date != $report->end_date)
                        - {{ \Carbon\Carbon::parse($report->end_date)->format('l, F j, Y') }}
                    @endif
                </span>
                <span class="week-section">
                    <span class="week-label">WEEK:</span>
                    <span class="week-value">Week {{ \Carbon\Carbon::parse($report->start_date)->weekOfYear }}</span>
                </span>
            </div>
        </div>
        
        {{-- Teachers on Duty --}}
        <div class="teachers-section">
            <p class="section-title">TEACHERS ON DUTY</p>
            
            <div class="teacher-row clearfix">
                <span class="teacher-number">1.</span>
                <span class="teacher-name">
                    {{ $report->teacher1 ? strtoupper($report->teacher1->name) : '___________________________________________' }}
                </span>
                <span class="sign-section">
                    <span class="sign-label">SIGN:</span>
                    <span class="sign-line"></span>
                </span>
            </div>
            
            <div class="teacher-row clearfix">
                <span class="teacher-number">2.</span>
                <span class="teacher-name">
                    {{ $report->teacher2 ? strtoupper($report->teacher2->name) : '___________________________________________' }}
                </span>
                <span class="sign-section">
                    <span class="sign-label">SIGN:</span>
                    <span class="sign-line"></span>
                </span>
            </div>
        </div>
        
        {{-- Head of the Week --}}
        <div class="head-section">
            <p class="section-title">HEAD OF THE WEEK</p>
            <div class="head-row clearfix">
                <span class="head-label">NAME:</span>
                <span class="head-name">
                    {{ $report->headOfWeek ? strtoupper($report->headOfWeek->name) : '___________________________________________' }}
                </span>
                <span class="sign-section">
                    <span class="sign-label">SIGN:</span>
                    <span class="sign-line"></span>
                </span>
            </div>
        </div>
        
        {{-- Attendance Table --}}
        <table class="attendance-table">
            <thead>
                <tr>
                    <th class="class-col">CLASS</th>
                    <th class="number-col">BOYS<br>PRESENT</th>
                    <th class="percent-col">%</th>
                    <th class="number-col">BOYS<br>ABSENT</th>
                    <th class="percent-col">%</th>
                    <th class="number-col">GIRLS<br>PRESENT</th>
                    <th class="percent-col">%</th>
                    <th class="number-col">GIRLS<br>ABSENT</th>
                    <th class="percent-col">%</th>
                    <th class="number-col">TOTAL<br>PRESENT</th>
                    <th class="number-col">TOTAL<br>ABSENT</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotalBoysPresent = 0;
                    $grandTotalBoysAbsent = 0;
                    $grandTotalGirlsPresent = 0;
                    $grandTotalGirlsAbsent = 0;
                    $grandTotalPresent = 0;
                    $grandTotalAbsent = 0;
                @endphp
                
                @if(is_array($report->target_audience_data) && count($report->target_audience_data) > 0)
                    @foreach($report->target_audience_data as $class)
                        @php
                            $boysPresent = $class['male_present'] ?? 0;
                            $boysAbsent = $class['male_absent'] ?? 0;
                            $girlsPresent = $class['female_present'] ?? 0;
                            $girlsAbsent = $class['female_absent'] ?? 0;
                            $totalPresent = $boysPresent + $girlsPresent;
                            $totalAbsent = $boysAbsent + $girlsAbsent;
                            $classTotal = $totalPresent + $totalAbsent;
                            
                            // Calculate percentages
                            $boysPresentPercent = $classTotal > 0 ? round(($boysPresent / $classTotal) * 100, 1) : 0;
                            $boysAbsentPercent = $classTotal > 0 ? round(($boysAbsent / $classTotal) * 100, 1) : 0;
                            $girlsPresentPercent = $classTotal > 0 ? round(($girlsPresent / $classTotal) * 100, 1) : 0;
                            $girlsAbsentPercent = $classTotal > 0 ? round(($girlsAbsent / $classTotal) * 100, 1) : 0;
                            
                            $grandTotalBoysPresent += $boysPresent;
                            $grandTotalBoysAbsent += $boysAbsent;
                            $grandTotalGirlsPresent += $girlsPresent;
                            $grandTotalGirlsAbsent += $girlsAbsent;
                            $grandTotalPresent += $totalPresent;
                            $grandTotalAbsent += $totalAbsent;
                        @endphp
                        <tr>
                            <td class="class-col">{{ strtoupper($class['title'] ?? $class['title_long'] ?? 'N/A') }}</td>
                            <td>{{ number_format($boysPresent) }}</td>
                            <td class="percentage">{{ $boysPresentPercent }}%</td>
                            <td>{{ number_format($boysAbsent) }}</td>
                            <td class="percentage">{{ $boysAbsentPercent }}%</td>
                            <td>{{ number_format($girlsPresent) }}</td>
                            <td class="percentage">{{ $girlsPresentPercent }}%</td>
                            <td>{{ number_format($girlsAbsent) }}</td>
                            <td class="percentage">{{ $girlsAbsentPercent }}%</td>
                            <td>{{ number_format($totalPresent) }}</td>
                            <td>{{ number_format($totalAbsent) }}</td>
                        </tr>
                    @endforeach
                    
                    {{-- Grand Total Row --}}
                    @php
                        $overallTotal = $grandTotalPresent + $grandTotalAbsent;
                        $grandBoysPresentPercent = $overallTotal > 0 ? round(($grandTotalBoysPresent / $overallTotal) * 100, 1) : 0;
                        $grandBoysAbsentPercent = $overallTotal > 0 ? round(($grandTotalBoysAbsent / $overallTotal) * 100, 1) : 0;
                        $grandGirlsPresentPercent = $overallTotal > 0 ? round(($grandTotalGirlsPresent / $overallTotal) * 100, 1) : 0;
                        $grandGirlsAbsentPercent = $overallTotal > 0 ? round(($grandTotalGirlsAbsent / $overallTotal) * 100, 1) : 0;
                    @endphp
                    <tr class="total-row">
                        <td class="class-col">GRAND TOTAL</td>
                        <td>{{ number_format($grandTotalBoysPresent) }}</td>
                        <td class="percentage">{{ $grandBoysPresentPercent }}%</td>
                        <td>{{ number_format($grandTotalBoysAbsent) }}</td>
                        <td class="percentage">{{ $grandBoysAbsentPercent }}%</td>
                        <td>{{ number_format($grandTotalGirlsPresent) }}</td>
                        <td class="percentage">{{ $grandGirlsPresentPercent }}%</td>
                        <td>{{ number_format($grandTotalGirlsAbsent) }}</td>
                        <td class="percentage">{{ $grandGirlsAbsentPercent }}%</td>
                        <td>{{ number_format($grandTotalPresent) }}</td>
                        <td>{{ number_format($grandTotalAbsent) }}</td>
                    </tr>
                @else
                    {{-- Empty rows if no data --}}
                    @for($i = 0; $i < 10; $i++)
                        <tr>
                            <td class="class-col">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                    <tr class="total-row">
                        <td class="class-col">GRAND TOTAL</td>
                        <td>0</td>
                        <td>0%</td>
                        <td>0</td>
                        <td>0%</td>
                        <td>0</td>
                        <td>0%</td>
                        <td>0</td>
                        <td>0%</td>
                        <td>0</td>
                        <td>0</td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        {{-- Summary Statistics Section --}}
        @php
            $overallTotal = $grandTotalPresent + $grandTotalAbsent;
            $overallPresentPercent = $overallTotal > 0 ? round(($grandTotalPresent / $overallTotal) * 100, 1) : 0;
            $overallAbsentPercent = $overallTotal > 0 ? round(($grandTotalAbsent / $overallTotal) * 100, 1) : 0;
            
            $boysTotal = $grandTotalBoysPresent + $grandTotalBoysAbsent;
            $girlsTotal = $grandTotalGirlsPresent + $grandTotalGirlsAbsent;
            
            $boysPresentRate = $boysTotal > 0 ? round(($grandTotalBoysPresent / $boysTotal) * 100, 1) : 0;
            $girlsPresentRate = $girlsTotal > 0 ? round(($grandTotalGirlsPresent / $girlsTotal) * 100, 1) : 0;
            
            $boysAbsentRate = $boysTotal > 0 ? round(($grandTotalBoysAbsent / $boysTotal) * 100, 1) : 0;
            $girlsAbsentRate = $girlsTotal > 0 ? round(($grandTotalGirlsAbsent / $girlsTotal) * 100, 1) : 0;
            
            $genderRatio = ($boysTotal + $girlsTotal) > 0 ? round(($boysTotal / ($boysTotal + $girlsTotal)) * 100, 1) : 0;
        @endphp
        
        <div style="margin: 20px 0;">
            <p style="font-weight: bold; text-transform: uppercase; margin-bottom: 10px; font-size: 11px; border-bottom: 2px solid #000; padding-bottom: 3px;">
                <i style="font-style: normal;">📊</i> ATTENDANCE SUMMARY & STATISTICS
            </p>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <thead>
                    <tr style="background-color: #e8e8e8;">
                        <th style="border: 1px solid #000; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; width: 35%;">METRIC</th>
                        <th style="border: 1px solid #000; padding: 6px; text-align: center; font-size: 10px; font-weight: bold; width: 20%;">BOYS</th>
                        <th style="border: 1px solid #000; padding: 6px; text-align: center; font-size: 10px; font-weight: bold; width: 20%;">GIRLS</th>
                        <th style="border: 1px solid #000; padding: 6px; text-align: center; font-size: 10px; font-weight: bold; width: 25%;">OVERALL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; font-size: 10px; font-weight: bold;">Total Enrolled</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px;">{{ number_format($boysTotal) }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px;">{{ number_format($girlsTotal) }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; font-weight: bold;">{{ number_format($overallTotal) }}</td>
                    </tr>
                    <tr style="background-color: #f9f9f9;">
                        <td style="border: 1px solid #000; padding: 5px; font-size: 10px; font-weight: bold;">Students Present</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; color: #00a65a;">
                            <strong>{{ number_format($grandTotalBoysPresent) }}</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; color: #00a65a;">
                            <strong>{{ number_format($grandTotalGirlsPresent) }}</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; font-weight: bold; color: #00a65a;">
                            {{ number_format($grandTotalPresent) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; font-size: 10px; font-weight: bold;">Attendance Rate</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; color: #00a65a;">
                            <strong style="font-size: 11px;">{{ $boysPresentRate }}%</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; color: #00a65a;">
                            <strong style="font-size: 11px;">{{ $girlsPresentRate }}%</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 11px; font-weight: bold; background-color: #d4edda; color: #155724;">
                            {{ $overallPresentPercent }}%
                        </td>
                    </tr>
                    <tr style="background-color: #f9f9f9;">
                        <td style="border: 1px solid #000; padding: 5px; font-size: 10px; font-weight: bold;">Students Absent</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; color: #dd4b39;">
                            {{ number_format($grandTotalBoysAbsent) }}
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; color: #dd4b39;">
                            {{ number_format($grandTotalGirlsAbsent) }}
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; font-weight: bold; color: #dd4b39;">
                            {{ number_format($grandTotalAbsent) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; font-size: 10px; font-weight: bold;">Absenteeism Rate</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; color: #dd4b39;">
                            <strong style="font-size: 11px;">{{ $boysAbsentRate }}%</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; color: #dd4b39;">
                            <strong style="font-size: 11px;">{{ $girlsAbsentRate }}%</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 11px; font-weight: bold; background-color: #f8d7da; color: #721c24;">
                            {{ $overallAbsentPercent }}%
                        </td>
                    </tr>
                    <tr style="background-color: #e8e8e8;">
                        <td style="border: 1px solid #000; padding: 5px; font-size: 10px; font-weight: bold;">Gender Distribution</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px;">
                            <strong>{{ $genderRatio }}%</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px;">
                            <strong>{{ 100 - $genderRatio }}%</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; font-style: italic;">
                            Boys:Girls Ratio
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        {{-- Top Absentees Section --}}
        <div class="absentees-section">
            <p class="absentees-title">TOP ABSENTEES</p>
            <table class="absentees-table">
                <thead>
                    <tr>
                        <th class="sn-col">S/N</th>
                        <th class="name-col">PUPIL'S NAME</th>
                        <th class="class-col">CLASS</th>
                        <th class="sn-col">S/N</th>
                        <th class="name-col">PUPIL'S NAME</th>
                        <th class="class-col">CLASS</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $absentees = [];
                        
                        // Parse top_absentees if it's JSON string
                        if (is_string($report->top_absentees)) {
                            try {
                                $absentees = json_decode($report->top_absentees, true) ?? [];
                            } catch (\Exception $e) {
                                $absentees = [];
                            }
                        } elseif (is_array($report->top_absentees)) {
                            $absentees = $report->top_absentees;
                        }
                        
                        // Create array of absentee details
                        $absenteesList = [];
                        foreach ($absentees as $absent) {
                            if (isset($absent['administrator_id'])) {
                                $student = \App\Models\User::find($absent['administrator_id']);
                                if ($student) {
                                    $absenteesList[] = [
                                        'name' => $student->name,
                                        'class' => $student->current_class ? $student->current_class->short_name : 'N/A',
                                        'count' => $absent['absence_count'] ?? 0
                                    ];
                                }
                            }
                        }
                        
                        // Create rows (2 columns per row)
                        $rows = ceil(count($absenteesList) / 2);
                        if ($rows < 6) $rows = 6; // Minimum 6 rows
                    @endphp
                    
                    @for($i = 0; $i < $rows; $i++)
                        <tr>
                            {{-- Left Column --}}
                            <td class="sn-col">{{ isset($absenteesList[$i * 2]) ? ($i * 2 + 1) : '' }}</td>
                            <td class="name-col">{{ isset($absenteesList[$i * 2]) ? strtoupper($absenteesList[$i * 2]['name']) : '' }}</td>
                            <td class="class-col">{{ isset($absenteesList[$i * 2]) ? $absenteesList[$i * 2]['class'] : '' }}</td>
                            
                            {{-- Right Column --}}
                            <td class="sn-col">{{ isset($absenteesList[$i * 2 + 1]) ? ($i * 2 + 2) : '' }}</td>
                            <td class="name-col">{{ isset($absenteesList[$i * 2 + 1]) ? strtoupper($absenteesList[$i * 2 + 1]['name']) : '' }}</td>
                            <td class="class-col">{{ isset($absenteesList[$i * 2 + 1]) ? $absenteesList[$i * 2 + 1]['class'] : '' }}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
