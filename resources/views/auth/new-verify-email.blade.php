@extends('layouts.guest-v3')

@section('title', 'Verify your email address')
@section('description', 'Please confirm your email to start tracking your favourite events.')
@section('suppress-chrome', true)

@section('content')
  <div class="max-w-md mx-auto px-4 sm:px-6 py-8">
    @if (session('status') === 'verification-link-sent')
      <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status" aria-live="polite">
        A new verification email has been sent. It should arrive within a couple of minutes.
      </div>
    @endif

    @if (session('error'))
      <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700" role="alert" aria-live="assertive">
        {{ session('error') }}
      </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 sm:p-8">
      <h1 class="text-xl font-semibold text-gray-900">
        Verify your email address
      </h1>

      <p class="mt-2 text-sm text-gray-600">
        We’ve sent a verification link to
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

        <div class="text-center text-sm">
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="font-medium text-gray-600 hover:text-gray-700">
              Sign out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
