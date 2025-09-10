HD TICKETS - PASSWORD CHANGED
=====================================

Hello {{ $user->full_name ?: $user->username }},

This is to confirm that your password for your HD Tickets account ({{ $user->email }}) was successfully changed.

*** SECURITY ALERT ***
If you did not make this change, please contact our support team immediately and secure your account.

CHANGE DETAILS:
- Date & Time: {{ $changeDetails['changed_at']->format('M j, Y \a\t g:i A T') }}
- Location: {{ $changeDetails['location'] }}
- IP Address: {{ $changeDetails['ip_address'] }}
- Device: {{ $changeDetails['user_agent'] }}

SECURITY TIPS:
• Use a unique, strong password for your HD Tickets account
• Enable two-factor authentication for additional security
• Never share your password with anyone
• Log out when using shared or public computers

WHAT SHOULD YOU DO NOW?
• If you made this change: No further action is needed
• If you didn't make this change: Secure your account immediately

Review your security settings: {{ route('profile.security') }}

If you have any questions or concerns about your account security, please don't hesitate to contact our support team.

Thank you for keeping your account secure!

Best regards,
The HD Tickets Security Team

---
This is an automated security notification. Please do not reply to this email.
If you need assistance, please contact us through our support channels.
© {{ date('Y') }} HD Tickets. All rights reserved.
