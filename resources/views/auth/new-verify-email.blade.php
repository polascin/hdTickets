@extends('layouts.guest-v3')

@section('title', 'Verify Your Email Address')
@section('description', 'Confirm your HD Tickets email address to finish onboarding and unlock your dashboard.')

@section('content')
  @php
    $user = auth()->user();
    $emailAddress = $user?->email ?? 'your email address';
    $pendingFor = null;

    if (
        $user instanceof \App\Models\User &&
        $user->email_verified_at === null &&
        $user->created_at instanceof \Carbon\CarbonInterface
    ) {
        $pendingFor = $user->created_at->diffForHumans();
    }
  @endphp

  <div class="min-h-screen flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-2xl space-y-8">
      <div class="text-center space-y-3">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
          <svg class="h-7 w-7 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Check your inbox</h1>
        <p class="text-sm text-gray-600 dark:text-gray-300">
          We sent a verification link to <span
            class="font-semibold text-gray-900 dark:text-gray-100">{{ $emailAddress }}</span>.
          Click the button in that email to finish setting up your account.
        </p>
        @if ($pendingFor)
          <p class="text-xs text-blue-600 dark:text-blue-300">Waiting for confirmation since {{ $pendingFor }}.</p>
        @endif
      </div>

      @if (session('status') === 'verification-link-sent')
        <div
          class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900 dark:bg-green-900/40 dark:text-green-200">
          A new verification email is on its way. It may take up to two minutes to arrive.
        </div>
      @endif

      <div class="card space-y-6 p-8">
        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
          <p class="font-semibold text-gray-900 dark:text-gray-100">Quick reminders</p>
          <ul class="space-y-2 list-disc pl-5">
            <li>Look for an email from <strong>support@hd-tickets.com</strong>.</li>
            <li>Check spam or promotions folders if you do not see it in your inbox.</li>
            <li>Each verification link expires after one hour for security.</li>
          </ul>
        </div>

        <form method="POST" action="{{ route('verification.send') }}" class="space-y-2">
          @csrf
          <button type="submit" class="btn btn-primary w-full">Resend verification email</button>
          <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Limit: six requests per minute.</p>
        </form>

        <div
          class="flex flex-col gap-3 text-sm text-gray-600 dark:text-gray-300 sm:flex-row sm:items-center sm:justify-between">
          <a href="{{ route('profile.edit') }}"
            class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
            Update email address
          </a>
          <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-900 dark:hover:text-gray-100">I will verify
              later</a>
            <span class="hidden sm:inline text-gray-400 dark:text-gray-600">•</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
              @csrf
              <button type="submit" class="hover:text-red-500 dark:hover:text-red-400">Sign out</button>
            </form>
          </div>
        </div>
      </div>

      <div class="text-center text-xs text-gray-500 dark:text-gray-400">
        Need help? <a href="mailto:support@hd-tickets.com"
          class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Contact
          support</a>
      </div>
    </div>
  </div>
@endsection
