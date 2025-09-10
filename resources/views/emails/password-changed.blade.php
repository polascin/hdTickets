<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed - HD Tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #1f2937;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 5px 5px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .alert {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #374151;
        }
        .value {
            color: #6b7280;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .security-tip {
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Password Changed</h1>
        <p>Your HD Tickets account password has been updated</p>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->full_name ?: $user->username }},</p>
        
        <p>This is to confirm that your password for your HD Tickets account ({{ $user->email }}) was successfully changed.</p>
        
        <div class="alert">
            <strong>⚠️ Security Alert:</strong> If you did not make this change, please contact our support team immediately and secure your account.
        </div>
        
        <div class="info-box">
            <h3>Change Details</h3>
            <div class="info-row">
                <span class="label">Date & Time:</span>
                <span class="value">{{ $changeDetails['changed_at']->format('M j, Y \a\t g:i A T') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Location:</span>
                <span class="value">{{ $changeDetails['location'] }}</span>
            </div>
            <div class="info-row">
                <span class="label">IP Address:</span>
                <span class="value">{{ $changeDetails['ip_address'] }}</span>
            </div>
            <div class="info-row">
                <span class="label">Device:</span>
                <span class="value">{{ $changeDetails['user_agent'] }}</span>
            </div>
        </div>
        
        <div class="security-tip">
            <h4>🛡️ Security Tips:</h4>
            <ul>
                <li>Use a unique, strong password for your HD Tickets account</li>
                <li>Enable two-factor authentication for additional security</li>
                <li>Never share your password with anyone</li>
                <li>Log out when using shared or public computers</li>
            </ul>
        </div>
        
        <p><strong>What should you do now?</strong></p>
        <ul>
            <li>If you made this change: No further action is needed</li>
            <li>If you didn't make this change: Secure your account immediately</li>
        </ul>
        
        <a href="{{ route('profile.security') }}" class="button">Review Security Settings</a>
        
        <p>If you have any questions or concerns about your account security, please don't hesitate to contact our support team.</p>
        
        <p>Thank you for keeping your account secure!</p>
        
        <p>Best regards,<br>
        The HD Tickets Security Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated security notification. Please do not reply to this email.</p>
        <p>If you need assistance, please contact us through our support channels.</p>
        <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
    </div>
</body>
</html>
