<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reply to Your Support Request</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .header { background: #007bff; color: white; padding: 20px; margin: -30px -30px 30px -30px; border-radius: 8px 8px 0 0; }
        .content { line-height: 1.6; }
        .reply-box { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
        .original-message { background: #f8f9fa; padding: 15px; border-left: 4px solid #6c757d; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Reply to Your Support Request</h2>
        </div>
        
        <div class="content">
            <p>Dear {{ $name }},</p>
            
            <p>We have responded to your support request. Please find our reply below:</p>
            
            <p><strong>Ticket ID:</strong> #{{ $ticket_id }}</p>
            <p><strong>Original Subject:</strong> {{ $original_subject }}</p>
            
            <div class="reply-box">
                <h4>Our Reply:</h4>
                <p>{{ $reply }}</p>
            </div>
            
            <div class="original-message">
                <h4>Your Original Message:</h4>
                <p>{{ $original_message }}</p>
            </div>
            
            <p>If you have any additional questions or need further assistance, please feel free to contact us again. When responding, please include your ticket ID (#{{ $ticket_id }}) for faster service.</p>
            
            <p>Thank you for choosing our services!</p>
            
            <p>Best regards,<br>
            Support Team</p>
        </div>
        
        <div class="footer">
            <p>This email was sent in response to your support request.</p>
            <p>If you need further assistance, please contact our support team.</p>
        </div>
    </div>
</body>
</html>