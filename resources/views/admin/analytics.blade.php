@extends('layouts.app-v2')

@section('title', 'Analytics Dashboard')

@section('content')
  <div class="min-h-screen bg-gray-50 p-6">
    {{-- Include the admin analytics dashboard component --}}
    @include('components.admin-analytics-dashboard')
  </div>

@endsection
