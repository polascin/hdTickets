<?php declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

/**
 * Honeypot Validation Rule
 *
 * Validates that honeypot fields remain empty to detect bot submissions.
 * When triggered, logs the potential bot activity for security monitoring.
 */
class HoneypotRule implements ValidationRule
{
    public function __construct(
        private string $fieldName = 'honeypot',
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Honeypot field should always be empty
        if (! empty($value)) {
            // Log potential bot activity
            Log::channel('security')->warning('Potential bot detected: Honeypot field filled', [
                'field'      => $attribute,
                'value'      => $value,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url'        => request()->fullUrl(),
                'session_id' => request()->session()->getId(),
                'timestamp'  => now()->toISOString(),
            ]);

            // Fail validation with a generic message
            $fail('Invalid form submission detected.');
        }
    }
}
