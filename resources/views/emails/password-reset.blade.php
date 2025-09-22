<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - HD Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .brand { font-size: 18px; font-weight: bold; color: #10B981; margin-bottom: 16px; }
        .panel { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; }
        .btn { display: inline-block; background: #10B981; color: #ffffff; padding: 10px 16px; border-radius: 6px; text-decoration: none; }
        .muted { color: #6b7280; font-size: 12px; }
        .danger { color: #b91c1c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">HD Tickets</div>
        <p>Hi {{ $user->name }},</p>

        <p>Your password has been reset by an administrator. Please use the temporary password below to sign in and then change your password immediately.</p>

        <div class="panel">
            <p><strong>Temporary Password:</strong></p>
            <p style="font-size: 18px; font-weight: bold; letter-spacing: 0.5px;">{{ $password }}</p>
        </div>

        <p style="margin-top: 16px;">
            <a class="btn" href="{{ config('app.url') }}/login">Sign in</a>
        </p>

        <p class="muted" style="margin-top: 16px;">
            For security, we recommend you:
        </p>
        <ul class="muted">
            <li>Change your password after you sign in</li>
            <li>Enable two-factor authentication (if available)</li>
            <li>Do not share this password with anyone</li>
        </ul>

        <p class="muted" style="margin-top: 16px;">
            If you did not request this reset, please contact support immediately at <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
        </p>

        <p class="muted">Thanks,<br/>The HD Tickets Team</p>
    </div>
</body>
</html>