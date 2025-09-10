<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class PhoneVerificationService
{
    private ?Client $twilio = NULL;

    public function __construct()
    {
        // Only initialize Twilio if we have valid credentials
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');

        if ($this->hasValidCredentials($sid, $token)) {
            $this->twilio = new Client($sid, $token);
        }
    }

    /**
     * Send verification code to user's phone
     */
    public function sendVerificationCode(User $user): bool
    {
        if (! $user->phone) {
            throw new Exception('User has no phone number');
        }

        // Generate 6-digit code
        $code = $this->generateVerificationCode();

        // Store code in cache for 10 minutes
        $cacheKey = "phone_verification:{$user->id}";
        Cache::put($cacheKey, $code, now()->addMinutes(10));

        // If no Twilio client or in development mode, just log the code
        if (! $this->twilio || $this->isDevelopmentMode()) {
            Log::info('Phone verification code (DEVELOPMENT MODE)', [
                'user_id' => $user->id,
                'phone'   => $user->phone,
                'code'    => $code,
                'message' => "Your HD Tickets verification code is: {$code}. Valid for 10 minutes.",
                'note'    => 'SMS not sent - using development mode or invalid Twilio credentials',
            ]);

            return TRUE;
        }

        try {
            // Send SMS via Twilio
            $message = $this->twilio->messages->create(
                $user->phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Your HD Tickets verification code is: {$code}. Valid for 10 minutes.",
                ],
            );

            Log::info('Phone verification code sent', [
                'user_id'     => $user->id,
                'phone'       => $user->phone,
                'message_sid' => $message->sid,
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to send phone verification code', [
                'user_id' => $user->id,
                'phone'   => $user->phone,
                'error'   => $e->getMessage(),
            ]);

            // In development mode, don't throw exception, just log and continue
            if ($this->isDevelopmentMode()) {
                Log::warning('SMS sending failed in development mode, continuing anyway', [
                    'user_id' => $user->id,
                    'code'    => $code,
                    'error'   => $e->getMessage(),
                ]);

                return TRUE;
            }

            throw new Exception('Failed to send verification code', $e->getCode(), $e);
        }
    }

    /**
     * Verify the provided code
     */
    public function verifyCode(User $user, string $code): bool
    {
        $cacheKey = "phone_verification:{$user->id}";
        $storedCode = Cache::get($cacheKey);

        if (! $storedCode || $storedCode !== $code) {
            return FALSE;
        }

        // Clear the verification code
        Cache::forget($cacheKey);

        Log::info('Phone verification successful', [
            'user_id' => $user->id,
            'phone'   => $user->phone,
        ]);

        return TRUE;
    }

    /**
     * Check if user can request a new code (rate limiting)
     */
    public function canRequestNewCode(User $user): bool
    {
        $rateLimitKey = "phone_verification_rate_limit:{$user->id}";

        return ! Cache::has($rateLimitKey);
    }

    /**
     * Apply rate limiting for verification code requests
     */
    public function applyRateLimit(User $user): void
    {
        $rateLimitKey = "phone_verification_rate_limit:{$user->id}";
        Cache::put($rateLimitKey, TRUE, now()->addMinutes(1)); // 1 minute rate limit
    }

    /**
     * Check if we have valid Twilio credentials
     */
    private function hasValidCredentials(?string $sid, ?string $token): bool
    {
        return $sid !== NULL && $sid !== '' && $sid !== '0'
               && ($token !== NULL && $token !== '' && $token !== '0')
               && $sid !== 'your_twilio_sid'
               && $token !== 'your_twilio_token';
    }

    /**
     * Check if we're in development mode
     */
    private function isDevelopmentMode(): bool
    {
        if (app()->environment('local', 'testing')) {
            return TRUE;
        }

        return (bool) config('app.debug');
    }

    /**
     * Generate 6-digit verification code
     */
    private function generateVerificationCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
