<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="mb-4">You are logged in as a <strong>Customer</strong>.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-yellow-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-yellow-800">My Tickets</h4>
                            <p class="text-yellow-600 text-sm">View and manage your support tickets</p>
                            <a href="#" class="text-yellow-800 hover:text-yellow-900 text-sm font-medium">View My Tickets →</a>
                        </div>
                        
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800">New Ticket</h4>
                            <p class="text-blue-600 text-sm">Submit a new support request</p>
                            <a href="#" class="text-blue-800 hover:text-blue-900 text-sm font-medium">Create Ticket →</a>
                        </div>
                        
                        <div class="bg-green-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800">Support Articles</h4>
                            <p class="text-green-600 text-sm">Browse our knowledge base</p>
                            <a href="#" class="text-green-800 hover:text-green-900 text-sm font-medium">Browse Articles →</a>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-lg font-semibold mb-4">Recent Tickets</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-600">No recent tickets to display.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
