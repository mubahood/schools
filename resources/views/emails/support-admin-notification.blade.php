<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Support Message</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .header { background: #007bff; color: white; padding: 20px; margin: -30px -30px 30px -30px; border-radius: 8px 8px 0 0; }
        .content { line-height: 1.6; }
        .message-box { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Support Message Received</h2>
        </div>
        
        <div class="content">
            <p><strong>A new support message has been submitted:</strong></p>
            
            <table style="width: 100%; margin: 20px 0;">
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Name:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Email:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $email }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Subject:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $subject }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>IP Address:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $ip_address }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Date:</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $created_at }}</td>
                </tr>
            </table>
            
            <div class="message-box">
                <h4>Message:</h4>
                <p>{{ $message }}</p>
            </div>
            
            <p>Please respond to this message as soon as possible.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from the school management system.</p>
        </div>
    </div>
</body>
</html>