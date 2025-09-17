<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Message Received Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .header { background: #28a745; color: white; padding: 20px; margin: -30px -30px 30px -30px; border-radius: 8px 8px 0 0; }
        .content { line-height: 1.6; }
        .ticket-info { background: #e9f7ef; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .message-summary { background: #f8f9fa; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>We Received Your Message!</h2>
        </div>
        
        <div class="content">
            <p>Dear {{ $name }},</p>
            
            <p>Thank you for contacting our support team. We have successfully received your message and will respond within 24 hours.</p>
            
            <div class="ticket-info">
                <h4>Your Support Ticket Details:</h4>
                <p><strong>Ticket ID:</strong> #{{ $ticket_id }}</p>
                <p><strong>Subject:</strong> {{ $subject }}</p>
                <p><strong>Date Submitted:</strong> {{ $created_at }}</p>
            </div>
            
            <div class="message-summary">
                <h4>Your Message:</h4>
                <p>{{ $message }}</p>
            </div>
            
            <p>Please keep your ticket ID (#{{ $ticket_id }}) for reference. If you need to follow up on this request, please include this ticket ID in your communication.</p>
            
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Our support team will review your message</li>
                <li>You'll receive a detailed response within 24 hours</li>
                <li>If urgent, you can call our support line directly</li>
            </ul>
            
            <p>Thank you for your patience!</p>
            
            <p>Best regards,<br>
            Support Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated confirmation email. Please do not reply to this email.</p>
            <p>If you need immediate assistance, please contact our support team directly.</p>
        </div>
    </div>
</body>
</html>