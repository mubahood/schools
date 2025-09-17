<?php
use App\Models\Utils;
$ent = Utils::ent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ent->name }} - Contact Support</title>
    
    <!-- Meta Tags -->
    <meta name="description" content="Get help with {{ $ent->name }} school management system">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        :root {
            --primary-color: {{ $ent->color ?? '#007bff' }};
            --primary-light: {{ $ent->color ?? '#007bff' }}15;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --text-muted: #8b9dc3;
            --border-light: #e9ecef;
            --success-color: #28a745;
            --error-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --white: #ffffff;
            --light-bg: #f8f9fa;
            --shadow-light: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-medium: 0 4px 12px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, #667eea 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .support-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header Section */
        .support-header {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }

        .header-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-bottom: 1.5rem;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
        }

        .support-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .support-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Quick Help Cards */
        .quick-help {
            margin-bottom: 3rem;
        }

        .quick-help-title {
            text-align: center;
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .help-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .help-card {
            background: var(--white);
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-medium);
        }

        .help-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .help-card-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .help-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .help-card-desc {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Main Content Grid */
        .support-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .content-card {
            background: var(--white);
            padding: 2rem;
            box-shadow: var(--shadow-medium);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-bg);
        }

        .card-icon {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 1rem;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Contact Methods */
        .contact-methods {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            transition: background-color 0.3s ease;
        }

        .contact-item:hover {
            background: var(--primary-light);
        }

        .contact-icon {
            font-size: 1.4rem;
            color: var(--primary-color);
            margin-right: 1rem;
            min-width: 40px;
        }

        .contact-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .contact-info p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin: 0;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-light);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            resize: vertical;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .form-control.is-invalid {
            border-color: var(--error-color);
        }

        .invalid-feedback {
            color: var(--error-color);
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        /* Alerts */
        .alert {
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        /* FAQ Section */
        .faq-item {
            border-bottom: 1px solid var(--border-light);
            padding: 1rem 0;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-dark);
            padding: 0.5rem 0;
            transition: color 0.3s ease;
        }

        .faq-question:hover {
            color: var(--primary-color);
        }

        .faq-toggle {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .faq-toggle.active {
            transform: rotate(180deg);
        }

        .faq-answer {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.6;
            padding: 1rem 0;
            display: none;
        }

        .faq-answer.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* System Status */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--light-bg);
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .status-online {
            background: var(--success-color);
            box-shadow: 0 0 0 3px rgba(40,167,69,0.2);
        }

        .status-text {
            font-size: 0.9rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        /* Back to Login */
        .back-to-login {
            text-align: center;
            margin-top: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateY(-1px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .support-content {
                grid-template-columns: 1fr;
            }
            
            .help-cards {
                grid-template-columns: 1fr;
            }
            
            .support-title {
                font-size: 2rem;
            }
            
            .content-card {
                padding: 1.5rem;
            }
            
            .status-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="support-container">
        <!-- Header Section -->
        <div class="support-header">
            <div class="header-content">
                <img src="{{ $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png') }}" 
                     alt="{{ $ent->name }}" 
                     class="brand-logo">
                <h1 class="support-title">How can we help you?</h1>
                <p class="support-subtitle">
                    Our support team is here to assist you with any questions or issues you may have with the {{ $ent->name }} school management system.
                </p>
            </div>
        </div>

        <!-- Quick Help Cards -->
        <div class="quick-help">
            <h2 class="quick-help-title">Quick Help</h2>
            <div class="help-cards">
                <div class="help-card" onclick="scrollToSection('contact-form')">
                    <i class='bx bx-message-dots help-card-icon'></i>
                    <h3 class="help-card-title">Send Message</h3>
                    <p class="help-card-desc">Send us a detailed message about your issue and we'll get back to you soon.</p>
                </div>
                <div class="help-card" onclick="scrollToSection('contact-info')">
                    <i class='bx bx-phone help-card-icon'></i>
                    <h3 class="help-card-title">Call Support</h3>
                    <p class="help-card-desc">Speak directly with our support team for immediate assistance.</p>
                </div>
                <div class="help-card" onclick="scrollToSection('faq')">
                    <i class='bx bx-help-circle help-card-icon'></i>
                    <h3 class="help-card-title">Browse FAQ</h3>
                    <p class="help-card-desc">Find answers to commonly asked questions about the system.</p>
                </div>
                <div class="help-card" onclick="scrollToSection('system-status')">
                    <i class='bx bx-shield-check help-card-icon'></i>
                    <h3 class="help-card-title">System Status</h3>
                    <p class="help-card-desc">Check the current status of all system services and components.</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="support-content">
            <!-- Contact Information -->
            <div class="content-card" id="contact-info">
                <div class="card-header">
                    <i class='bx bx-phone-call card-icon'></i>
                    <h2 class="card-title">Contact Information</h2>
                </div>
                
                <div class="contact-methods">
                    <div class="contact-item">
                        <i class='bx bx-phone contact-icon'></i>
                        <div class="contact-info">
                            <h4>Phone Support</h4>
                            <p>{{ Utils::phone() ?: '+256 700 000 000' }}</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class='bx bx-envelope contact-icon'></i>
                        <div class="contact-info">
                            <h4>Email Support</h4>
                            <p>{{ Utils::email() ?: 'support@' . strtolower(str_replace(' ', '', $ent->name)) . '.com' }}</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class='bx bx-time contact-icon'></i>
                        <div class="contact-info">
                            <h4>Support Hours</h4>
                            <p>Monday - Friday: 8:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class='bx bx-map contact-icon'></i>
                        <div class="contact-info">
                            <h4>Office Address</h4>
                            <p>{{ $ent->address ?: 'Kampala, Uganda' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="content-card" id="contact-form">
                <div class="card-header">
                    <i class='bx bx-message-square-detail card-icon'></i>
                    <h2 class="card-title">Send us a Message</h2>
                </div>

                @if(session('status'))
                    <div class="alert alert-success">
                        <i class='bx bx-check-circle'></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class='bx bx-error-circle'></i>
                        Please correct the errors below and try again.
                    </div>
                @endif

                <form method="POST" action="{{ url('auth/support') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <select class="form-control @error('subject') is-invalid @enderror" 
                                id="subject" 
                                name="subject" 
                                required>
                            <option value="">Select a subject</option>
                            <option value="Login Issues" {{ old('subject') == 'Login Issues' ? 'selected' : '' }}>Login Issues</option>
                            <option value="Technical Support" {{ old('subject') == 'Technical Support' ? 'selected' : '' }}>Technical Support</option>
                            <option value="Account Management" {{ old('subject') == 'Account Management' ? 'selected' : '' }}>Account Management</option>
                            <option value="Feature Request" {{ old('subject') == 'Feature Request' ? 'selected' : '' }}>Feature Request</option>
                            <option value="Bug Report" {{ old('subject') == 'Bug Report' ? 'selected' : '' }}>Bug Report</option>
                            <option value="Other" {{ old('subject') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                  id="message" 
                                  name="message" 
                                  rows="5" 
                                  placeholder="Please describe your issue in detail..."
                                  required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="captcha" class="form-label">Security Code</label>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <img src="{{ url('auth/captcha') }}" 
                                 alt="CAPTCHA" 
                                 id="captcha-image"
                                 style="border: 2px solid var(--border-light); padding: 5px; background: white;">
                            <button type="button" 
                                    onclick="refreshCaptcha()" 
                                    style="background: var(--primary-color); color: white; border: none; padding: 8px 12px; cursor: pointer; font-size: 14px;">
                                <i class='bx bx-refresh'></i> Refresh
                            </button>
                        </div>
                        <input type="text" 
                               class="form-control @error('captcha') is-invalid @enderror" 
                               id="captcha" 
                               name="captcha" 
                               placeholder="Enter the numbers shown above"
                               autocomplete="off"
                               required>
                        @error('captcha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small style="color: var(--text-light); font-size: 0.8rem;">
                            Please enter the 4-digit number shown in the image above.
                        </small>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class='bx bx-send'></i>
                        Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="content-card" id="faq" style="margin-bottom: 2rem;">
            <div class="card-header">
                <i class='bx bx-help-circle card-icon'></i>
                <h2 class="card-title">Frequently Asked Questions</h2>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I reset my password?</span>
                    <i class='bx bx-chevron-down faq-toggle'></i>
                </div>
                <div class="faq-answer">
                    You can reset your password by clicking on the "Forgot Password?" link on the login page. Enter your email address or username, and we'll send you instructions to reset your password.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Why can't I access my account?</span>
                    <i class='bx bx-chevron-down faq-toggle'></i>
                </div>
                <div class="faq-answer">
                    Account access issues can occur due to incorrect credentials, account suspension, or network problems. Try resetting your password first. If the issue persists, contact our support team.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I update my profile information?</span>
                    <i class='bx bx-chevron-down faq-toggle'></i>
                </div>
                <div class="faq-answer">
                    Once logged in, navigate to your profile settings where you can update your personal information, contact details, and account preferences.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Is my data secure in the system?</span>
                    <i class='bx bx-chevron-down faq-toggle'></i>
                </div>
                <div class="faq-answer">
                    Yes, we use industry-standard security measures including data encryption, secure connections, and regular security audits to protect your information.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How can I report a bug or technical issue?</span>
                    <i class='bx bx-chevron-down faq-toggle'></i>
                </div>
                <div class="faq-answer">
                    You can report bugs by using the contact form above with "Bug Report" as the subject, or by calling our technical support line directly.
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="content-card" id="system-status" style="margin-bottom: 2rem;">
            <div class="card-header">
                <i class='bx bx-server card-icon'></i>
                <h2 class="card-title">System Status</h2>
            </div>

            <div class="status-grid">
                <div class="status-item">
                    <div class="status-indicator status-online"></div>
                    <span class="status-text">Login System</span>
                </div>
                <div class="status-item">
                    <div class="status-indicator status-online"></div>
                    <span class="status-text">Database</span>
                </div>
                <div class="status-item">
                    <div class="status-indicator status-online"></div>
                    <span class="status-text">Email Service</span>
                </div>
                <div class="status-item">
                    <div class="status-indicator status-online"></div>
                    <span class="status-text">File Storage</span>
                </div>
                <div class="status-item">
                    <div class="status-indicator status-online"></div>
                    <span class="status-text">Backup System</span>
                </div>
                <div class="status-item">
                    <div class="status-indicator status-online"></div>
                    <span class="status-text">Security Services</span>
                </div>
            </div>
        </div>

        <!-- Back to Login -->
        <div class="back-to-login">
            <a href="{{ url('/auth/login') }}" class="back-link">
                <i class='bx bx-arrow-back'></i>
                Back to Login
            </a>
        </div>
    </div>

    <!-- Simple JavaScript -->
    <script>
        function toggleFaq(element) {
            const answer = element.nextElementSibling;
            const toggle = element.querySelector('.faq-toggle');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-answer').forEach(item => {
                if (item !== answer) {
                    item.classList.remove('active');
                }
            });
            document.querySelectorAll('.faq-toggle').forEach(item => {
                if (item !== toggle) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle current FAQ
            answer.classList.toggle('active');
            toggle.classList.toggle('active');
        }

        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function refreshCaptcha() {
            const captchaImage = document.getElementById('captcha-image');
            const captchaInput = document.getElementById('captcha');
            
            if (captchaImage) {
                captchaImage.src = '{{ url("auth/captcha") }}?' + new Date().getTime();
            }
            
            if (captchaInput) {
                captchaInput.value = '';
                captchaInput.focus();
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>