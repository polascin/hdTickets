@extends('layouts.guest-v3')

@section('title', 'Verify your email address')
@section('description', 'Please confirm your email to start tracking your favourite events.')

@section('content')
  <div class="max-w-md mx-auto px-4 sm:px-6 py-8">
    @if (session('status') === 'verification-link-sent')
      <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status" aria-live="polite">
        A new verification email has been sent. It should arrive within a couple of minutes.
      </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 sm:p-8">
      <h1 class="text-xl font-semibold text-gray-900">
        Verify your email address
      </h1>

      <p class="mt-2 text-sm text-gray-600">
        We've sent a verification link to
        <span class="font-medium text-gray-900">{{ auth()->user()->email }}</span>.
        Please confirm to start tracking your favourite events.
      </p>

      <div class="mt-6 space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
          @csrf
          <button type="submit" class="btn btn-primary w-full">
            Resend verification email
          </button>
        </form>

        <div class="flex items-center justify-between text-sm">
          <a href="{{ route('profile.edit') }}" class="font-medium text-purple-600 hover:text-purple-700">
            Update email address
          </a>

          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="font-medium text-gray-600 hover:text-gray-700">
              Sign out
            </button>
          </form>
        </div>

        <p class="text-center text-xs text-gray-500">
          Need help? <a href="mailto:support@hd-tickets.com" class="font-medium text-purple-600 hover:text-purple-700">Contact support</a>
        </p>
      </div>
    </div>
  </div>
@endsection
