/**
 * HD Tickets Login Enhancements
 * Modern login experience with security features, real-time validation, and UX improvements
 * 
 * Features:
 * - Device fingerprinting
 * - Real-time email validation
 * - Biometric authentication support
 * - Security monitoring
 * - Progressive enhancement
 * 
 * @version 2.0.0
 */

(function() {
    'use strict';

    const CONFIG = {
        CHECK_EMAIL: '/login/check-email',
    };

    document.addEventListener('DOMContentLoaded', () => {
        // Attach device fingerprint to forms if present
        const form = document.getElementById('login-form') || document.getElementById('enhanced-login-form');
        if (form) {
            const fpInput = form.querySelector('input[name="device_fingerprint"]');
            if (fpInput) {
                const fp = generateFingerprint();
                fpInput.value = fp;
            }
        }
    });

    function generateFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillText('HD Tickets Security Canvas', 2, 2);
            const data = {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                screen: { w: screen.width, h: screen.height, d: screen.colorDepth },
                canvas: canvas.toDataURL().slice(-50),
                ts: Date.now()
            };
            return btoa(JSON.stringify(data));
        } catch (e) {
            return btoa(JSON.stringify({ ua: navigator.userAgent, fallback: true, ts: Date.now() }));
        }
    }
})();

