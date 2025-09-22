@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    {{-- Include the admin user management component --}}
    @include('components.admin-user-management')
</div>

@endsection