@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="max-w-2xl mx-auto">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            Your Recovery Codes
                        </h2>
                        <p class="text-gray-600">
                            Keep these recovery codes safe. You can use them to access your account if you lose access to your authentication app.
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="bg-red-50 border border-red-200 p-6 rounded-lg mb-6">
                        <div class="flex items-center mb-3">
                            <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-red-800">
                                Important: Save These Recovery Codes
                            </h3>
                        </div>
                        <p class="text-red-700 mb-4">
                            Each recovery code can only be used once. Store them in a safe place and treat them like passwords.
                        </p>
                        
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($recoveryCodes as $code)
                                <div class="bg-white p-3 rounded border">
                                    <code class="text-lg font-mono font-bold text-gray-900">{{ $code }}</code>
                                </div>
                            @endforeach
                        </div>
                        
                        @if ($remainingCount > 0)
                            <div class="mt-4 text-sm text-red-600">
                                <strong>Remaining codes:</strong> {{ $remainingCount }}
                            </div>
                        @endif
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-yellow-800">
                                <p class="font-medium">Remember:</p>
                                <ul class="text-sm mt-2 space-y-1">
                                    <li>• Print or write down these codes</li>
                                    <li>• Store them separately from your device</li>
                                    <li>• Each code works only once</li>
                                    <li>• Use them when you can't access your authenticator app</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <form action="{{ route('2fa.regenerate-codes') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    onclick="return confirm('This will invalidate your current recovery codes. Are you sure?')">
                                Regenerate Recovery Codes
                            </button>
                        </form>
                        
                        <button onclick="printCodes()" 
                                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Print Codes
                        </button>
                        
                        <button onclick="downloadCodes()" 
                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Download as Text
                        </button>
                    </div>

                    <div class="mt-8 text-center">
                        <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-gray-800 underline">
                            ← Back to Profile Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printCodes() {
    const codes = @json($recoveryCodes);
    const printWindow = window.open('', '_blank');
    const content = `
        <html>
            <head>
                <title>{{ config('app.name') }} - Recovery Codes</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h1 { color: #333; }
                    .code { font-family: monospace; font-size: 18px; margin: 10px 0; padding: 5px; background: #f5f5f5; }
                    .warning { color: #d32f2f; margin: 20px 0; padding: 10px; border: 1px solid #d32f2f; background: #ffebee; }
                </style>
            </head>
            <body>
                <h1>{{ config('app.name') }} - Two-Factor Authentication Recovery Codes</h1>
                <div class="warning">
                    <strong>IMPORTANT:</strong> Keep these codes safe and secret. Each code can only be used once.
                </div>
                <h2>Your Recovery Codes:</h2>
                ${codes.map(code => `<div class="code">${code}</div>`).join('')}
                <p><small>Generated on: ${new Date().toLocaleString()}</small></p>
            </body>
        </html>
    `;
    printWindow.document.write(content);
    printWindow.document.close();
    printWindow.print();
}

function downloadCodes() {
    const codes = @json($recoveryCodes);
    const content = `{{ config('app.name') }} - Two-Factor Authentication Recovery Codes\n\n` +
                   `IMPORTANT: Keep these codes safe and secret. Each code can only be used once.\n\n` +
                   `Your Recovery Codes:\n` +
                   codes.join('\n') +
                   `\n\nGenerated on: ${new Date().toLocaleString()}`;
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = '{{ config('app.name') }}_recovery_codes.txt';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
