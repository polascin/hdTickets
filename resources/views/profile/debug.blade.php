<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Debug Profile View
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3>User Information:</h3>
                    <p><strong>ID:</strong> {{ $user->id }}</p>
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Role:</strong> {{ $user->role }}</p>
                    
                    @php
                        try {
                            $userInfo = $user->getEnhancedUserInfo();
                            echo "<h4>Enhanced User Info Retrieved Successfully</h4>";
                            echo "<pre>" . print_r(array_keys($userInfo), true) . "</pre>";
                        } catch (Exception $e) {
                            echo "<h4>Error getting enhanced user info:</h4>";
                            echo "<p>" . $e->getMessage() . "</p>";
                        }
                    @endphp
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
