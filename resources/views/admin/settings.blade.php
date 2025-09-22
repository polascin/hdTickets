@extends('layouts.admin')

@section('title', 'System Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    {{-- Include the admin system configuration component --}}
    @include('components.admin-system-config')
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
@endsection