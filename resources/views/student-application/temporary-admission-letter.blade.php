<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <title>Temporary Admission Letter - {{ $application->application_number }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            position: relative;
        }
        
        /* Watermark */
        body::before {
            content: "";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 60%;
            background-image: url({{ $logoPath }});
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.08;
            z-index: -1;
        }
        
        .header-container {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid {{ $school->school_pay_primary_color ?? '#3c8dbc' }};
        }
        
        .school-logo {
            max-width: 120px;
            max-height: 120px;
            margin-bottom: 15px;
        }
        
        .school-name {
            font-size: 26px;
            font-weight: bold;
            color: {{ $school->school_pay_primary_color ?? '#3c8dbc' }};
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .school-motto {
            font-style: italic;
            color: #666;
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .school-details {
            font-size: 12px;
            color: #555;
            line-height: 1.4;
        }
        
        .letter-date {
            text-align: right;
            margin: 20px 0;
            font-weight: bold;
        }
        
        .letter-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
            text-transform: uppercase;
            color: {{ $school->school_pay_primary_color ?? '#3c8dbc' }};
        }
        
        .letter-body {
            text-align: justify;
            margin: 20px 0;
        }
        
        .letter-body p {
            margin-bottom: 15px;
        }
        
        .highlight {
            font-weight: bold;
            color: {{ $school->school_pay_primary_color ?? '#3c8dbc' }};
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid {{ $school->school_pay_primary_color ?? '#3c8dbc' }};
            padding: 15px;
            margin: 20px 0;
        }
        
        .info-box h4 {
            margin-top: 0;
            color: {{ $school->school_pay_primary_color ?? '#3c8dbc' }};
            font-size: 16px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .info-table th,
        .info-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .info-table th {
            background: {{ $school->school_pay_primary_color ?? '#3c8dbc' }};
            color: white;
            font-weight: bold;
        }
        
        .info-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .requirements-table th,
        .requirements-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .requirements-table th {
            background: {{ $school->school_pay_primary_color ?? '#3c8dbc' }};
            color: white;
            text-align: center;
            font-size: 13px;
        }
        
        .requirements-table td {
            font-size: 12px;
        }
        
        .requirements-table .text-right {
            text-align: right;
        }
        
        .requirements-table .total-row {
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .signature-section {
            margin-top: 50px;
        }
        
        .signature-line {
            margin-top: 40px;
            border-top: 2px solid #333;
            width: 200px;
        }
        
        .footer-note {
            margin-top: 30px;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .temporary-notice {
            background: #d1ecf1;
            border: 2px solid #0c5460;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            border-radius: 5px;
        }
        
        .temporary-notice strong {
            color: #0c5460;
            font-size: 16px;
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .verification-box {
            border: 2px dashed #999;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <!-- Header with School Information -->
    <div class="header-container">
        @if($school->logo)
        <img src="{{ $logoPath }}" alt="{{ $school->name }}" class="school-logo">
        @endif
        
        <div class="school-name">{{ $school->name }}</div>
        
        @if($school->motto)
        <div class="school-motto">"{{ $school->motto }}"</div>
        @endif
        
        <div class="school-details">
            <strong>Address:</strong> {{ $school->address ?? 'N/A' }}, {{ $school->p_o_box ?? '' }}<br>
            <strong>Email:</strong> {{ $school->email ?? 'N/A' }} | 
            <strong>Tel:</strong> {{ $school->phone_number ?? 'N/A' }}
            @if($school->phone_number_2)
            , {{ $school->phone_number_2 }}
            @endif
            @if($school->website)
            <br><strong>Website:</strong> {{ $school->website }}
            @endif
        </div>
    </div>
    
    <!-- Temporary Notice -->
    <div class="temporary-notice">
        <strong>!! TEMPORARY ADMISSION LETTER</strong><br>
        <small>This is a temporary admission letter issued pending official confirmation. An official admission letter will be issued upon completion of registration formalities.</small>
    </div>
    
    <!-- Date -->
    <div class="letter-date">
        {{ now()->format('jS F, Y') }}
    </div>
    
    <!-- Letter Title -->
    <div class="letter-title">
        Temporary Admission Letter
    </div>
    
    <!-- Letter Body -->
    <div class="letter-body">
        <p>
            Dear <span class="highlight">{{ $application->full_name }}</span>,
        </p>
        
        <p>
            We are pleased to inform you that your application (<span class="highlight">{{ $application->application_number }}</span>) 
            to join <span class="highlight">{{ $school->name }}</span> has been <strong>provisionally accepted</strong>.
        </p>
        
        <p>
            This letter serves as temporary confirmation of your admission pending completion of the registration process 
            and verification of submitted documents.
        </p>
    </div>
    
    <!-- Student Information Box -->
    <div class="info-box">
        <h4>APPLICATION DETAILS</h4>
        <table class="info-table">
            <tr>
                <th style="width: 40%;">Application Number</th>
                <td><strong>{{ $application->application_number }}</strong></td>
            </tr>
            <tr>
                <th>Student Name</th>
                <td>{{ $application->full_name }}</td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td>{{ $application->date_of_birth ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Gender</th>
                <td>{{ $application->gender ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Class Applied For</th>
                <td><strong>{{ $application->applying_for_class ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <th>Email Address</th>
                <td>{{ $application->email }}</td>
            </tr>
            <tr>
                <th>Phone Number</th>
                <td>{{ $application->phone_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Application Date</th>
                <td>{{ $application->submitted_at ? $application->submitted_at->format('jS F, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Acceptance Date</th>
                <td>{{ $application->completed_at ? $application->completed_at->format('jS F, Y') : now()->format('jS F, Y') }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Next Steps -->
    <div class="letter-body">
        <h4 style="color: {{ $school->school_pay_primary_color ?? '#3c8dbc' }};">NEXT STEPS</h4>
        <ol style="line-height: 2;">
            <li>Visit the school with this admission letter for official registration</li>
            <li>Complete all registration formalities within <strong>14 days</strong> from the date of this letter</li>
            <li>Submit original documents for verification</li>
            <li>Make payment of school fees as per the fee structure below</li>
            <li>Collect your official admission letter and student ID card</li>
        </ol>
    </div>
    
    @if($requiredDocuments && count($requiredDocuments) > 0)
    <!-- Required Documents -->
    <div class="info-box">
        <h4>DOCUMENTS TO BRING</h4>
        <ul style="margin: 10px 0;">
            @foreach($requiredDocuments as $doc)
            <li>
                {{ $doc['name'] }}
                @if($doc['required'])
                <span style="color: #dc3545; font-weight: bold;">(Required)</span>
                @else
                <span style="color: #6c757d;">(Optional)</span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif
    
    @if(isset($feeStructure) && count($feeStructure) > 0)
    <!-- Fee Structure -->
    <div class="letter-body">
        <h4 style="color: {{ $school->school_pay_primary_color ?? '#3c8dbc' }};">ESTIMATED FEE STRUCTURE</h4>
        <table class="requirements-table">
            <thead>
                <tr>
                    <th style="width: 10%;">S/N</th>
                    <th>Item Description</th>
                    <th style="width: 25%;" class="text-right">Amount (UGX)</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; $count = 1; @endphp
                @foreach($feeStructure as $fee)
                <tr>
                    <td style="text-align: center;">{{ $count++ }}</td>
                    <td>{{ $fee['name'] }}</td>
                    <td class="text-right">{{ number_format($fee['amount']) }}/=</td>
                </tr>
                @php $total += $fee['amount']; @endphp
                @endforeach
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;"><strong>ESTIMATED TOTAL</strong></td>
                    <td class="text-right"><strong>UGX {{ number_format($total) }}/=</strong></td>
                </tr>
            </tbody>
        </table>
        <p style="font-size: 11px; font-style: italic; color: #666;">
            <strong>Note:</strong> The above fee structure is an estimate. Final fees will be confirmed during official registration.
        </p>
    </div>
    @endif
    
    <!-- Important Notice -->
    <div class="footer-note">
        <strong>!! IMPORTANT NOTICE:</strong><br>
        <ul style="margin: 10px 0; padding-left: 20px; font-size: 11px;">
            <li>This temporary admission letter is valid for 14 days from the date of issue</li>
            <li>Admission is subject to verification of all submitted documents</li>
            <li>The school reserves the right to withdraw admission if any information provided is found to be false</li>
            <li>Please bring this letter when visiting the school for registration</li>
            <li>For any queries, contact the admissions office using the details provided above</li>
        </ul>
    </div>
    
    @if($application->admin_notes)
    <!-- Admin Notes -->
    <div style="margin: 20px 0; padding: 15px; background: #e7f3ff; border-left: 4px solid #2196F3;">
        <strong style="color: #1976D2;">Additional Notes from Admissions:</strong><br>
        <p style="margin: 10px 0; font-size: 13px;">{{ $application->admin_notes }}</p>
    </div>
    @endif
    
    <!-- School Rules Reminder -->
    <div class="letter-body">
        <p>
            You are expected to abide by the <strong>school rules and regulations</strong> at all times. 
            A detailed copy of the school rules and regulations will be provided during official registration.
        </p>
        
        <p>
            We look forward to welcoming you to <span class="highlight">{{ $school->name }}</span> and wish you success in your academic journey.
        </p>
    </div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <p><strong>Yours faithfully,</strong></p>
        <div class="signature-line"></div>
        <p><strong>ADMISSIONS OFFICE</strong><br>
        {{ $school->name }}</p>
    </div>
    
    <!-- Verification Box -->
    <div class="verification-box">
        <strong>VERIFICATION CODE: {{ strtoupper(substr(md5($application->application_number), 0, 8)) }}</strong><br>
        Application Number: {{ $application->application_number }} | 
        Generated: {{ now()->format('d/m/Y H:i') }}<br>
        <small>To verify this letter, visit {{ $school->website ?? url('/') }} or contact the admissions office</small>
    </div>
    
    <!-- Footer -->
    <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px;">
        <em>This is a computer-generated temporary admission letter and does not require a physical signature.</em><br>
        Generated on {{ now()->format('jS F, Y \a\t H:i') }} via Online Application Portal
    </div>
</body>
</html>
