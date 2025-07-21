<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agent Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="mb-4">You are logged in as an <strong>Agent</strong>.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-orange-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-orange-800">My Assigned Tickets</h4>
                            <p class="text-orange-600 text-sm">View tickets assigned to you</p>
                            <a href="#" class="text-orange-800 hover:text-orange-900 text-sm font-medium">View Tickets →</a>
                        </div>
                        
                        <div class="bg-red-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-800">Pending Tickets</h4>
                            <p class="text-red-600 text-sm">Tickets waiting for response</p>
                            <a href="#" class="text-red-800 hover:text-red-900 text-sm font-medium">View Pending →</a>
                        </div>
                        
                        <div class="bg-green-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800">Knowledge Base</h4>
                            <p class="text-green-600 text-sm">Access support resources</p>
                            <a href="#" class="text-green-800 hover:text-green-900 text-sm font-medium">Browse KB →</a>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold mb-4">Recent Activity</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-600">No recent activity to display.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
