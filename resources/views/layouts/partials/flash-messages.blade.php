{{--
    Flash Messages Partial - Unified Layout System
    Responsive flash messages with auto-dismiss functionality
--}}
<div class="flash-messages-container" x-data="flashMessagesManager()">
    {{-- Success Messages --}}
@if(session('success'))
        <div class="alert alert-success mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transform ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-init="setTimeout(() => show = false, 5000)"
             data-testid="flash-success">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
<x-icon name="check-circle" class="mr-3 text-green-600 w-5 h-5" />
                    <div>
                        <strong>Success!</strong>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
<button @click="show = false" 
                        class="ml-4 text-green-600 hover:text-green-800 transition-colors" aria-label="Dismiss success message"
                        data-testid="flash-dismiss-success">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Error Messages --}}
@if(session('error'))
        <div class="alert alert-danger mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transform ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-init="setTimeout(() => show = false, 7000)"
             data-testid="flash-error">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
<x-icon name="warning" class="mr-3 text-red-600 w-5 h-5" />
                    <div>
                        <strong>Error!</strong>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
<button @click="show = false" 
                        class="ml-4 text-red-600 hover:text-red-800 transition-colors" aria-label="Dismiss error message"
                        data-testid="flash-dismiss-error">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Warning Messages --}}
@if(session('warning'))
        <div class="alert alert-warning mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transform ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-init="setTimeout(() => show = false, 6000)"
             data-testid="flash-warning">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
<x-icon name="warning" class="mr-3 text-yellow-600 w-5 h-5" />
                    <div>
                        <strong>Warning!</strong>
                        <span>{{ session('warning') }}</span>
                    </div>
                </div>
<button @click="show = false" 
                        class="ml-4 text-yellow-600 hover:text-yellow-800 transition-colors" aria-label="Dismiss warning message"
                        data-testid="flash-dismiss-warning">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Info Messages --}}
@if(session('info'))
        <div class="alert alert-info mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transform ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-init="setTimeout(() => show = false, 5000)"
             data-testid="flash-info">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
<x-icon name="info" class="mr-3 text-blue-600 w-5 h-5" />
                    <div>
                        <strong>Info!</strong>
                        <span>{{ session('info') }}</span>
                    </div>
                </div>
<button @click="show = false" 
                        class="ml-4 text-blue-600 hover:text-blue-800 transition-colors" aria-label="Dismiss info message"
                        data-testid="flash-dismiss-info">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Status Messages --}}
@if(session('status'))
        <div class="alert alert-info mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transform ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-init="setTimeout(() => show = false, 4000)"
             data-testid="flash-status">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
<x-icon name="info" class="mr-3 text-blue-600 w-5 h-5" />
                    <div>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
<button @click="show = false" 
                        class="ml-4 text-blue-600 hover:text-blue-800 transition-colors" aria-label="Dismiss status message"
                        data-testid="flash-dismiss-status">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
@if($errors->any())
        <div class="alert alert-danger mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transform ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             data-testid="flash-validation-errors">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
<x-icon name="warning" class="mr-3 text-red-600 w-5 h-5 mt-0.5" />
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mt-2 space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-sm">• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
<button @click="show = false" 
                        class="ml-4 text-red-600 hover:text-red-800 transition-colors flex-shrink-0" aria-label="Dismiss validation errors"
                        data-testid="flash-dismiss-validation-errors">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Alpine.js Flash Messages Manager --}}
<script>
    function flashMessagesManager() {
        return {
            init() {
                // Add any global flash message handling here
                console.log('Flash messages initialized');
            }
        }
    }
</script>
