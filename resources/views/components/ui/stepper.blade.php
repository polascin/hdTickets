@props([
    'steps' => [],
    'currentStep' => 1,
    'theme' => 'stadium' // stadium, minimal, classic
])

<div {{ $attributes->merge(['class' => 'hd-stepper']) }}>
    <nav 
        role="list" 
        aria-label="Registration Steps" 
        class="flex items-center justify-center w-full max-w-md mx-auto px-4 sm:px-0"
        x-data="{ currentStep: {{ $currentStep }} }"
    >
        <ol class="flex items-center w-full space-x-2 sm:space-x-4">
            @foreach($steps as $index => $step)
                @php
                    $stepNumber = $index + 1;
                    $isActive = $stepNumber === $currentStep;
                    $isCompleted = $stepNumber < $currentStep;
                    $isUpcoming = $stepNumber > $currentStep;
                @endphp
                
                <li class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                    <!-- Step Circle -->
                    <div 
                        class="
                            relative flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 
                            rounded-full border-2 transition-all duration-300 ease-in-out
                            @if($isCompleted)
                                bg-success-600 border-success-600 text-white shadow-md
                            @elseif($isActive) 
                                bg-primary-600 border-primary-600 text-white shadow-lg ring-2 ring-primary-200 dark:ring-primary-800
                            @else
                                bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500
                            @endif
                        "
                        aria-current="{{ $isActive ? 'step' : 'false' }}"
                        aria-label="Step {{ $stepNumber }}: {{ $step['title'] }}"
                    >
                        @if($isCompleted)
                            <!-- Checkmark Icon -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <!-- Step Number -->
                            <span class="text-sm sm:text-base font-semibold">{{ $stepNumber }}</span>
                        @endif
                    </div>
                    
                    <!-- Step Connector Line -->
                    @if(!$loop->last)
                        <div 
                            class="
                                flex-1 h-0.5 ml-2 sm:ml-4 transition-colors duration-300
                                @if($stepNumber < $currentStep)
                                    bg-success-600
                                @else
                                    bg-gray-300 dark:bg-gray-600
                                @endif
                            "
                            aria-hidden="true"
                        ></div>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
    
    <!-- Step Labels (Mobile Hidden, Desktop Visible) -->
    <div class="hidden sm:flex justify-between items-center w-full max-w-md mx-auto mt-3 px-4">
        @foreach($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isActive = $stepNumber === $currentStep;
                $isCompleted = $stepNumber < $currentStep;
            @endphp
            
            <div class="flex-1 text-center">
                <p class="
                    text-xs font-medium transition-colors duration-300
                    @if($isCompleted || $isActive)
                        text-gray-900 dark:text-gray-100
                    @else
                        text-gray-500 dark:text-gray-400
                    @endif
                ">
                    {{ $step['title'] }}
                </p>
                @if(isset($step['description']))
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $step['description'] }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>
    
    <!-- Mobile Step Indicator -->
    <div class="sm:hidden flex items-center justify-center mt-3">
        <div class="text-center">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ $steps[$currentStep - 1]['title'] ?? 'Step ' . $currentStep }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Step {{ $currentStep }} of {{ count($steps) }}
            </p>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hd-stepper {
        /* Custom stepper-specific styles */
    }
    
    @media (prefers-reduced-motion: reduce) {
        .hd-stepper * {
            transition: none !important;
            animation: none !important;
        }
    }
</style>
@endpush
