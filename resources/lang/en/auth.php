<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed'   => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'verify' => [
        'title'               => 'Verify Your Email - HD Tickets',
        'heading'             => 'Verify Your Email Address',
        'instructions'        => 'We have sent a verification link to :email. To continue with HD Tickets, please verify your email address. If it has not arrived, you can request another below.',
        'instructions_tips'   => 'Please check your inbox and spam folder. Email delivery can take up to 5 minutes.',
        'resend_button'       => 'Resend Verification Email',
        'resend_available_in' => 'Resend available in',
        'link_sent'           => 'A new verification link has been sent. Please check your inbox and spam folder.',
        'back_to_sign_in'     => 'Back to Sign In',
        'change_email'        => 'Change Email Address',
        'log_out'             => 'Log Out',
        'too_many_requests'   => 'You have requested too many emails. Please wait a moment and try again.',
        'email_security'      => 'For your security, this email is masked.',
        'troubleshoot_title'  => 'Not Receiving Emails?',
        'troubleshoot_items'  => [
            'Check your spam or junk folder',
            'Ensure your email address is correct',
            'Email delivery can take up to 5 minutes',
            'Try adding no-reply@hdtickets.local to your contacts',
        ],
    ],
];
