{{-- Modern HD Tickets 2FA Challenge Page --}}
<x-guest-layout>
    {{-- Live Regions for Screen Reader Announcements --}}
    <div id="hd-status-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>
    <div id="hd-alert-region" class="sr-only" aria-live="assertive" aria-atomic="true"></div>

    {{-- Modern 2FA Challenge Component --}}
    <x-auth.two-factor-challenge 
        title="Two-Factor Authentication"
        subtitle="Enter your authentication code to continue securely"
        :show-backup-options="true"
    />
</x-guest-layout>
