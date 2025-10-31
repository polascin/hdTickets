<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

use function array_slice;
use function count;
use function strlen;

class PasswordHistoryService
{
    /** Number of previous passwords to remember */
    public const PASSWORD_HISTORY_COUNT = 5;

    /** Minimum days before password can be reused */
    public const PASSWORD_REUSE_DAYS = 90;

    /**
     * Add a new password to user's history
     */
    /**
     * AddPasswordToHistory
     */
    public function addPasswordToHistory(User $user, string $password): void
    {
        $passwordHistory = $user->password_history ?? [];

        // Add new password with timestamp
        $newEntry = [
            'password_hash' => Hash::make($password),
            'created_at'    => now()->toISOString(),
        ];

        array_unshift($passwordHistory, $newEntry);

        // Keep only the last N passwords
        $passwordHistory = array_slice($passwordHistory, 0, self::PASSWORD_HISTORY_COUNT);

        $user->password_history = $passwordHistory;
        $user->save();
    }

    /**
     * Check if a password has been used recently
     */
    /**
     * Check if  password recently used
     */
    public function isPasswordRecentlyUsed(User $user, string $password): bool
    {
        $passwordHistory = $user->password_history ?? [];
        $cutoffDate = now()->subDays(self::PASSWORD_REUSE_DAYS);

        foreach ($passwordHistory as $historyEntry) {
            // Check if password matches
            if (Hash::check($password, $historyEntry['password_hash'])) {
                // If it's within the reuse period, it's not allowed
                $entryDate = Carbon::parse($historyEntry['created_at']);
                if ($entryDate->isAfter($cutoffDate)) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * Check if password matches current password
     */
    /**
     * Check if  current password
     */
    public function isCurrentPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * Get password history validation rules
     */
    /**
     * Get  password history validation rules
     */
    public function getPasswordHistoryValidationRules(User $user): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) use ($user): void {
                    if ($this->isCurrentPassword($user, $value)) {
                        $fail('The new password cannot be the same as your current password.');
                    }

                    if ($this->isPasswordRecentlyUsed($user, $value)) {
                        $fail('This password has been used recently. Please choose a different password.');
                    }
                },
            ],
        ];
    }

    /**
     * Get password reuse information
     */
    /**
     * Get  password reuse info
     */
    public function getPasswordReuseInfo(User $user): array
    {
        $passwordHistory = $user->password_history ?? [];

        return [
            'history_count'     => count($passwordHistory),
            'max_history_count' => self::PASSWORD_HISTORY_COUNT,
            'reuse_days'        => self::PASSWORD_REUSE_DAYS,
            'oldest_entry'      => empty($passwordHistory) ?
                NULL : Carbon::parse(end($passwordHistory)['created_at'])->format('M j, Y'),
            'newest_entry' => empty($passwordHistory) ?
                NULL : Carbon::parse($passwordHistory[0]['created_at'])->format('M j, Y'),
        ];
    }

    /**
     * Clean up old password history entries
     */
    /**
     * CleanupOldPasswords
     */
    public function cleanupOldPasswords(User $user): void
    {
        $passwordHistory = $user->password_history ?? [];
        $cutoffDate = now()->subDays(self::PASSWORD_REUSE_DAYS * 2); // Keep extra time for safety

        $cleanedHistory = array_filter($passwordHistory, function (array $entry) use ($cutoffDate): bool {
            $entryDate = Carbon::parse($entry['created_at']);

            return $entryDate->isAfter($cutoffDate);
        });

        // Ensure we don't have more than the limit
        $cleanedHistory = array_slice(array_values($cleanedHistory), 0, self::PASSWORD_HISTORY_COUNT);

        if (count($cleanedHistory) !== count($passwordHistory)) {
            $user->password_history = $cleanedHistory;
            $user->save();
        }
    }

    /**
     * Get password strength requirements
     */
    /**
     * Get  password requirements
     */
    public function getPasswordRequirements(): array
    {
        return [
            'min_length'            => 8,
            'recommended_length'    => 12,
            'require_lowercase'     => TRUE,
            'require_uppercase'     => TRUE,
            'require_numbers'       => TRUE,
            'require_special_chars' => TRUE,
            'history_count'         => self::PASSWORD_HISTORY_COUNT,
            'reuse_days'            => self::PASSWORD_REUSE_DAYS,
            'requirements'          => [
                'At least 8 characters long',
                'Contains at least one lowercase letter',
                'Contains at least one uppercase letter',
                'Contains at least one number',
                'Contains at least one special character (!@#$%^&*)',
                'Cannot be the same as your current password',
                'Cannot be one of your last ' . self::PASSWORD_HISTORY_COUNT . ' passwords',
                'Must be different from passwords used in the last ' . self::PASSWORD_REUSE_DAYS . ' days',
            ],
        ];
    }

    /**
     * Validate password strength
     */
    /**
     * ValidatePasswordStrength
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        $score = 0;
        $maxScore = 6;

        // Length check
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } else {
            $score++;
        }

        if (strlen($password) >= 12) {
            $score++;
        }

        // Character type checks
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        } else {
            $score++;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } else {
            $score++;
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Password must contain at least one number';
        } else {
            $score++;
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        } else {
            $score++;
        }

        $strengthPercentage = ($score / $maxScore) * 100;
        $strengthLabel = $this->getStrengthLabel($strengthPercentage);

        return [
            'is_valid'            => $errors === [],
            'errors'              => $errors,
            'score'               => $score,
            'max_score'           => $maxScore,
            'strength_percentage' => $strengthPercentage,
            'strength_label'      => $strengthLabel,
            'recommendations'     => $this->getPasswordRecommendations($password, $score),
        ];
    }

    /**
     * Get strength label based on percentage
     */
    /**
     * Get  strength label
     */
    private function getStrengthLabel(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'Very Strong';
        }
        if ($percentage >= 75) {
            return 'Strong';
        }
        if ($percentage >= 50) {
            return 'Fair';
        }
        if ($percentage >= 25) {
            return 'Weak';
        }

        return 'Very Weak';
    }

    /**
     * Get password improvement recommendations
     */
    /**
     * Get  password recommendations
     *
     * @return string[]
     */
    private function getPasswordRecommendations(string $password, int $currentScore): array
    {
        $recommendations = [];

        if (strlen($password) < 12) {
            $recommendations[] = 'Consider using at least 12 characters for better security';
        }

        if ($currentScore < 4) {
            $recommendations[] = 'Add a mix of uppercase, lowercase, numbers, and special characters';
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $recommendations[] = 'Include special characters like !@#$%^&*';
        }

        if (preg_match('/(.)\1{2,}/', $password)) {
            $recommendations[] = 'Avoid repeating the same character multiple times';
        }

        // Check for common patterns
        if (preg_match('/123|abc|password|qwerty/i', $password)) {
            $recommendations[] = 'Avoid common patterns and dictionary words';
        }

        return $recommendations;
    }
}
