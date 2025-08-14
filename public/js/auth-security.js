/**
 * HD Tickets Authentication Security Enhancement
 * 
 * Provides comprehensive client-side security measures for authentication forms
 * including rate limiting UI, form resubmission prevention, and CSRF handling.
 */

class AuthSecurity {
    constructor() {
        this.init();
        this.rateLimitCountdown = null;
        this.submissionInProgress = false;
        this.formSubmissionTokens = new Set();
    }

    init() {
        this.setupCSRFHeaders();
        this.setupFormProtection();
        this.setupRateLimitUI();
        this.setupHoneypotProtection();
        this.setupTimestampTracking();
    }

    /**
     * Setup CSRF protection headers for AJAX requests
     */
    setupCSRFHeaders() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            // Set up default headers for fetch requests
            window.csrfToken = token;
            
            // Set up jQuery CSRF headers if jQuery is available
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                });
            }
        }
    }

    /**
     * Prevent form resubmission and double-clicking
     */
    setupFormProtection() {
        const loginForm = document.getElementById('login-form');
        if (!loginForm) return;

        const submitButton = loginForm.querySelector('button[type="submit"]');
        const formTokenInput = loginForm.querySelector('input[name="form_token"]');
        
        loginForm.addEventListener('submit', (e) => {
            // Check if submission is already in progress
            if (this.submissionInProgress) {
                e.preventDefault();
                this.showMessage('Please wait, your request is being processed...', 'warning');
                return;
            }

            // Check for duplicate form token (prevents back button resubmission)
            if (formTokenInput && this.formSubmissionTokens.has(formTokenInput.value)) {
                e.preventDefault();
                this.showMessage('This form has already been submitted. Please refresh the page and try again.', 'error');
                return;
            }

            // Mark submission as in progress
            this.submissionInProgress = true;
            if (formTokenInput) {
                this.formSubmissionTokens.add(formTokenInput.value);
            }

            // Update button state
            if (submitButton) {
                submitButton.disabled = true;
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Signing In...</span>
                `;
                
                // Reset button after 30 seconds (safety net)
                setTimeout(() => {
                    if (submitButton.disabled) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                        this.submissionInProgress = false;
                    }
                }, 30000);
            }

            // Set client timestamp
            const timestampInput = document.getElementById('client_timestamp');
            if (timestampInput) {
                timestampInput.value = new Date().toISOString();
            }
        });

        // Re-enable form if there's a validation error (form is reloaded)
        window.addEventListener('load', () => {
            const hasErrors = document.querySelector('.text-red-600');
            if (hasErrors && submitButton) {
                this.submissionInProgress = false;
                submitButton.disabled = false;
            }
        });
    }

    /**
     * Setup rate limiting UI with countdown timer
     */
    setupRateLimitUI() {
        const errorElements = document.querySelectorAll('.text-red-600');
        
        errorElements.forEach(errorElement => {
            const errorText = errorElement.textContent;
            
            // Check if this is a rate limit error
            if (errorText.includes('Too many login attempts') && errorText.includes('seconds')) {
                this.handleRateLimit(errorElement, errorText);
            }
        });
    }

    /**
     * Handle rate limit display with countdown
     */
    handleRateLimit(errorElement, errorText) {
        // Extract seconds from error message
        const secondsMatch = errorText.match(/\((\d+) seconds\)/);
        if (!secondsMatch) return;

        let remainingSeconds = parseInt(secondsMatch[1]);
        const baseMessage = errorText.split(' Please try again')[0];

        // Create countdown element
        const countdownElement = document.createElement('div');
        countdownElement.className = 'mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-md';
        countdownElement.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-yellow-800">Security Lockout Active</p>
                    <p class="text-sm text-yellow-700">Try again in <span id="countdown-timer" class="font-mono font-bold">${remainingSeconds}</span> seconds</p>
                </div>
            </div>
            <div class="mt-2">
                <div class="w-full bg-yellow-200 rounded-full h-2">
                    <div id="countdown-progress" class="bg-yellow-400 h-2 rounded-full transition-all duration-1000" style="width: 100%"></div>
                </div>
            </div>
        `;

        // Insert countdown after the error element
        errorElement.parentNode.insertBefore(countdownElement, errorElement.nextSibling);

        // Update original error to be less technical
        errorElement.textContent = baseMessage + ' Please wait for the security timeout to expire.';

        // Disable the form
        const form = document.getElementById('login-form');
        const submitButton = form?.querySelector('button[type="submit"]');
        const inputs = form?.querySelectorAll('input');
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
        
        inputs?.forEach(input => {
            if (input.type !== 'hidden') {
                input.disabled = true;
                input.classList.add('opacity-50');
            }
        });

        // Start countdown
        this.startCountdown(remainingSeconds, countdownElement);
    }

    /**
     * Start the countdown timer
     */
    startCountdown(seconds, countdownElement) {
        const timerElement = countdownElement.querySelector('#countdown-timer');
        const progressElement = countdownElement.querySelector('#countdown-progress');
        const totalSeconds = seconds;

        this.rateLimitCountdown = setInterval(() => {
            seconds--;
            
            if (timerElement) {
                timerElement.textContent = seconds;
            }
            
            if (progressElement) {
                const percentage = (seconds / totalSeconds) * 100;
                progressElement.style.width = percentage + '%';
            }

            if (seconds <= 0) {
                clearInterval(this.rateLimitCountdown);
                this.enableFormAfterTimeout(countdownElement);
            }
        }, 1000);
    }

    /**
     * Re-enable form after timeout expires
     */
    enableFormAfterTimeout(countdownElement) {
        // Remove countdown element
        countdownElement.remove();

        // Re-enable form
        const form = document.getElementById('login-form');
        const submitButton = form?.querySelector('button[type="submit"]');
        const inputs = form?.querySelectorAll('input');

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        inputs?.forEach(input => {
            if (input.type !== 'hidden') {
                input.disabled = false;
                input.classList.remove('opacity-50');
            }
        });

        // Show success message
        this.showMessage('Security timeout has expired. You may now try logging in again.', 'success');
    }

    /**
     * Setup honeypot field protection
     */
    setupHoneypotProtection() {
        const honeypot = document.querySelector('input[name="website"]');
        if (!honeypot) return;

        // Monitor honeypot field - if filled, likely a bot
        honeypot.addEventListener('input', () => {
            // Mark as potential bot
            console.warn('Potential bot detected: honeypot field filled');
            
            // You could send this information to the server
            // or implement additional security measures
        });

        // Additional protection: check if honeypot is visible
        const observer = new MutationObserver(() => {
            if (honeypot.offsetParent !== null) {
                console.warn('Potential bot detected: honeypot field made visible');
            }
        });

        observer.observe(honeypot, { 
            attributes: true, 
            attributeFilter: ['style'] 
        });
    }

    /**
     * Setup client-side timestamp tracking
     */
    setupTimestampTracking() {
        // Track page load time for anti-automation
        window.pageLoadTime = new Date().getTime();
        
        // Track form interaction time
        const form = document.getElementById('login-form');
        if (form) {
            let firstInteraction = null;
            
            form.addEventListener('input', (e) => {
                if (!firstInteraction) {
                    firstInteraction = new Date().getTime();
                    
                    // Add hidden field to track interaction time
                    const interactionInput = document.createElement('input');
                    interactionInput.type = 'hidden';
                    interactionInput.name = 'first_interaction';
                    interactionInput.value = firstInteraction.toString();
                    form.appendChild(interactionInput);
                }
            });
        }
    }

    /**
     * Show user messages
     */
    showMessage(message, type = 'info') {
        const existingMessage = document.querySelector('.auth-security-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        const colors = {
            success: 'bg-green-50 border-green-200 text-green-700',
            error: 'bg-red-50 border-red-200 text-red-700',
            warning: 'bg-yellow-50 border-yellow-200 text-yellow-700',
            info: 'bg-blue-50 border-blue-200 text-blue-700'
        };

        const icons = {
            success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
            info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        };

        const messageElement = document.createElement('div');
        messageElement.className = `auth-security-message mb-4 p-4 border rounded-lg ${colors[type]}`;
        messageElement.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[type]}"></path>
                </svg>
                <p class="text-sm font-medium">${message}</p>
            </div>
        `;

        // Insert at the top of the login form
        const form = document.getElementById('login-form');
        if (form) {
            form.parentNode.insertBefore(messageElement, form);
        }

        // Auto-remove success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                messageElement.remove();
            }, 5000);
        }
    }

    /**
     * Clean up timers when page is unloaded
     */
    cleanup() {
        if (this.rateLimitCountdown) {
            clearInterval(this.rateLimitCountdown);
        }
    }
}

/**
 * Professional Authentication Features Extension
 */
class ProfessionalAuthFeatures {
    constructor() {
        this.sessionTimeoutWarning = null;
        this.sessionCountdown = null;
        this.tooltips = new Map();
        
        this.config = {
            sessionWarningMinutes: 5,
            sessionTimeoutMinutes: 30,
            passwordRequirements: {
                minLength: 8,
                requireLowercase: true,
                requireUppercase: true,
                requireNumbers: true,
                requireSpecialChars: true,
                recommendedLength: 12
            },
            emailExamples: [
                'user@example.com',
                'name.lastname@domain.co.uk', 
                'admin@company.org'
            ]
        };

        this.init();
    }

    init() {
        this.setupPasswordStrengthIndicator();
        this.setupSessionTimeout();
        this.setupTooltips();
        this.setupEmailHelpers();
        this.setupSupportInfo();
        this.injectStyles();
    }

    setupPasswordStrengthIndicator() {
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        
        passwordInputs.forEach(input => {
            if (input.name === 'password' || input.name === 'new_password') {
                this.createPasswordStrengthIndicator(input);
            }
        });
    }

    createPasswordStrengthIndicator(input) {
        const container = document.createElement('div');
        container.className = 'password-strength-container';
        container.innerHTML = `
            <div class="password-strength-bar-container" style="display: none;">
                <div class="password-strength-bar">
                    <div class="password-strength-fill"></div>
                </div>
                <div class="password-strength-text"></div>
            </div>
            <div class="password-requirements" style="display: none;">
                <div class="requirements-header">
                    <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Password Requirements</span>
                </div>
                <ul class="requirements-list">
                    <li data-requirement="length">
                        <span class="requirement-icon">○</span>
                        At least 8 characters
                    </li>
                    <li data-requirement="lowercase">
                        <span class="requirement-icon">○</span>
                        One lowercase letter (a-z)
                    </li>
                    <li data-requirement="uppercase">
                        <span class="requirement-icon">○</span>
                        One uppercase letter (A-Z)
                    </li>
                    <li data-requirement="numbers">
                        <span class="requirement-icon">○</span>
                        One number (0-9)
                    </li>
                    <li data-requirement="special">
                        <span class="requirement-icon">○</span>
                        One special character (!@#$%^&*)
                    </li>
                    <li data-requirement="recommended" class="recommended">
                        <span class="requirement-icon">○</span>
                        12+ characters (recommended)
                    </li>
                </ul>
            </div>
            <div class="password-tips" style="display: none;">
                <div class="tip-content">
                    <svg class="h-4 w-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span class="tip-text"></span>
                </div>
            </div>
        `;

        input.parentNode.insertBefore(container, input.nextSibling);

        const strengthBar = container.querySelector('.password-strength-bar-container');
        const strengthFill = container.querySelector('.password-strength-fill');
        const strengthText = container.querySelector('.password-strength-text');
        const requirements = container.querySelector('.password-requirements');
        const tips = container.querySelector('.password-tips');

        input.addEventListener('focus', () => {
            requirements.style.display = 'block';
        });

        input.addEventListener('blur', () => {
            if (!input.value) {
                requirements.style.display = 'none';
                strengthBar.style.display = 'none';
                tips.style.display = 'none';
            }
        });

        input.addEventListener('input', (e) => {
            this.updatePasswordStrength(e.target.value, {
                strengthBar, strengthFill, strengthText, requirements, tips
            });
        });
    }

    updatePasswordStrength(password, elements) {
        const { strengthBar, strengthFill, strengthText, requirements, tips } = elements;

        if (!password) {
            strengthBar.style.display = 'none';
            tips.style.display = 'none';
            return;
        }

        strengthBar.style.display = 'block';
        const strength = this.calculatePasswordStrength(password);
        const percentage = (strength.score / 4) * 100;

        strengthFill.style.width = `${percentage}%`;
        strengthFill.className = `password-strength-fill strength-${strength.level}`;
        strengthText.textContent = strength.label;
        strengthText.className = `password-strength-text text-${strength.level}`;

        this.updateRequirements(password, requirements);

        if (strength.score < 2 && strength.suggestions.length > 0) {
            tips.style.display = 'block';
            tips.querySelector('.tip-text').textContent = strength.suggestions[0];
        } else {
            tips.style.display = 'none';
        }
    }

    calculatePasswordStrength(password) {
        let score = 0;
        const suggestions = [];

        if (password.length >= 8) score++;
        else suggestions.push('Use at least 8 characters');

        if (/[a-z]/.test(password)) score++;
        else suggestions.push('Include lowercase letters');

        if (/[A-Z]/.test(password)) score++;
        else suggestions.push('Include uppercase letters');

        if (/\d/.test(password)) score++;
        else suggestions.push('Include numbers');

        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score++;
        else suggestions.push('Include special characters');

        if (password.length >= 12) score = Math.min(score + 1, 4);

        const levels = ['very-weak', 'weak', 'fair', 'good', 'strong'];
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];

        return {
            score,
            level: levels[score] || 'very-weak',
            label: labels[score] || 'Very Weak',
            suggestions
        };
    }

    updateRequirements(password, requirementsElement) {
        const requirements = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            numbers: /\d/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
            recommended: password.length >= 12
        };

        Object.entries(requirements).forEach(([key, met]) => {
            const element = requirementsElement.querySelector(`[data-requirement="${key}"]`);
            if (element) {
                const icon = element.querySelector('.requirement-icon');
                if (met) {
                    element.classList.add('met');
                    icon.textContent = '✓';
                    icon.style.color = '#10b981';
                } else {
                    element.classList.remove('met');
                    icon.textContent = '○';
                    icon.style.color = '#6b7280';
                }
            }
        });
    }

    setupSessionTimeout() {
        const isAuthenticated = document.querySelector('meta[name="authenticated"]')?.content === 'true';
        if (!isAuthenticated) return;
        
        this.startSessionMonitoring();
    }

    startSessionMonitoring() {
        const warningTime = (this.config.sessionTimeoutMinutes - this.config.sessionWarningMinutes) * 60 * 1000;
        
        setTimeout(() => {
            this.showSessionWarning();
        }, warningTime);

        this.setupActivityTracking();
    }

    setupActivityTracking() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        const resetTimer = this.debounce(() => {
            this.startSessionMonitoring();
        }, 60000);

        events.forEach(event => {
            document.addEventListener(event, resetTimer, true);
        });
    }

    showSessionWarning() {
        if (this.sessionTimeoutWarning) return;

        const overlay = document.createElement('div');
        overlay.className = 'session-warning-overlay';
        overlay.innerHTML = `
            <div class="session-warning-modal">
                <div class="session-warning-header">
                    <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3>Session Expiring Soon</h3>
                </div>
                <div class="session-warning-content">
                    <p>Your session will expire in <strong id="session-countdown">${this.config.sessionWarningMinutes}:00</strong> due to inactivity.</p>
                    <p>You will be automatically logged out to protect your account security.</p>
                </div>
                <div class="session-warning-actions">
                    <button type="button" class="btn-extend-session">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Stay Logged In
                    </button>
                    <button type="button" class="btn-logout-now">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Logout Now
                    </button>
                </div>
                <div class="session-progress-bar">
                    <div class="session-progress-fill"></div>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        this.sessionTimeoutWarning = overlay;
        this.startSessionCountdown();

        overlay.querySelector('.btn-extend-session').addEventListener('click', () => {
            this.extendSession();
        });

        overlay.querySelector('.btn-logout-now').addEventListener('click', () => {
            this.performLogout();
        });
    }

    startSessionCountdown() {
        let timeLeft = this.config.sessionWarningMinutes * 60;
        const countdownElement = document.getElementById('session-countdown');
        const progressBar = this.sessionTimeoutWarning.querySelector('.session-progress-fill');
        const totalTime = this.config.sessionWarningMinutes * 60;

        this.sessionCountdown = setInterval(() => {
            timeLeft--;
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            const percentage = (timeLeft / totalTime) * 100;
            progressBar.style.width = `${percentage}%`;

            if (timeLeft <= 0) {
                clearInterval(this.sessionCountdown);
                this.performAutoLogout();
            }
        }, 1000);
    }

    extendSession() {
        fetch('/api/session/extend', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.closeSessionWarning();
                this.showNotification('Session extended successfully', 'success');
                setTimeout(() => this.startSessionMonitoring(), 1000);
            } else {
                this.showNotification('Unable to extend session. Please log in again.', 'error');
                this.performLogout();
            }
        })
        .catch(() => {
            this.showNotification('Connection error. Please check your internet connection.', 'error');
        });
    }

    performAutoLogout() {
        this.closeSessionWarning();
        this.showNotification('Your session has expired due to inactivity. Redirecting to login...', 'warning');
        
        setTimeout(() => {
            window.location.href = '/login';
        }, 3000);
    }

    performLogout() {
        window.location.href = '/logout';
    }

    closeSessionWarning() {
        if (this.sessionTimeoutWarning) {
            this.sessionTimeoutWarning.remove();
            this.sessionTimeoutWarning = null;
        }
        
        if (this.sessionCountdown) {
            clearInterval(this.sessionCountdown);
            this.sessionCountdown = null;
        }
    }

    setupTooltips() {
        this.createTooltip('input[name="password"]', {
            title: 'Password Security',
            content: 'Use a strong, unique password. Avoid using personal information or common words. Consider using a password manager.',
            placement: 'bottom'
        });

        this.createTooltip('input[name="email"]', {
            title: 'Email Address Format',
            content: `Examples: ${this.config.emailExamples.join(', ')}`,
            placement: 'bottom'
        });

        this.createTooltip('input[name="remember"]', {
            title: 'Remember Me',
            content: 'This will keep you signed in for 30 days. Only use on trusted devices.',
            placement: 'right'
        });
    }

    createTooltip(selector, options) {
        const elements = document.querySelectorAll(selector);
        
        elements.forEach(element => {
            const tooltip = document.createElement('div');
            tooltip.className = 'professional-tooltip';
            tooltip.innerHTML = `
                <div class="tooltip-header">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <strong>${options.title}</strong>
                </div>
                <div class="tooltip-content">
                    ${options.content}
                </div>
            `;

            document.body.appendChild(tooltip);
            this.tooltips.set(element, tooltip);

            const position = () => {
                const rect = element.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();

                let top = rect.bottom + window.scrollY + 5;
                let left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);

                tooltip.style.top = `${top}px`;
                tooltip.style.left = `${left}px`;
            };

            element.addEventListener('focus', () => {
                position();
                tooltip.classList.add('show');
            });

            element.addEventListener('blur', () => {
                tooltip.classList.remove('show');
            });

            element.addEventListener('mouseenter', () => {
                position();
                tooltip.classList.add('show');
            });

            element.addEventListener('mouseleave', () => {
                tooltip.classList.remove('show');
            });

            window.addEventListener('resize', position);
        });
    }

    setupEmailHelpers() {
        const emailInputs = document.querySelectorAll('input[type="email"]');
        
        emailInputs.forEach(input => {
            if (!input.placeholder) {
                this.addEmailExamples(input);
            }

            input.addEventListener('blur', (e) => {
                this.validateEmailFormat(e.target);
            });
        });
    }

    addEmailExamples(input) {
        let currentExample = 0;
        const examples = this.config.emailExamples;

        const cycleExamples = () => {
            if (input.value === '') {
                input.placeholder = examples[currentExample];
                currentExample = (currentExample + 1) % examples.length;
            }
        };

        cycleExamples();
        setInterval(cycleExamples, 3000);
    }

    validateEmailFormat(input) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = emailRegex.test(input.value);

        const existingIndicator = input.parentNode.querySelector('.email-validation');
        if (existingIndicator) existingIndicator.remove();

        if (input.value && !isValid) {
            const indicator = document.createElement('div');
            indicator.className = 'email-validation invalid';
            indicator.innerHTML = `
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span>Please enter a valid email address</span>
            `;
            input.parentNode.insertBefore(indicator, input.nextSibling);
        } else if (input.value && isValid) {
            const indicator = document.createElement('div');
            indicator.className = 'email-validation valid';
            indicator.innerHTML = `
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Valid email format</span>
            `;
            input.parentNode.insertBefore(indicator, input.nextSibling);
        }
    }

    setupSupportInfo() {
        const authForms = document.querySelectorAll('form[action*="login"], form[action*="register"], form[action*="password"]');
        
        authForms.forEach(form => {
            this.addSupportInfo(form);
        });
    }

    addSupportInfo(form) {
        const supportSection = document.createElement('div');
        supportSection.className = 'support-contact-section';
        supportSection.innerHTML = `
            <div class="support-header">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3>Need Help?</h3>
            </div>
            <div class="support-content">
                <div class="support-item">
                    <strong>New Users:</strong>
                    <p>Account registration is restricted to administrators. Contact your system administrator to request access to HD Tickets.</p>
                </div>
                <div class="support-item">
                    <strong>Password Issues:</strong>
                    <p>Use the "Forgot Password" link above to reset your password. If you continue having issues, contact support.</p>
                </div>
                <div class="support-item">
                    <strong>Technical Support:</strong>
                    <div class="contact-methods">
                        <a href="mailto:support@hdtickets.local" class="contact-link">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            support@hdtickets.local
                        </a>
                        <span class="contact-info">Response within 24 hours</span>
                    </div>
                </div>
                <div class="support-item">
                    <strong>System Status:</strong>
                    <div class="system-status">
                        <div class="status-indicator online"></div>
                        <span>All systems operational</span>
                    </div>
                </div>
            </div>
        `;

        const container = form.parentNode;
        container.appendChild(supportSection);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `professional-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${this.getNotificationIcon(type)}"></path>
                </svg>
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close" type="button">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, type === 'error' ? 8000 : 5000);

        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
            info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        };
        return icons[type] || icons.info;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    injectStyles() {
        const style = document.createElement('style');
        style.id = 'professional-auth-styles';
        style.textContent = `
            /* Password Strength Styles */
            .password-strength-container { margin-top: 8px; transition: all 0.3s ease; }
            .password-strength-bar { height: 6px; background-color: #e5e7eb; border-radius: 3px; overflow: hidden; margin-bottom: 4px; }
            .password-strength-fill { height: 100%; border-radius: 3px; transition: all 0.3s ease; background-color: #ef4444; }
            .password-strength-fill.strength-very-weak { background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%); }
            .password-strength-fill.strength-weak { background: linear-gradient(90deg, #f97316 0%, #ea580c 100%); }
            .password-strength-fill.strength-fair { background: linear-gradient(90deg, #eab308 0%, #ca8a04 100%); }
            .password-strength-fill.strength-good { background: linear-gradient(90deg, #65a30d 0%, #4d7c0f 100%); }
            .password-strength-fill.strength-strong { background: linear-gradient(90deg, #16a34a 0%, #15803d 100%); }
            .password-strength-text { font-size: 12px; font-weight: 600; text-align: center; margin-bottom: 4px; }
            .password-strength-text.text-very-weak { color: #dc2626; }
            .password-strength-text.text-weak { color: #ea580c; }
            .password-strength-text.text-fair { color: #ca8a04; }
            .password-strength-text.text-good { color: #4d7c0f; }
            .password-strength-text.text-strong { color: #15803d; }
            
            /* Password Requirements */
            .password-requirements { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-top: 8px; }
            .requirements-header { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; }
            .requirements-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 4px; }
            .requirement-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b; transition: all 0.2s ease; }
            .requirement-item.met { color: #16a34a; }
            .requirement-item.recommended { border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 4px; opacity: 0.8; }
            .requirement-icon { width: 14px; height: 14px; border-radius: 50%; font-size: 10px; font-weight: bold; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
            .password-tips { background-color: #fef3c7; border: 1px solid #fbbf24; border-radius: 6px; padding: 8px; margin-top: 6px; }
            .tip-content { display: flex; align-items: flex-start; gap: 6px; font-size: 12px; color: #92400e; }
            
            /* Session Warning */
            .session-warning-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.75); z-index: 9999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); animation: fadeIn 0.3s ease-out; }
            .session-warning-modal { background: white; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 450px; width: 90%; margin: 20px; overflow: hidden; animation: slideIn 0.3s ease-out; }
            .session-warning-header { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; padding: 20px; display: flex; align-items: center; gap: 12px; }
            .session-warning-header h3 { margin: 0; font-size: 18px; font-weight: 600; }
            .session-warning-content { padding: 20px; }
            .session-warning-content p { margin: 0 0 12px 0; color: #374151; line-height: 1.5; }
            .session-warning-content strong { color: #dc2626; font-weight: 600; }
            .session-warning-actions { display: flex; gap: 12px; padding: 0 20px 20px 20px; }
            .btn-extend-session, .btn-logout-now { flex: 1; padding: 12px 16px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: all 0.2s ease; border: none; font-size: 14px; }
            .btn-extend-session { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; }
            .btn-extend-session:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4); }
            .btn-logout-now { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; }
            .btn-logout-now:hover { background: linear-gradient(135deg, #4b5563 0%, #374151 100%); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(75, 85, 99, 0.4); }
            .session-progress-bar { height: 4px; background-color: #f3f4f6; overflow: hidden; }
            .session-progress-fill { height: 100%; background: linear-gradient(90deg, #dc2626 0%, #991b1b 100%); transition: width 1s linear; width: 100%; }
            
            /* Tooltips */
            .professional-tooltip { position: absolute; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); padding: 12px; max-width: 280px; z-index: 1000; opacity: 0; visibility: hidden; transform: translateY(-5px); transition: all 0.2s ease; pointer-events: none; }
            .professional-tooltip.show { opacity: 1; visibility: visible; transform: translateY(0); }
            .professional-tooltip::before { content: ''; position: absolute; top: -6px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-bottom: 6px solid white; filter: drop-shadow(0 -1px 1px rgba(0, 0, 0, 0.1)); }
            .tooltip-header { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; color: #1f2937; font-weight: 600; font-size: 13px; }
            .tooltip-content { font-size: 12px; line-height: 1.4; color: #6b7280; }
            
            /* Email Validation */
            .email-validation { display: flex; align-items: center; gap: 6px; font-size: 12px; margin-top: 4px; padding: 4px 8px; border-radius: 4px; animation: slideDown 0.2s ease-out; }
            .email-validation.valid { background-color: #ecfdf5; color: #16a34a; border: 1px solid #86efac; }
            .email-validation.invalid { background-color: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }
            
            /* Support Section */
            .support-contact-section { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-top: 20px; }
            .support-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
            .support-header h3 { margin: 0; color: #1f2937; font-size: 16px; font-weight: 600; }
            .support-content { display: grid; gap: 16px; }
            .support-item { background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; }
            .support-item strong { color: #374151; font-weight: 600; margin-bottom: 6px; display: block; }
            .support-item p { margin: 0; color: #6b7280; font-size: 14px; line-height: 1.5; }
            .contact-methods { display: flex; flex-direction: column; gap: 4px; }
            .contact-link { display: flex; align-items: center; gap: 8px; color: #2563eb; text-decoration: none; font-weight: 500; transition: color 0.2s ease; }
            .contact-link:hover { color: #1d4ed8; }
            .contact-info { font-size: 12px; color: #9ca3af; font-style: italic; }
            .system-status { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #374151; }
            .status-indicator { width: 8px; height: 8px; border-radius: 50%; position: relative; }
            .status-indicator.online { background-color: #16a34a; }
            .status-indicator.online::after { content: ''; position: absolute; width: 8px; height: 8px; border-radius: 50%; background-color: #16a34a; animation: pulse 2s infinite; }
            
            /* Notifications */
            .professional-notification { position: fixed; top: 20px; right: 20px; background: white; border-radius: 8px; box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); padding: 16px; max-width: 400px; z-index: 1000; display: flex; align-items: flex-start; gap: 12px; animation: slideInFromRight 0.3s ease-out; border-left: 4px solid #6b7280; }
            .professional-notification.success { border-left-color: #16a34a; }
            .professional-notification.error { border-left-color: #dc2626; }
            .professional-notification.warning { border-left-color: #f59e0b; }
            .professional-notification.info { border-left-color: #2563eb; }
            .notification-content { display: flex; align-items: flex-start; gap: 8px; flex: 1; }
            .notification-message { font-size: 14px; line-height: 1.4; color: #374151; }
            .notification-close { background: none; border: none; color: #9ca3af; cursor: pointer; padding: 2px; border-radius: 4px; transition: all 0.2s ease; flex-shrink: 0; }
            .notification-close:hover { color: #6b7280; background-color: #f3f4f6; }
            .professional-notification.fade-out { animation: fadeOut 0.3s ease-out forwards; }
            
            /* Animations */
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes slideIn { from { opacity: 0; transform: translateY(-20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
            @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes slideInFromRight { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
            @keyframes fadeOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(100%); } }
            @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.2); } }
            
            /* Responsive */
            @media (max-width: 640px) {
                .session-warning-modal { margin: 10px; width: calc(100% - 20px); }
                .session-warning-actions { flex-direction: column; }
                .professional-notification { top: 10px; right: 10px; left: 10px; max-width: none; }
                .support-contact-section { padding: 16px; margin-top: 16px; }
                .professional-tooltip { max-width: calc(100vw - 40px); left: 20px !important; right: 20px; transform: none !important; }
                .professional-tooltip.show { transform: none !important; }
            }
        `;
        
        if (!document.getElementById('professional-auth-styles')) {
            document.head.appendChild(style);
        }
    }

    destroy() {
        this.closeSessionWarning();
        this.tooltips.forEach(tooltip => tooltip.remove());
        this.tooltips.clear();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.authSecurity = new AuthSecurity();
    window.professionalAuthFeatures = new ProfessionalAuthFeatures();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (window.authSecurity) {
            window.authSecurity.cleanup();
        }
        if (window.professionalAuthFeatures) {
            window.professionalAuthFeatures.destroy();
        }
    });
});

// Additional styles for enhanced UI
const securityStyles = `
    <style>
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .hd-enhanced-checkbox-wrapper {
            position: relative;
        }
        
        .hd-enhanced-checkbox {
            appearance: none;
            -webkit-appearance: none;
            background-color: #fff;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            height: 18px;
            width: 18px;
            transition: all 0.2s ease;
        }
        
        .hd-enhanced-checkbox:checked {
            background-color: #1e40af;
            border-color: #1e40af;
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='m13.854 3.646-8 8a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L5.5 10.293l7.646-7.647a.5.5 0 0 1 .708.708z'/%3e%3c/svg%3e");
        }
        
        .hd-enhanced-checkbox:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        /* Disable text selection on labels */
        .select-none {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Enhanced error styling */
        .text-red-600 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .text-red-600:before {
            content: "⚠";
            color: #dc2626;
            font-weight: bold;
        }
    </style>
`;

// Inject styles
document.head.insertAdjacentHTML('beforeend', securityStyles);
