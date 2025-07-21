<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="mb-4">You are logged in as an <strong>Administrator</strong>.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800">User Management</h4>
                            <p class="text-blue-600 text-sm">Manage users, roles, and permissions</p>
                            <a href="{{ route('admin.users.index') }}" class="text-blue-800 hover:text-blue-900 text-sm font-medium">Manage Users →</a>
                        </div>
                        
                        <div class="bg-green-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800">Ticket Overview</h4>
                            <p class="text-green-600 text-sm">View all tickets and analytics</p>
                            <a href="#" class="text-green-800 hover:text-green-900 text-sm font-medium">View Tickets →</a>
                        </div>
                        
                        <div class="bg-purple-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-800">API Integration</h4>
                            <p class="text-purple-600 text-sm">Connect to ticket platforms</p>
                            <a href="{{ route('ticket-api.index') }}" class="text-purple-800 hover:text-purple-900 text-sm font-medium">Manage APIs →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
