@extends('layouts.guest-v3')

@section('title', 'Verify Your Email Address')
@section('description',
  'Secure your HD Tickets account by confirming your email address. Resend the link and get
  troubleshooting guidance if the message has not arrived yet.')

@section('content')
  @php
    $user = auth()->user();
    $emailAddress = $email ?? ($user?->email ?? 'your email');
    $name = $name ?? ($user?->name ?? 'there');
    $joinDate = isset($createdAt) && $createdAt instanceof \Carbon\CarbonInterface ? $createdAt : null;
    $waitingSince =
        isset($pendingSince) && $pendingSince instanceof \Carbon\CarbonInterface ? $pendingSince : $joinDate;
  @endphp

  <section class="relative overflow-hidden pb-16">
    <div
      class="absolute inset-0 bg-gradient-to-br from-blue-100/60 via-transparent to-purple-100/40 dark:from-blue-900/30 dark:via-gray-900/20 dark:to-purple-900/30 pointer-events-none">
    </div>
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-16">
      <div class="grid lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2 space-y-8">
          <div
            class="rounded-3xl bg-white/90 dark:bg-gray-900/80 shadow-xl ring-1 ring-blue-100/70 dark:ring-blue-900/40 backdrop-blur-sm p-8 sm:p-12">
            <div class="flex items-start gap-4">
              <div
                class="h-14 w-14 flex items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/30">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 7.5l8.485 5.091a1.5 1.5 0 001.53 0L21 7.5M5.25 18.75h13.5A1.25 1.25 0 0020 17.5v-11A1.25 1.25 0 0018.75 5.25H5.25A1.25 1.25 0 004 6.5v11a1.25 1.25 0 001.25 1.25z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">Email
                  verification pending</p>
                <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Hi {{ $name }},
                  confirm your email to unlock HD Tickets</h1>
                <p class="mt-3 text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                  We sent a secure verification link to <span
                    class="font-semibold text-gray-900 dark:text-white">{{ $emailAddress }}</span>.
                  Click the link in the message to activate full access to live monitoring, smart alerts, and automated
                  purchases.
                </p>
                @if ($waitingSince)
                  <p class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                    Waiting for confirmation since {{ $waitingSince->diffForHumans() }}.
                  </p>
                @endif
              </div>
            </div>

            @if (session('status') === 'verification-link-sent')
              <div
                class="mt-6 rounded-2xl border border-green-200 bg-green-50/80 dark:border-green-900 dark:bg-green-900/40 px-5 py-4 text-sm text-green-700 dark:text-green-200">
                <div class="flex items-center gap-3">
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-7.39 7.39a1 1 0 01-1.414 0L3.293 9.49a1 1 0 011.414-1.414l3.196 3.197 6.683-6.683a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
                  </svg>
                  <span>We just sent another verification email. It can take up to two minutes to arrive.</span>
                </div>
              </div>
            @endif

            <div class="mt-8 grid gap-6 md:grid-cols-2">
              <div
                class="rounded-2xl border border-gray-200/70 dark:border-gray-700/70 bg-white/70 dark:bg-gray-900/60 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Quick checklist</h2>
                <ul class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                  <li class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-blue-500"></span>
                    <span>Open the latest email from <strong class="font-semibold">support@hd-tickets.com</strong> and tap
                      the blue “Verify Email” button.</span>
                  </li>
                  <li class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-blue-500"></span>
                    <span>Check spam, promotions, or “Other” folders — some corporate filters move verification emails
                      automatically.</span>
                  </li>
                  <li class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-blue-500"></span>
                    <span>Confirm that typo-free email appears above. If it is incorrect, sign out and register again with
                      the right address.</span>
                  </li>
                </ul>
              </div>

              <div
                class="rounded-2xl border border-blue-200/70 dark:border-blue-900/60 bg-gradient-to-br from-blue-50/70 to-indigo-50/70 dark:from-blue-900/60 dark:to-indigo-900/40 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Need another link?</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">We can resend it instantly. Each new link
                  replaces the previous one for security.</p>
                <form method="POST" action="{{ route('verification.send') }}"
                  class="mt-4 flex flex-col sm:flex-row gap-3">
                  @csrf
                  <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-blue-500/30 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                    Resend verification email
                  </button>
                  <span class="text-xs text-gray-500 dark:text-gray-400 sm:ml-2 sm:flex sm:items-center">Limit six
                    requests per minute</span>
                </form>
              </div>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
              <div
                class="rounded-2xl border border-gray-200/70 dark:border-gray-700/70 bg-white/70 dark:bg-gray-900/60 p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Account summary</h3>
                <dl class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                  <div class="flex justify-between">
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Registered name</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $name }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Primary email</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $emailAddress }}</dd>
                  </div>
                  @if ($joinDate)
                    <div class="flex justify-between">
                      <dt class="font-medium text-gray-500 dark:text-gray-400">Joined</dt>
                      <dd class="text-gray-900 dark:text-gray-100">{{ $joinDate->format('M j, Y') }}</dd>
                    </div>
                  @endif
                </dl>
              </div>

              <div
                class="rounded-2xl border border-gray-200/70 dark:border-gray-700/70 bg-white/70 dark:bg-gray-900/60 p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Troubleshooting tips</h3>
                <ul class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                  <li class="flex items-start gap-3">
                    <svg class="mt-0.5 h-4 w-4 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M5.05 4.05A7 7 0 1110 17a7 7 0 01-4.95-12.95zM9 6h2v5H9V6zm0 6h2v2H9v-2z" />
                    </svg>
                    <span>Your organization may filter security emails. Ask IT to allow <code
                        class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">hd-tickets.com</code>.</span>
                  </li>
                  <li class="flex items-start gap-3">
                    <svg class="mt-0.5 h-4 w-4 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M5.05 4.05A7 7 0 1110 17a7 7 0 01-4.95-12.95zM11 9V5H9v6h4V9h-2z" />
                    </svg>
                    <span>Links expire after 60 minutes. If the last message is older, request a fresh one above.</span>
                  </li>
                  <li class="flex items-start gap-3">
                    <svg class="mt-0.5 h-4 w-4 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        d="M2.166 12.91l7.778 4.489a1 1 0 00.965 0l7.778-4.489a1 1 0 00.505-.87v-4.88a1 1 0 00-.505-.87L10.909 2.67a1 1 0 00-.965 0L2.166 6.29a1 1 0 00-.505.87v4.88a1 1 0 00.505.87z" />
                    </svg>
                    <span>Still waiting? Contact <a href="mailto:support@hd-tickets.com"
                        class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">support@hd-tickets.com</a>
                      and we will confirm manually.</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="mt-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <a href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('verify-email-logout-form').submit();"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300/80 bg-white/70 px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:border-gray-400 dark:border-gray-700/80 dark:bg-gray-900/60 dark:text-gray-300 dark:hover:text-white transition">
                Use a different email
              </a>
              <form method="POST" action="{{ route('logout') }}" class="hidden" id="verify-email-logout-form">
                @csrf
              </form>
              <a href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-indigo-500/40 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                I will verify later →
              </a>
            </div>
          </div>
        </div>

        <aside class="space-y-6">
          <div
            class="rounded-3xl bg-white/80 dark:bg-gray-900/70 shadow-lg ring-1 ring-gray-200/70 dark:ring-gray-800/70 backdrop-blur-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">What happens after verification?</h2>
            <ul class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
              <li class="flex items-start gap-3">
                <span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span>
                <span>Unlock full dashboard with live market feeds and predictive analytics.</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span>
                <span>Enable smart alerts on price drops, sell-outs, and verified resellers.</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span>
                <span>Complete payment profile setup and access automated purchasing workflows.</span>
              </li>
            </ul>
          </div>

          <div
            class="rounded-3xl border border-blue-200/70 dark:border-blue-900/50 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 text-white p-6">
            <h2 class="text-lg font-semibold">Need help right now?</h2>
            <p class="mt-3 text-sm text-blue-50">
              Our onboarding specialists are available Monday–Friday, 8am–8pm ET. Reply to the verification email or reach
              out using the channels below.
            </p>
            <div class="mt-5 space-y-3 text-sm">
              <a href="mailto:support@hd-tickets.com"
                class="flex items-center gap-3 text-white/90 hover:text-white transition">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                support@hd-tickets.com
              </a>
              <a href="tel:+18882684353" class="flex items-center gap-3 text-white/90 hover:text-white transition">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 5a2 2 0 012-2h2.153a1 1 0 01.986.836l.764 4.584a1 1 0 01-.27.87l-1.477 1.478a16.017 16.017 0 006.364 6.364l1.478-1.477a1 1 0 01.87-.27l4.584.764a1 1 0 01.836.986V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                +1 (888) 268-4353
              </a>
              <a href="https://hd-tickets.com/legal/privacy-policy"
                class="flex items-center gap-3 text-white/90 hover:text-white transition">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 6l-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V8l-6-6H9" />
                </svg>
                View security & privacy commitments
              </a>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>
@endsection
