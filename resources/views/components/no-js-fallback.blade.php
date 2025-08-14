{{-- No JavaScript Fallback UI Component --}}

@props([
    'feature' => 'this feature',
    'showMessage' => true,
    'redirectUrl' => null,
    'fallbackContent' => null
])

{{-- No-JS Detection --}}
<noscript>
    @if($showMessage)
        <div class="no-js-warning alert alert-warning mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>JavaScript Required</strong>
                    <p class="mb-1">{{ ucfirst($feature) }} requires JavaScript to function properly. Please enable JavaScript in your browser for the best experience.</p>
                    
                    @if($redirectUrl)
                        <p class="mb-0">
                            <a href="{{ $redirectUrl }}" class="alert-link">Use the basic version instead</a>
                        </p>
                    @endif
                    
                    <details class="mt-2">
                        <summary class="text-decoration-underline" style="cursor: pointer;">How to enable JavaScript</summary>
                        <div class="mt-2 small">
                            <h6>Chrome, Firefox, Safari, Edge:</h6>
                            <ol class="ps-3">
                                <li>Look for the 🔒 or ⚙️ icon in your address bar</li>
                                <li>Click on it and select "Site Settings" or "Permissions"</li>
                                <li>Find "JavaScript" and set it to "Allow"</li>
                                <li>Refresh this page</li>
                            </ol>
                            
                            <h6 class="mt-3">Alternative:</h6>
                            <p class="mb-0">Open your browser's Settings → Privacy/Security → JavaScript → Enable for all sites</p>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    @endif
    
    @if($fallbackContent)
        <div class="no-js-fallback">
            {!! $fallbackContent !!}
        </div>
    @else
        {{ $slot }}
    @endif
</noscript>

{{-- CSS for enhanced styling --}}
@push('styles')
<style>
.no-js-warning {
    border: 2px solid #ffc107;
    background: linear-gradient(135deg, #fff3cd 0%, #fef7e0 100%);
    border-radius: 8px;
    padding: 1rem;
}

.no-js-warning .fas {
    color: #856404;
    font-size: 1.25rem;
}

.no-js-fallback {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 1rem;
}

.no-js-fallback .form-control {
    margin-bottom: 1rem;
}

.no-js-fallback .btn {
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

@media (max-width: 576px) {
    .no-js-warning {
        padding: 0.75rem;
        font-size: 0.875rem;
    }
    
    .no-js-warning .fas {
        font-size: 1rem;
    }
}
</style>
@endpush
