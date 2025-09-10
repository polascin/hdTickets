<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use function in_array;

/**
 * Secure Error Messages Middleware
 *
 * Prevents user enumeration by providing generic error messages
 * while maintaining detailed logging for security monitoring.
 */
class SecureErrorMessages
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (SymfonyResponse) $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        // Only process authentication-related routes
        if (! $this->isAuthRoute($request)) {
            return $response;
        }

        // Check if this is a validation error response
        if ($response->getStatusCode() === 422 && $request->expectsJson()) {
            return $this->sanitizeJsonErrors($response, $request);
        }

        // For non-JSON requests, errors are handled in the LoginRequest
        return $response;
    }

    /**
     * Check if this is an authentication-related route
     */
    private function isAuthRoute(Request $request): bool
    {
        $authRoutes = [
            'login',
            'register',
            'password.request',
            'password.email',
            'password.reset',
            'password.store',
        ];

        return in_array($request->route()?->getName(), $authRoutes, TRUE)
               || str_contains($request->path(), 'login')
               || str_contains($request->path(), 'register')
               || str_contains($request->path(), 'password');
    }

    /**
     * Sanitize JSON error responses to prevent user enumeration
     */
    private function sanitizeJsonErrors(SymfonyResponse $response, Request $request): SymfonyResponse
    {
        $content = json_decode((string) $response->getContent(), TRUE);

        if (! isset($content['errors'])) {
            return $response;
        }

        $originalErrors = $content['errors'];
        $sanitizedErrors = [];

        foreach ($originalErrors as $field => $messages) {
            $sanitizedErrors[$field] = $this->sanitizeFieldErrors($field, $messages, $request);
        }

        // Log original errors for security monitoring
        $this->logSecurityEvent($originalErrors, $request);

        $content['errors'] = $sanitizedErrors;
        $response->setContent(json_encode($content));

        return $response;
    }

    /**
     * Sanitize error messages for a specific field
     */
    private function sanitizeFieldErrors(string $field, array $messages, Request $request): array
    {
        $sanitizedMessages = [];

        foreach ($messages as $message) {
            $sanitizedMessage = $this->getSanitizedMessage($field, $message);
            if ($sanitizedMessage !== '' && $sanitizedMessage !== '0') {
                $sanitizedMessages[] = $sanitizedMessage;
            }
        }

        return $sanitizedMessages ?: ['Invalid input provided.'];
    }

    /**
     * Get a sanitized version of the error message
     */
    private function getSanitizedMessage(string $field, string $message): string
    {
        // Map of potentially revealing messages to generic ones
        $messageMap = [
            // Authentication failures
            'These credentials do not match our records.' => 'Invalid login credentials.',
            'The provided credentials are incorrect.'     => 'Invalid login credentials.',
            'Invalid credentials provided.'               => 'Invalid login credentials.',
            'User not found.'                             => 'Invalid login credentials.',
            'Email not found in our records.'             => 'Invalid login credentials.',
            'Password is incorrect.'                      => 'Invalid login credentials.',

            // Account status messages (keep some specificity for UX)
            'Your account has been deactivated.'  => 'Your account has been deactivated. Please contact support.',
            'Your account is temporarily locked.' => 'Your account is temporarily locked for security reasons.',
            'Account verification required.'      => 'Account verification required. Please check your email.',

            // Rate limiting (keep informative for security)
            // Rate limiting messages are handled separately with countdown

            // Password reset
            'We can\'t find a user with that email address.' => 'If an account with that email exists, we\'ve sent password reset instructions.',
            'Password reset link has expired.'               => 'This password reset link has expired. Please request a new one.',
            'Invalid password reset token.'                  => 'This password reset link is invalid. Please request a new one.',

            // Registration
            'The email has already been taken.'   => 'This email address is already registered.',
            'Registration is currently disabled.' => 'New registrations are currently restricted.',
        ];

        // Check for exact matches first
        if (isset($messageMap[$message])) {
            return $messageMap[$message];
        }

        // Handle partial matches and patterns
        if (str_contains($message, 'credentials') || str_contains($message, 'password')) {
            return 'Invalid login credentials.';
        }

        if (str_contains($message, 'email') && str_contains($message, 'not found')) {
            return 'Invalid login credentials.';
        }

        if (str_contains($message, 'too many attempts') || str_contains($message, 'rate limit')) {
            // Rate limiting messages should be preserved for security UX
            return $message;
        }

        if (str_contains($message, 'locked') || str_contains($message, 'suspended')) {
            return 'Account access restricted. Please contact support.';
        }

        // Validation errors - preserve these as they don't reveal system information
        if ($field === 'email' && (str_contains($message, 'format') || str_contains($message, 'valid'))) {
            return 'Please enter a valid email address.';
        }

        if ($field === 'password' && str_contains($message, 'required')) {
            return 'Password is required.';
        }

        if ($field === 'password' && (str_contains($message, 'length') || str_contains($message, 'characters'))) {
            return 'Password must meet minimum requirements.';
        }

        // Default fallback for unknown messages
        return 'Please check your input and try again.';
    }

    /**
     * Log security events for monitoring
     */
    private function logSecurityEvent(array $originalErrors, Request $request): void
    {
        // Don't log routine validation errors
        $securityRelevantMessages = [
            'credentials',
            'not found',
            'locked',
            'suspended',
            'deactivated',
            'too many',
            'rate limit',
        ];

        $hasSecurityRelevantError = FALSE;
        foreach ($originalErrors as $messages) {
            foreach ($messages as $message) {
                foreach ($securityRelevantMessages as $keyword) {
                    if (str_contains(strtolower((string) $message), $keyword)) {
                        $hasSecurityRelevantError = TRUE;

                        break 3;
                    }
                }
            }
        }

        if ($hasSecurityRelevantError) {
            Log::channel('security')->info('Authentication attempt with security-relevant error', [
                'ip'              => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'route'           => $request->route()?->getName(),
                'original_errors' => $originalErrors,
                'timestamp'       => now()->toISOString(),
                'session_id'      => $request->session()->getId(),
            ]);
        }
    }
}
