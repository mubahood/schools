<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        :root {
            --school-primary: {{ $schoolPrimary ?? '#3c8dbc' }};
            --school-secondary: {{ $schoolSecondary ?? '#f39c12' }};
        }
        
        body {
            background-color: #f4f6f9;
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        
        .application-header {
            background: linear-gradient(135deg, var(--school-primary), var(--school-secondary));
            color: white;
            padding: 30px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .school-logo {
            max-height: 80px;
            margin-bottom: 15px;
        }
        
        .school-name {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .school-motto {
            font-size: 16px;
            font-style: italic;
            opacity: 0.9;
        }
        
        .progress-steps {
            background: white;
            padding: 20px;
            margin: 30px 0;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .step {
            display: inline-block;
            width: 19%;
            text-align: center;
            position: relative;
            padding: 10px 0;
        }
        
        .step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 25px;
            right: -10%;
            width: 100%;
            height: 2px;
            background: #ddd;
            z-index: -1;
        }
        
        .step.active:not(:last-child):after {
            background: var(--school-primary);
        }
        
        .step .step-number {
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            background: #ddd;
            color: #999;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background: var(--school-primary);
            color: white;
        }
        
        .step.completed .step-number {
            background: #00a65a;
            color: white;
        }
        
        .step .step-label {
            font-size: 13px;
            color: #666;
        }
        
        .step.active .step-label {
            color: var(--school-primary);
            font-weight: 600;
        }
        
        .application-container {
            max-width: 900px;
            margin: 0 auto 40px;
        }
        
        .application-card {
            background: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .card-header-custom {
            border-bottom: 2px solid var(--school-primary);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .card-header-custom h3 {
            margin: 0;
            color: var(--school-primary);
            font-size: 24px;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--school-primary);
            border-color: var(--school-primary);
        }
        
        .btn-primary:hover {
            background-color: var(--school-secondary);
            border-color: var(--school-secondary);
        }
        
        .session-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            max-width: 300px;
        }
        
        .session-indicator.warning {
            border-left: 4px solid #f39c12;
        }
        
        .session-indicator.danger {
            border-left: 4px solid #dd4b39;
        }
        
        .session-indicator.success {
            border-left: 4px solid #00a65a;
        }
        
        .auto-save-status {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            background: white;
            border-radius: 3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            font-size: 13px;
            z-index: 1000;
            display: none;
        }
        
        .auto-save-status.saving {
            display: block;
            color: #f39c12;
        }
        
        .auto-save-status.saved {
            display: block;
            color: #00a65a;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--school-primary);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .required-field:after {
            content: ' *';
            color: red;
        }
        
        @media (max-width: 768px) {
            .step {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .step:not(:last-child):after {
                display: none;
            }
            
            .school-name {
                font-size: 22px;
            }
            
            .application-card {
                padding: 20px 15px;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Application Header -->
    <div class="application-header">
        <div class="container">
            <div class="text-center">
                @if(isset($schoolLogo))
                    <img src="{{ $schoolLogo }}" alt="School Logo" class="school-logo">
                @endif
                <div class="school-name">{{ $schoolName ?? config('app.name') }}</div>
                @if(isset($schoolMotto))
                    <div class="school-motto">{{ $schoolMotto }}</div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Progress Steps -->
    @if(isset($showProgress) && $showProgress)
    <div class="container">
        <div class="progress-steps">
            <div class="step {{ $currentStep >= 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'completed' : '' }}">
                <div class="step-number">
                    @if($currentStep > 1)
                        <i class="fa fa-check"></i>
                    @else
                        1
                    @endif
                </div>
                <div class="step-label">School Selection</div>
            </div>
            
            <div class="step {{ $currentStep >= 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'completed' : '' }}">
                <div class="step-number">
                    @if($currentStep > 2)
                        <i class="fa fa-check"></i>
                    @else
                        2
                    @endif
                </div>
                <div class="step-label">Personal Information</div>
            </div>
            
            <div class="step {{ $currentStep >= 3 ? 'active' : '' }} {{ $currentStep > 3 ? 'completed' : '' }}">
                <div class="step-number">
                    @if($currentStep > 3)
                        <i class="fa fa-check"></i>
                    @else
                        3
                    @endif
                </div>
                <div class="step-label">Confirmation</div>
            </div>
            
            <div class="step {{ $currentStep >= 4 ? 'active' : '' }} {{ $currentStep > 4 ? 'completed' : '' }}">
                <div class="step-number">
                    @if($currentStep > 4)
                        <i class="fa fa-check"></i>
                    @else
                        4
                    @endif
                </div>
                <div class="step-label">Documents</div>
            </div>
            
            <div class="step {{ $currentStep >= 5 ? 'active' : '' }} {{ $currentStep > 5 ? 'completed' : '' }}">
                <div class="step-number">
                    @if($currentStep > 5)
                        <i class="fa fa-check"></i>
                    @else
                        5
                    @endif
                </div>
                <div class="step-label">Complete</div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Main Content -->
    <div class="application-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Please correct the following errors:</strong>
                <ul style="margin-top: 10px; margin-bottom: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
    </div>
    
    <!-- Auto-save Status -->
    <div id="autoSaveStatus" class="auto-save-status">
        <i class="fa fa-spinner fa-spin"></i> Saving...
    </div>
    
    <!-- Session Timeout Indicator -->
    @if(isset($showSessionTimer) && $showSessionTimer)
    <div id="sessionIndicator" class="session-indicator" style="display: none;">
        <div style="margin-bottom: 5px;">
            <strong>Session Time Remaining:</strong>
        </div>
        <div id="sessionTimer" style="font-size: 18px; font-weight: bold;">--:--</div>
        <div style="margin-top: 5px; font-size: 12px; color: #666;">
            Your progress is being saved automatically
        </div>
    </div>
    @endif
    
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <script>
        // Set CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>
