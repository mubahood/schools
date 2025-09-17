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
            --secondary-color: {{ $ent->sec_color ?? '#6c757d' }};
            --background-light: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --border-light: #e9ecef;
            --success-color: #28a745;
            --error-color: #dc3545;
            --shadow-light: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-medium: 0 4px 20px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .support-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .support-header {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid rgba(255,255,255,0.2);
        }

        .support-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .support-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .support-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .contact-methods, .contact-form-container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-medium);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-method {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-light);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-dark);
        }

        .contact-method:hover {
            background: var(--background-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-light);
            color: var(--text-dark);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .contact-info h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .contact-info p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin: 0;
        }

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
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            resize: vertical;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color), 0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: var(--background-light);
            color: var(--text-dark);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .faq-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-medium);
            margin-bottom: 2rem;
        }

        .faq-item {
            border-bottom: 1px solid var(--border-light);
            padding: 1rem 0;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .faq-answer {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .support-content {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .contact-methods, .contact-form-container {
                padding: 1.5rem;
            }

            .support-title {
                font-size: 1.6rem;
            }
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="support-container">
        <!-- Header -->
        <div class="support-header">
            <img src="{{ $ent->logo ? url('storage/' . $ent->logo) : asset('assets/8tech.png') }}" 
                 alt="{{ $ent->name }}" 
                 class="brand-logo">
            <h1 class="support-title">Need Help?</h1>
            <p class="support-subtitle">We're here to help you with {{ $ent->name }} school management system</p>
        </div>

        <!-- Main Content -->
        <div class="support-content">
            <!-- Contact Methods -->
            <div class="contact-methods">
                <h2 class="section-title">
                    <i class='bx bx-phone'></i>
                    Contact Us Directly
                </h2>

                <a href="tel:{{ Utils::get_support_phone() }}" class="contact-method">
                    <div class="contact-icon">
                        <i class='bx bx-phone'></i>
                    </div>
                    <div class="contact-info">
                        <h4>Phone Support</h4>
                        <p>{{ Utils::get_support_phone() }}</p>
                    </div>
                </a>

                <a href="mailto:{{ Utils::get_support_email() }}" class="contact-method">
                    <div class="contact-icon">
                        <i class='bx bx-envelope'></i>
                    </div>
                    <div class="contact-info">
                        <h4>Email Support</h4>
                        <p>{{ Utils::get_support_email() }}</p>
                    </div>
                </a>

                <a href="{{ Utils::get_whatsapp_link() }}" target="_blank" class="contact-method">
                    <div class="contact-icon">
                        <i class='bx bxl-whatsapp'></i>
                    </div>
                    <div class="contact-info">
                        <h4>WhatsApp Support</h4>
                        <p>Chat with us instantly</p>
                    </div>
                </a>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-container">
                <h2 class="section-title">
                    <i class='bx bx-message-detail'></i>
                    Send us a Message
                </h2>

                @if (session('status'))
                    <div class="alert alert-success">
                        <i class='bx bx-check-circle'></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class='bx bx-error-circle'></i>
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                <form id="supportForm" action="{{ admin_url('auth/support') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Your Name</label>
                        <input type="text" 
                               name="name" 
                               class="form-control" 
                               placeholder="Enter your full name"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="Enter your email address"
                               value="{{ old('email') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" 
                               name="subject" 
                               class="form-control" 
                               placeholder="What do you need help with?"
                               value="{{ old('subject') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" 
                                  class="form-control" 
                                  rows="5" 
                                  placeholder="Describe your issue or question in detail..."
                                  required>{{ old('message') }}</textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="supportBtn">
                            <span class="btn-text">Send Message</span>
                            <span class="spinner d-none"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2 class="section-title">
                <i class='bx bx-help-circle'></i>
                Frequently Asked Questions
            </h2>

            <div class="faq-item">
                <div class="faq-question">I forgot my password. How can I reset it?</div>
                <div class="faq-answer">
                    Click on the "Forgot Password?" link on the login page. Enter your email, phone number, or username, and we'll send you a password reset link.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">I can't access my account. What should I do?</div>
                <div class="faq-answer">
                    First, try resetting your password. If that doesn't work, contact our support team using the contact methods above. Have your account details ready for faster assistance.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">How do I get training on the system?</div>
                <div class="faq-answer">
                    We provide comprehensive training for all new users. Contact our support team to schedule a training session for your school staff.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Is there a user manual available?</div>
                <div class="faq-answer">
                    Yes! Once you log in, you'll find detailed documentation and video tutorials in the help section of your dashboard.
                </div>
            </div>
        </div>

        <!-- Back to Login -->
        <div style="text-align: center;">
            <a href="{{ admin_url('auth/login') }}" class="btn btn-secondary" style="max-width: 300px;">
                <i class='bx bx-arrow-back'></i>
                Back to Sign In
            </a>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Form submission handling
            $('#supportForm').on('submit', function() {
                const $form = $(this);
                const $btn = $('#supportBtn');
                const $btnText = $btn.find('.btn-text');
                const $spinner = $btn.find('.spinner');

                // Show loading state
                $btn.addClass('loading');
                $btnText.text('Sending...');
                $spinner.removeClass('d-none');
                
                // Disable form
                $form.find('input, textarea, button').prop('disabled', true);
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);

            // Focus first input
            $('input[name="name"]').focus();
        });
    </script>
</body>
</html>