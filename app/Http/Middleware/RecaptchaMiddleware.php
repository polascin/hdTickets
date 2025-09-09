<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RecaptchaMiddleware
{
    public function __construct(
        private RecaptchaService $recaptchaService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action = 'login'): Response
    {
        // Only validate POST requests that might require CAPTCHA
        if (!$request->isMethod('POST')) {
            return $next($request);
        }

        // Skip if reCAPTCHA is disabled
        if (!$this->recaptchaService->isEnabled()) {
            return $next($request);
        }

        // Check if this request should be challenged
        $shouldChallenge = $this->recaptchaService->shouldChallenge($request);
        
        // Always verify token if present, regardless of challenge status
        $recaptchaToken = $request->input('g-recaptcha-response') ?: 
                         $request->input('recaptcha_token');

        if ($recaptchaToken) {
            $verification = $this->recaptchaService->verify(
                $recaptchaToken, 
                $action, 
                $request->ip()
            );

            // Check if verification passed
            if (!$this->recaptchaService->passes($verification)) {
                $this->handleFailedVerification($verification, $request);
            }
        } elseif ($shouldChallenge) {
            // No token provided but challenge is required
            throw ValidationException::withMessages([
                'recaptcha' => 'Security verification is required. Please complete the CAPTCHA.',
                'challenge_required' => true,
                'error_type' => 'recaptcha_required'
            ]);
        }

        return $next($request);
    }

    /**
     * Handle failed reCAPTCHA verification
     */
    private function handleFailedVerification(array $verification, Request $request): void
    {
        $ip = $request->ip();
        $score = $verification['score'] ?? 0.0;
        $errorCodes = $verification['error-codes'] ?? [];

        // Increment failed attempts for this IP
        $failedKey = "recaptcha_failed:{$ip}";
        RateLimiter::hit($failedKey, 60); // 1 hour decay

        // Determine error message based on verification result
        $message = $this->getErrorMessage($verification);
        $suggestions = $this->getErrorSuggestions($verification);

        throw ValidationException::withMessages([
            'recaptcha' => $message,
            'recaptcha_suggestions' => $suggestions,
            'error_type' => 'recaptcha_failed',
            'score' => $score,
            'error_codes' => $errorCodes,
            'challenge_required' => true
        ]);
    }

    /**
     * Get user-friendly error message based on verification result
     */
    private function getErrorMessage(array $verification): string
    {
        $errorCodes = $verification['error-codes'] ?? [];
        $score = $verification['score'] ?? 0.0;

        if (in_array('timeout-or-duplicate', $errorCodes)) {
            return 'Security verification expired. Please try again.';
        }

        if (in_array('invalid-input-response', $errorCodes)) {
            return 'Invalid security verification. Please refresh and try again.';
        }

        if (in_array('token-already-used', $errorCodes)) {
            return 'Security verification was already used. Please refresh and try again.';
        }

        if (in_array('action-mismatch', $errorCodes)) {
            return 'Security verification failed. Please refresh and try again.';
        }

        if ($score < 0.1) {
            return 'Security verification indicates high risk. Please contact support if you believe this is an error.';
        }

        if ($score < 0.3) {
            return 'Security verification failed due to suspicious activity. Please try again or contact support.';
        }

        return 'Security verification failed. Please try again.';
    }

    /**
     * Get helpful suggestions based on verification result
     */
    private function getErrorSuggestions(array $verification): array
    {
        $errorCodes = $verification['error-codes'] ?? [];
        $suggestions = [];

        if (in_array('timeout-or-duplicate', $errorCodes)) {
            $suggestions = [
                'Refresh the page and try again',
                'Make sure you complete the verification quickly',
                'Check your internet connection'
            ];
        } elseif (in_array('token-already-used', $errorCodes)) {
            $suggestions = [
                'Refresh the page to get a new verification',
                'Do not use the browser back button after submitting',
                'Clear your browser cache if the issue persists'
            ];
        } elseif (in_array('action-mismatch', $errorCodes)) {
            $suggestions = [
                'Refresh the page completely',
                'Make sure JavaScript is enabled',
                'Try a different browser if the issue continues'
            ];
        } else {
            $suggestions = [
                'Refresh the page and try logging in again',
                'Make sure JavaScript is enabled in your browser',
                'Try using a different browser or device',
                'Contact support if you continue having issues'
            ];
        }

        return $suggestions;
    }
}
