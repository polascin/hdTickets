@props(['user'])

@php
    $completion = $user->getProfileCompletion();
    $statusColors = [
        'incomplete' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'bar' => 'bg-red-500'],
        'fair' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'bar' => 'bg-yellow-500'],
        'good' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'bar' => 'bg-blue-500'],
        'excellent' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'bar' => 'bg-green-500'],
    ];
    $colors = $statusColors[$completion['status']];
@endphp

<div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
            Profile Completion
        </h3>
        <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full {{ $colors['bg'] }} {{ $colors['text'] }}">
            {{ $completion['percentage'] }}% Complete
        </span>
    </div>
    
    <!-- Progress Bar -->
    <div class="mb-4">
        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
            <span>{{ $completion['completed_count'] }} of {{ $completion['total_fields'] }} fields completed</span>
            <span class="font-medium capitalize">{{ $completion['status'] }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="h-3 rounded-full {{ $colors['bar'] }} transition-all duration-300 ease-in-out" 
                 style="width: {{ $completion['percentage'] }}%"></div>
        </div>
    </div>
    
    @if($completion['percentage'] < 100)
    <!-- Missing Fields -->
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-gray-700">Complete your profile by adding:</h4>
        <div class="grid grid-cols-2 gap-3">
            @foreach($completion['missing_fields'] as $field)
                @php
                    $fieldLabels = [
                        'name' => 'First Name',
                        'surname' => 'Last Name',
                        'phone' => 'Phone Number',
                        'bio' => 'Personal Bio',
                        'profile_picture' => 'Profile Picture',
                        'timezone' => 'Timezone',
                        'language' => 'Language',
                        'two_factor_enabled' => 'Two-Factor Authentication',
                    ];
                    $fieldIcons = [
                        'name' => 'user',
                        'surname' => 'user',
                        'phone' => 'phone',
                        'bio' => 'document-text',
                        'profile_picture' => 'photograph',
                        'timezone' => 'globe',
                        'language' => 'translate',
                        'two_factor_enabled' => 'shield-check',
                    ];
                    $fieldActions = [
                        'profile_picture' => 'Upload photo in Profile Picture section',
                        'two_factor_enabled' => 'Enable in Security Settings',
                        'default' => 'Fill out in form above'
                    ];
                @endphp
                
                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($fieldIcons[$field] === 'user')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            @elseif($fieldIcons[$field] === 'phone')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            @elseif($fieldIcons[$field] === 'document-text')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            @elseif($fieldIcons[$field] === 'photograph')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            @elseif($fieldIcons[$field] === 'globe')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @elseif($fieldIcons[$field] === 'translate')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                            @elseif($fieldIcons[$field] === 'shield-check')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            @endif
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $fieldLabels[$field] ?? ucfirst($field) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $fieldActions[$field] ?? $fieldActions['default'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @else
    <!-- Profile Complete -->
    <div class="text-center py-4">
        <div class="text-green-600">
            <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <h4 class="text-lg font-medium text-gray-900 mb-2">Profile Complete!</h4>
        <p class="text-sm text-gray-600">
            Your profile is fully completed. Great job on providing all the necessary information!
        </p>
    </div>
    @endif
</div>
