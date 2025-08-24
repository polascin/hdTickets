<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6" id="password-update-form">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input 
                id="update_password_password" 
                name="password" 
                type="password" 
                class="mt-1 block w-full" 
                autocomplete="new-password" 
                data-strength-indicator="true"
                data-show-requirements="true"
                data-show-estimations="true"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            
            <!-- Real-time feedback container -->
            <div id="password-feedback" class="mt-3" style="display: none;">
                <div id="compromise-check" class="mb-2"></div>
                <div id="history-check" class="mb-2"></div>
                <div id="overall-status" class="mb-2"></div>
            </div>
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Password Requirements Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-medium text-blue-900 mb-2">📋 Password Requirements</h4>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• At least 8 characters long (12+ recommended)</li>
                <li>• Contains uppercase and lowercase letters</li>
                <li>• Contains at least one number</li>
                <li>• Contains at least one special character (!@#$%^&*)</li>
                <li>• Cannot be the same as your current password</li>
                <li>• Cannot be one of your recently used passwords</li>
                <li>• Should not appear in known data breaches</li>
            </ul>
        </div>

        <!-- Password History Info -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4" id="password-history-info">
            <h4 class="font-medium text-gray-900 mb-2">🕐 Password History</h4>
            <p class="text-sm text-gray-600">Loading password history information...</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button id="save-password-btn" disabled>{{ __('Update Password') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >{{ __('Password updated successfully! Check your email for confirmation.') }}</p>
            @endif
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('update_password_password');
        const confirmInput = document.getElementById('update_password_password_confirmation');
        const saveButton = document.getElementById('save-password-btn');
        const feedbackDiv = document.getElementById('password-feedback');
        const compromiseDiv = document.getElementById('compromise-check');
        const historyDiv = document.getElementById('history-check');
        const statusDiv = document.getElementById('overall-status');
        const historyInfoDiv = document.getElementById('password-history-info');

        let debounceTimer;
        let lastValidation = { isValid: false };

        // Load password history info
        loadPasswordHistoryInfo();

        // Password strength checking with debounce
        passwordInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (passwordInput.value.length > 0) {
                    checkPasswordStrength(passwordInput.value);
                } else {
                    feedbackDiv.style.display = 'none';
                    updateSaveButton();
                }
            }, 500);
        });

        confirmInput.addEventListener('input', updateSaveButton);

        function checkPasswordStrength(password) {
            fetch('/password/check-strength', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password: password })
            })
            .then(response => response.json())
            .then(data => {
                displayPasswordFeedback(data);
                lastValidation = data.overall_status;
                updateSaveButton();
            })
            .catch(error => {
                console.error('Password check error:', error);
            });
        }

        function displayPasswordFeedback(data) {
            feedbackDiv.style.display = 'block';

            // Display compromise check
            const compromise = data.compromise_check;
            if (compromise.is_compromised) {
                compromiseDiv.innerHTML = `
                    <div class="${compromise.severity === 'critical' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-yellow-50 border-yellow-200 text-yellow-800'} border rounded-md p-3">
                        <div class="font-medium">⚠️ Security Warning</div>
                        <div class="text-sm">${compromise.message}</div>
                        <div class="text-xs mt-1">${compromise.recommendation}</div>
                    </div>
                `;
            } else {
                compromiseDiv.innerHTML = `
                    <div class="bg-green-50 border-green-200 text-green-800 border rounded-md p-3">
                        <div class="font-medium">✅ Security Check</div>
                        <div class="text-sm">${compromise.message}</div>
                    </div>
                `;
            }

            // Display history check
            const history = data.history_check;
            if (history.is_recently_used || history.is_current_password) {
                historyDiv.innerHTML = `
                    <div class="bg-red-50 border-red-200 text-red-800 border rounded-md p-3">
                        <div class="font-medium">🚫 Password History</div>
                        <div class="text-sm">${history.message}</div>
                    </div>
                `;
            } else {
                historyDiv.innerHTML = `
                    <div class="bg-green-50 border-green-200 text-green-800 border rounded-md p-3">
                        <div class="font-medium">✅ Password History</div>
                        <div class="text-sm">${history.message}</div>
                    </div>
                `;
            }

            // Display overall status
            const status = data.overall_status;
            let statusClass = status.is_valid ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
            
            let statusContent = `
                <div class="${statusClass} border rounded-md p-3">
                    <div class="font-medium">${status.is_valid ? '✅' : '❌'} Overall Status: ${status.strength_label} (${Math.round(status.strength_score)}%)</div>
            `;
            
            if (status.errors && status.errors.length > 0) {
                statusContent += `<div class="text-sm mt-1"><strong>Issues:</strong> ${status.errors.join(', ')}</div>`;
            }
            
            if (status.warnings && status.warnings.length > 0) {
                statusContent += `<div class="text-sm mt-1"><strong>Warnings:</strong> ${status.warnings.join(', ')}</div>`;
            }
            
            statusContent += '</div>';
            statusDiv.innerHTML = statusContent;
        }

        function updateSaveButton() {
            const hasPassword = passwordInput.value.length > 0;
            const hasConfirmation = confirmInput.value.length > 0;
            const passwordsMatch = passwordInput.value === confirmInput.value;
            const isValid = lastValidation.is_valid;

            const shouldEnable = hasPassword && hasConfirmation && passwordsMatch && isValid;
            saveButton.disabled = !shouldEnable;
            
            if (shouldEnable) {
                saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                saveButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        function loadPasswordHistoryInfo() {
            fetch('/password/history-info')
                .then(response => response.json())
                .then(data => {
                    historyInfoDiv.innerHTML = `
                        <h4 class="font-medium text-gray-900 mb-2">🕐 Password History</h4>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p>• We remember your last <strong>${data.max_history_count}</strong> passwords</p>
                            <p>• You currently have <strong>${data.history_count}</strong> passwords in history</p>
                            <p>• Passwords cannot be reused for <strong>${data.reuse_days}</strong> days</p>
                            ${data.newest_entry ? `<p>• Last password change: <strong>${data.newest_entry}</strong></p>` : ''}
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Failed to load password history info:', error);
                });
        }
    });
    </script>
</section>
