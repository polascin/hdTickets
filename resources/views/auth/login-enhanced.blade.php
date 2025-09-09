{{-- Enhanced HD Tickets Login Page with Advanced Security --}}
<x-guest-layout>
    {{-- Enhanced Security Headers --}}
    <meta name="security-policy" content="enhanced-login-v3">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Live Regions for Screen Reader Announcements --}}
    <div id="hd-status-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>
    <div id="hd-alert-region" class="sr-only" aria-live="assertive" aria-atomic="true"></div>


    {{-- Enhanced Modern Login Form Component --}}
    <x-auth.login-form 
        title="Enhanced Security Login"
        subtitle="Advanced protection for your HD Tickets account"
        :show-remember-me="true"
        :show-forgot-password="true"
        :show-security-badge="true"
        :show-registration-links="true"
        registration_style="compact"
    />
</x-guest-layout>
