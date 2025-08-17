@extends('layouts.app')

@section('title', 'Dashboard Test')

@section('content')
<div class="dashboard-container">
    <h1 class="text-3xl font-bold mb-6">Navigation and Dashboard Test</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- CSS Loading Test -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">CSS Loading Test</h2>
            <div class="space-y-3">
                <p>If navigation icons are small, our CSS is working ✓</p>
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p>This icon should be 16px (w-4 h-4)</p>
            </div>
        </div>

        <!-- Route Test -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Route Test</h2>
            <div class="space-y-2">
                <a href="{{ route('dashboard') }}" class="block text-blue-600 hover:underline">Dashboard Route</a>
                <a href="{{ route('tickets.scraping.index') }}" class="block text-blue-600 hover:underline">Tickets Route</a>
                <a href="{{ route('tickets.alerts.index') }}" class="block text-blue-600 hover:underline">Alerts Route</a>
                <a href="{{ route('purchase-decisions.index') }}" class="block text-blue-600 hover:underline">Purchase Decisions Route</a>
            </div>
        </div>

        <!-- User Info Test -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">User Info Test</h2>
            @auth
                <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                <p><strong>Role:</strong> {{ Auth::user()->role ?? 'No role' }}</p>
                <p><strong>Is Admin:</strong> {{ Auth::user()->isAdmin() ? 'Yes' : 'No' }}</p>
                <p><strong>Is Customer:</strong> {{ Auth::user()->isCustomer() ? 'Yes' : 'No' }}</p>
            @else
                <p>Not authenticated</p>
            @endauth
        </div>

        <!-- Alpine.js Test -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Alpine.js Test</h2>
            <div x-data="{ open: false }">
                <button @click="open = !open" class="bg-blue-500 text-white px-4 py-2 rounded">
                    Toggle Dropdown
                </button>
                <div x-show="open" x-transition class="mt-2 p-4 bg-gray-100 rounded">
                    <p>Alpine.js is working! ✓</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include our dashboard CSS -->
<link href="{{ css_with_timestamp('css/customer-dashboard.css') }}" rel="stylesheet">

@endsection
