@props([
    'documents' => [],
    'errors' => [],
])

<div class="hd-legal-acceptance" x-data="{
    selectedDocument: null,
    showModal: false,
    
    openDocument(document) {
        this.selectedDocument = document;
        this.showModal = true;
        document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
        this.showModal = false;
        this.selectedDocument = null;
        document.body.style.overflow = 'auto';
    }
}">
    
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Legal Agreement Required
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            You must read and accept the following documents to register for HD Tickets:
        </p>
    </div>
    
    <!-- Legal Documents List -->
    <div class="space-y-4">
        @foreach($documents as $type => $document)
            @php
                $fieldName = "accept_{$type}";
                $hasError = isset($errors[$fieldName]);
                $checkboxId = "legal-{$type}-" . str()->random(6);
            @endphp
            
            <div class="
                bg-white dark:bg-gray-800 
                border @if($hasError) border-error-300 dark:border-error-600 @else border-gray-200 dark:border-gray-700 @endif 
                rounded-lg p-4 transition-all duration-200
                hover:border-gray-300 dark:hover:border-gray-600
                focus-within:border-primary-500 dark:focus-within:border-primary-400
                focus-within:ring-2 focus-within:ring-primary-500/20
            ">
                <!-- Document Header -->
                <div class="flex items-start space-x-3">
                    <!-- Checkbox -->
                    <div class="flex-shrink-0 mt-0.5">
                        <input
                            type="checkbox"
                            id="{{ $checkboxId }}"
                            name="{{ $fieldName }}"
                            value="1"
                            {{ old($fieldName) ? 'checked' : '' }}
                            required
                            class="
                                w-4 h-4 rounded border-gray-300 dark:border-gray-600 
                                text-success-600 focus:ring-success-500 focus:ring-offset-0
                                transition-colors duration-200
                                @if($hasError) border-error-300 dark:border-error-600 @endif
                            "
                            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                            @if($hasError) aria-describedby="{{ $checkboxId }}-error" @endif
                        />
                    </div>
                    
                    <!-- Document Info -->
                    <div class="flex-1 min-w-0">
                        <label for="{{ $checkboxId }}" class="block text-sm font-medium text-gray-900 dark:text-gray-100 cursor-pointer">
                            I have read and accept the 
                            <button
                                type="button"
                                class="
                                    text-primary-600 dark:text-primary-400 underline hover:text-primary-800 dark:hover:text-primary-300 
                                    focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 rounded
                                "
                                x-on:click="openDocument({
                                    type: '{{ $type }}',
                                    title: '{{ addslashes($document->title) }}',
                                    content: `{!! addslashes($document->content) !!}`,
                                    version: '{{ $document->version }}',
                                    effectiveDate: '{{ $document->effective_date?->format('F j, Y') }}',
                                    summary: '{{ addslashes($document->summary ?? '') }}'
                                })"
                                :aria-label="'View {{ $document->title }}'"
                            >
                                {{ $document->title }}
                                <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </button>
                        </label>
                        
                        <!-- Version & Date Info -->
                        @if($document->version || $document->effective_date)
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($document->version)
                                    Version {{ $document->version }}
                                @endif
                                @if($document->version && $document->effective_date)
                                    •
                                @endif
                                @if($document->effective_date)
                                    Last updated {{ $document->effective_date->format('M j, Y') }}
                                @endif
                            </div>
                        @endif
                        
                        <!-- Summary Preview -->
                        @if($document->summary)
                            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                {{ $document->summary }}
                            </p>
                        @endif
                    </div>
                </div>
                
                <!-- Error Message -->
                @if($hasError)
                    <div id="{{ $checkboxId }}-error" class="mt-3 text-sm text-error-600 dark:text-error-400 flex items-start">
                        <svg class="w-4 h-4 mr-1 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $errors[$fieldName] }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    
    <!-- GDPR Information Notice -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                    Data Protection Information
                </h4>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>
                        We process your personal data in accordance with GDPR. You have rights including access, 
                        rectification, erasure, and data portability. 
                        <a href="{{ route('legal.privacy-policy') }}" target="_blank" class="underline hover:no-underline">
                            Learn more about your privacy rights
                            <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal for Document Viewing -->
    <div 
        x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
        x-cloak
    >
        <!-- Backdrop -->
        <div 
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            x-on:click="closeModal()"
            aria-hidden="true"
        ></div>
        
        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                class="
                    relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 
                    text-left shadow-xl transition-all w-full max-w-4xl max-h-[90vh]
                "
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex-1 min-w-0">
                        <h2 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="selectedDocument?.title">
                        </h2>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-show="selectedDocument?.version || selectedDocument?.effectiveDate">
                            <span x-show="selectedDocument?.version" x-text="'Version ' + selectedDocument?.version"></span>
                            <span x-show="selectedDocument?.version && selectedDocument?.effectiveDate"> • </span>
                            <span x-show="selectedDocument?.effectiveDate" x-text="'Last updated ' + selectedDocument?.effectiveDate"></span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="
                            ml-4 flex-shrink-0 rounded-lg p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 
                            focus:outline-none focus:ring-2 focus:ring-primary-500
                        "
                        x-on:click="closeModal()"
                        aria-label="Close document"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <!-- Summary -->
                    <div x-show="selectedDocument?.summary" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Document Summary</h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300" x-text="selectedDocument?.summary"></p>
                    </div>
                    
                    <!-- Document Content -->
                    <div 
                        class="prose prose-sm max-w-none dark:prose-invert prose-blue"
                        x-html="selectedDocument?.content"
                    ></div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex items-center justify-end p-6 border-t border-gray-200 dark:border-gray-700 space-x-3">
                    <button
                        type="button"
                        class="
                            px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 
                            border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 
                            focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors duration-200
                        "
                        x-on:click="closeModal()"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak] { 
        display: none !important; 
    }
    
    .hd-legal-acceptance .prose {
        color: inherit;
    }
    
    .hd-legal-acceptance .prose h1,
    .hd-legal-acceptance .prose h2,
    .hd-legal-acceptance .prose h3,
    .hd-legal-acceptance .prose h4 {
        color: inherit;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .hd-legal-acceptance .prose h1:first-child,
    .hd-legal-acceptance .prose h2:first-child {
        margin-top: 0;
    }
    
    @media (prefers-reduced-motion: reduce) {
        .hd-legal-acceptance * {
            transition: none !important;
        }
    }
</style>
@endpush
