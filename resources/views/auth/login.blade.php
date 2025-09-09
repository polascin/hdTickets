{{-- Modern HD Tickets Login Page --}}
<x-guest-layout>
    {{-- Live Regions for Screen Reader Announcements --}}
    <div id="hd-status-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>
    <div id="hd-alert-region" class="sr-only" aria-live="assertive" aria-atomic="true"></div>

    {{-- Modern Login Form Component --}}
    <x-auth.login-form 
        title="Welcome back"
        subtitle="Sign in to access your HD Tickets sports events dashboard"
        :show-remember-me="true"
        :show-forgot-password="true"
        :show-security-badge="config('app.env') === 'production'"
        :show-registration-links="true"
        registration_style="full"
    />
</x-guest-layout>
