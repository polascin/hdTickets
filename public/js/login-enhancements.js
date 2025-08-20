/**
 * HD Tickets Login Enhancements
 * 
 * Advanced security and UX enhancements for the login page:
 * - Advanced brute force protection
 * - Biometric authentication support
 * - Device fingerprinting
 * - Progressive form enhancement
 * - Smart form validation
 * - Session management
 * - Security monitoring
 */

class LoginEnhancements {
    constructor() {
        this.config = {
            maxRetries: 3,
            lockoutDuration: 300000, // 5 minutes
            sessionWarning: 300000,  // 5 minutes before expiry
            fingerprintEnabled: true,
            biometricEnabled: 'webauthn' in window,
            progressiveValidation: true
        };
        
        this.state = {
            attempts: 0,
            locked: false,
            deviceFingerprint: null,
            sessionTimer: null,
            validationState: {}
        };
        
        this.init();
    }

    init() {
        this.detectCapabilities();
        this.setupAdvancedSecurity();
        this.setupBiometricAuth();
        this.setupProgressiveValidation();
        this.setupSessionManagement();
        this.setupPerformanceMonitoring();
        this.setupAccessibilityEnhancements();
        this.monitorSecurityEvents();
    }

    detectCapabilities() {
        // Detect device capabilities
        this.capabilities = {
            touchScreen: 'ontouchstart' in window,
            biometric: 'webauthn' in window && 'credentials' in navigator,
            notifications: 'Notification' in window,
            storage: 'localStorage' in window,
            webGL: !!window.WebGLRenderingContext,
            battery: 'getBattery' in navigator,
            connection: 'connection' in navigator
        };

        // Generate device fingerprint
        if (this.config.fingerprintEnabled) {
            this.generateDeviceFingerprint();
        }
    }

    async generateDeviceFingerprint() {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillText('HD Tickets Security Check', 2, 2);

        const fingerprint = {
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            screen: `${screen.width}x${screen.height}`,
            canvas: canvas.toDataURL(),
            timestamp: Date.now()
        };

        this.state.deviceFingerprint = btoa(JSON.stringify(fingerprint));
        
        // Add fingerprint to form
        const form = document.getElementById('login-form');
        if (form) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'device_fingerprint';
            input.value = this.state.deviceFingerprint;
            form.appendChild(input);
        }
    }

    setupAdvancedSecurity() {
        // Monitor for suspicious activity
        this.detectAutomation();
        this.setupRateLimiting();
        this.monitorFormInteraction();
        this.setupCSRFProtection();
    }

    detectAutomation() {
        // Detect automated tools
        const checks = {
            webdriver: navigator.webdriver,
            phantom: window.callPhantom || window._phantom,
            selenium: window.__selenium_unwrapped || window.__selenium_evaluate,
            automation: window.automation || window.__nightmare
        };

        if (Object.values(checks).some(check => check)) {
            this.flagSuspiciousActivity('automation_detected');
        }

        // Check for headless browsers
        if (navigator.webdriver === true || 
            navigator.languages.length === 0 ||
            !navigator.plugins.length) {
            this.flagSuspiciousActivity('potential_headless');
        }
    }

    setupBiometricAuth() {
        if (!this.capabilities.biometric) return;

        const biometricBtn = this.createBiometricButton();
        const form = document.getElementById('login-form');
        
        if (form && biometricBtn) {
            const emailGroup = form.querySelector('.hd-form-group');
            if (emailGroup) {
                emailGroup.insertAdjacentElement('beforebegin', biometricBtn);
            }
        }
    }

    createBiometricButton() {
        const container = document.createElement('div');
        container.className = 'mb-4';
        container.innerHTML = `
            <button type="button" 
                    id="biometric-login" 
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-600 hover:to-purple-700 transition-all duration-200 flex items-center justify-center space-x-2"
                    aria-label="Sign in with biometric authentication">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span>Sign in with Biometric</span>
            </button>
            <div class="text-xs text-center mt-2 text-gray-500">
                Use fingerprint, Face ID, or other biometric authentication
            </div>
        `;

        const button = container.querySelector('#biometric-login');
        button.addEventListener('click', () => this.handleBiometricLogin());

        return container;
    }

    async handleBiometricLogin() {
        try {
            const credential = await navigator.credentials.create({
                publicKey: {
                    challenge: new Uint8Array(32),
                    rp: { name: "HD Tickets" },
                    user: {
                        id: new Uint8Array(16),
                        name: "user@hdtickets.local",
                        displayName: "HD Tickets User"
                    },
                    pubKeyCredParams: [{alg: -7, type: "public-key"}],
                    timeout: 60000,
                    attestation: "direct"
                }
            });

            if (credential) {
                this.processBiometricCredential(credential);
            }
        } catch (error) {
            console.warn('Biometric authentication failed:', error);
            this.showMessage('Biometric authentication is not available. Please use your email and password.', 'warning');
        }
    }

    setupProgressiveValidation() {
        const emailField = document.getElementById('email');
        const passwordField = document.getElementById('password');

        if (emailField) {
            emailField.addEventListener('input', this.debounce(() => {
                this.validateEmailProgressive(emailField);
            }, 300));

            emailField.addEventListener('blur', () => {
                this.validateEmailProgressive(emailField);
            });
        }

        if (passwordField) {
            passwordField.addEventListener('input', this.debounce(() => {
                this.validatePasswordProgressive(passwordField);
            }, 300));
        }
    }

    validateEmailProgressive(field) {
        const email = field.value.trim();
        const isValid = this.isValidEmail(email);
        
        this.updateFieldState(field, isValid, isValid ? 'valid' : 'Email format is invalid');
        
        if (isValid) {
            this.checkEmailExists(email);
        }
    }

    async checkEmailExists(email) {
        try {
            const response = await fetch('/api/auth/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email })
            });

            const result = await response.json();
            const emailField = document.getElementById('email');
            
            if (result.exists) {
                this.updateFieldState(emailField, true, 'Email found');
                this.preloadUserPreferences(result.preferences);
            } else {
                this.updateFieldState(emailField, false, 'Email not found in our system');
            }
        } catch (error) {
            console.warn('Email check failed:', error);
        }
    }

    validatePasswordProgressive(field) {
        const password = field.value;
        const strength = this.calculatePasswordStrength(password);
        
        this.updatePasswordStrengthIndicator(strength);
    }

    calculatePasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            numbers: /\d/.test(password),
            symbols: /[^A-Za-z0-9]/.test(password)
        };

        score = Object.values(checks).filter(Boolean).length;
        
        return {
            score,
            checks,
            strength: score <= 2 ? 'weak' : score <= 4 ? 'medium' : 'strong'
        };
    }

    updatePasswordStrengthIndicator(strength) {
        let indicator = document.getElementById('password-strength');
        
        if (!indicator) {
            indicator = this.createPasswordStrengthIndicator();
        }

        const colors = {
            weak: 'bg-red-500',
            medium: 'bg-yellow-500',
            strong: 'bg-green-500'
        };

        indicator.className = `h-2 rounded transition-all duration-200 ${colors[strength.strength]}`;
        indicator.style.width = `${(strength.score / 5) * 100}%`;
    }

    createPasswordStrengthIndicator() {
        const passwordField = document.getElementById('password');
        if (!passwordField) return null;

        const container = document.createElement('div');
        container.className = 'mt-2';
        container.innerHTML = `
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Password Strength</span>
                <span id="strength-text">Enter password</span>
            </div>
            <div class="w-full bg-gray-200 rounded">
                <div id="password-strength" class="h-2 rounded transition-all duration-200" style="width: 0%"></div>
            </div>
        `;

        passwordField.parentElement.appendChild(container);
        return container.querySelector('#password-strength');
    }

    setupSessionManagement() {
        // Monitor session activity
        this.startSessionMonitoring();
        this.setupIdleDetection();
        this.setupVisibilityTracking();
    }

    startSessionMonitoring() {
        setInterval(() => {
            this.checkSessionStatus();
        }, 60000); // Check every minute
    }

    async checkSessionStatus() {
        try {
            const response = await fetch('/api/session/status');
            const data = await response.json();
            
            if (data.expires_soon) {
                this.showSessionWarning(data.time_remaining);
            }
        } catch (error) {
            console.warn('Session check failed:', error);
        }
    }

    showSessionWarning(timeRemaining) {
        const minutes = Math.ceil(timeRemaining / 60000);
        this.showMessage(
            `Your session will expire in ${minutes} minutes. Please save your work.`,
            'warning'
        );
    }

    setupPerformanceMonitoring() {
        // Monitor page load performance
        window.addEventListener('load', () => {
            if ('performance' in window) {
                const perfData = performance.getEntriesByType('navigation')[0];
                
                // Send performance metrics
                this.reportPerformance({
                    loadTime: perfData.loadEventEnd - perfData.fetchStart,
                    domReady: perfData.domContentLoadedEventEnd - perfData.fetchStart,
                    timestamp: Date.now()
                });
            }
        });
    }

    // Utility methods
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

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    updateFieldState(field, isValid, message) {
        const container = field.parentElement;
        let feedback = container.querySelector('.field-feedback');
        
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'field-feedback text-xs mt-1';
            container.appendChild(feedback);
        }

        field.classList.toggle('border-green-500', isValid);
        field.classList.toggle('border-red-500', !isValid);
        feedback.className = `field-feedback text-xs mt-1 ${isValid ? 'text-green-600' : 'text-red-600'}`;
        feedback.textContent = message;
    }

    showMessage(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-black' :
            type === 'success' ? 'bg-green-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    flagSuspiciousActivity(type) {
        console.warn(`Suspicious activity detected: ${type}`);
        // Report to security monitoring system
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new LoginEnhancements();
});
