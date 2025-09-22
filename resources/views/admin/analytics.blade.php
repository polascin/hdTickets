@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    {{-- Include the admin analytics dashboard component --}}
    @include('components.admin-analytics-dashboard')
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
@endsection