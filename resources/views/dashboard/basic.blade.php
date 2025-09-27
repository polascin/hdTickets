@extends('layouts.app-v2')
@section('title', 'Basic Dashboard')
@section('content')
  <div class="space-y-6">
    <div class="flex justify-between items-center">
    </div>
  </div>
@endsection
</div>
<div class="text-sm text-slate-600 dark:text-slate-300">
  Welcome, {{ Auth::user()->name }}
</div>
</div>
<!-- Welcome Banner -->
<div
  class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
  <!-- Background Pattern -->
  <div class="absolute inset-0 opacity-10">
    <div class="absolute top-0 left-0 w-full h-full">
      <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
        <defs>
          <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5" />
          </pattern>
        </defs>
        <rect width="100" height="100" fill="url(#grid)" />
      </svg>
    </div>
  </div>

  <div class="relative z-10">
    <div class="flex items-center mb-6">
      <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-6">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
        </svg>
      </div>
      <div>
        <h3 class="text-4xl font-bold mb-2">Hello, {{ Auth::user()->name }}!</h3>
        <p class="text-blue-100 text-lg">Welcome to our Sports Ticket Information Portal</p>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z"></path>
        </svg>
        {{ now()->format('l, F j, Y') }}
      </div>
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="currentTime">{{ now()->format('H:i:s') }}</span>
      </div>
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        {{ ucfirst(Auth::user()->role) }} User
      </div>
    </div>
  </div>
</div>

<!-- Information Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <!-- Sports Events Info -->
  <div
    class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
    <div class="p-6">
      <div class="flex items-center mb-4">
        <div
          class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
            </path>
          </svg>
        </div>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Sports Events</h3>
      <p class="text-gray-600 text-sm mb-4">Stay updated with the latest sports events and ticket availability
        information.</p>
      <div class="flex items-center text-green-600 text-sm font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Information Portal
      </div>
    </div>
  </div>

  <!-- Account Information -->
  <div
    class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
    <div class="p-6">
      <div class="flex items-center mb-4">
        <div
          class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
        </div>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Your Account</h3>
      <p class="text-gray-600 text-sm mb-4">Manage your profile settings and account preferences.</p>
      <a href="{{ route('profile.edit') }}"
        class="inline-flex items-center text-blue-600 text-sm font-medium hover:text-blue-700">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
          </path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
          </path>
        </svg>
        Manage Profile
      </a>
    </div>
  </div>

  <!-- Contact Support -->
  <div
    class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
    <div class="p-6">
      <div class="flex items-center mb-4">
        <div
          class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
            </path>
          </svg>
        </div>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Need Help?</h3>
      <p class="text-gray-600 text-sm mb-4">Contact our support team for assistance with your account or questions.</p>
      <div class="flex items-center text-purple-600 text-sm font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
          </path>
        </svg>
        support@sportstickets.com
      </div>
    </div>
  </div>
</div>

<!-- User Information Panel -->
<div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
  <div class="p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <dl class="space-y-3">
          <div>
            <dt class="text-sm font-medium text-gray-500">Full Name</dt>
            <dd class="text-sm text-gray-900">{{ Auth::user()->name }} {{ Auth::user()->surname ?? '' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Email Address</dt>
            <dd class="text-sm text-gray-900">{{ Auth::user()->email }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Username</dt>
            <dd class="text-sm text-gray-900">{{ Auth::user()->username ?? 'Not set' }}</dd>
          </div>
        </dl>
      </div>
      <div>
        <dl class="space-y-3">
          <div>
            <dt class="text-sm font-medium text-gray-500">Account Role</dt>
            <dd class="text-sm text-gray-900">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                {{ ucfirst(Auth::user()->role) }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Account Status</dt>
            <dd class="text-sm text-gray-900">
              <span
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ Auth::user()->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ Auth::user()->is_active ? 'Active' : 'Inactive' }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Member Since</dt>
            <dd class="text-sm text-gray-900">{{ Auth::user()->created_at->format('F j, Y') }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</div>

<!-- Access Notice -->
<div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
  <div class="flex items-center">
    <div class="flex-shrink-0">
      <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd"
          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
          clip-rule="evenodd"></path>
      </svg>
    </div>
    <div class="ml-3">
      <h3 class="text-sm font-medium text-amber-800">Limited Access Notice</h3>
      <div class="mt-2 text-sm text-amber-700">
        <p>Your current account role ({{ ucfirst(Auth::user()->role) }}) provides access to basic account information
          and settings. For additional features and administrative access, please contact your system administrator.</p>
      </div>
    </div>
  </div>
</div>
</div>
</div>

<script>
  // Update time display
  function updateTime() {
    const now = new Date();
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
      timeElement.textContent = now.toLocaleTimeString();
    }
  }

  // Update time every second
  setInterval(updateTime, 1000);

  // Add smooth scrolling
  document.documentElement.style.scrollBehavior = 'smooth';
</script>
@endsection
