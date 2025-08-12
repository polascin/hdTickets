<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordChangedNotification;
use App\Services\PasswordCompromiseCheckService;
use App\Services\PasswordHistoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Log;

class PasswordController extends Controller
{
    protected $passwordHistoryService;

    protected $compromiseCheckService;

    public function __construct(
        PasswordHistoryService $passwordHistoryService,
        PasswordCompromiseCheckService $compromiseCheckService,
    ) {
        $this->passwordHistoryService = $passwordHistoryService;
        $this->compromiseCheckService = $compromiseCheckService;
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Get password history validation rules
        $historyRules = $this->passwordHistoryService->getPasswordHistoryValidationRules($user);

        // Add compromise check rule
        $historyRules['password'][] = $this->compromiseCheckService->getCompromiseValidationRule(FALSE);

        // Add current password verification
        $historyRules['current_password'] = ['required', 'current_password'];

        $validated = $request->validateWithBag('updatePassword', $historyRules);

        // Store old password in history before updating
        $this->passwordHistoryService->addPasswordToHistory($user, $user->password);

        // Update password
        $user->update([
            'password'            => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ]);

        // Send email notification about password change
        try {
            Mail::to($user->email)->send(new PasswordChangedNotification($user));
        } catch (Exception $e) {
            // Log error but don't fail the password update
            Log::warning('Failed to send password change notification', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'password-updated');
    }

    /**
     * Check password strength (AJAX)
     */
    public function checkStrength(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $password = $request->input('password');
        $user = $request->user();

        // Check password strength
        $strengthResult = $this->passwordHistoryService->validatePasswordStrength($password);

        // Check if password was used before
        $isRecentlyUsed = $this->passwordHistoryService->isPasswordRecentlyUsed($user, $password);
        $isCurrentPassword = $this->passwordHistoryService->isCurrentPassword($user, $password);

        // Check compromise status
        $compromiseResult = $this->compromiseCheckService->checkPasswordCompromise($password);

        return response()->json([
            'strength'      => $strengthResult,
            'history_check' => [
                'is_recently_used'    => $isRecentlyUsed,
                'is_current_password' => $isCurrentPassword,
                'message'             => $this->getHistoryMessage($isRecentlyUsed, $isCurrentPassword),
            ],
            'compromise_check' => $compromiseResult,
            'overall_status'   => $this->getOverallStatus($strengthResult, $isRecentlyUsed, $isCurrentPassword, $compromiseResult),
        ]);
    }

    /**
     * Get password requirements
     */
    public function requirements(): JsonResponse
    {
        $requirements = $this->passwordHistoryService->getPasswordRequirements();

        return response()->json($requirements);
    }

    /**
     * Get password history info for current user
     */
    public function historyInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        $info = $this->passwordHistoryService->getPasswordReuseInfo($user);

        return response()->json($info);
    }

    /**
     * Get history message based on checks
     */
    private function getHistoryMessage(bool $isRecentlyUsed, bool $isCurrentPassword): string
    {
        if ($isCurrentPassword) {
            return 'This is your current password. Please choose a different password.';
        }

        if ($isRecentlyUsed) {
            return 'This password has been used recently. Please choose a different password.';
        }

        return 'This password has not been used recently.';
    }

    /**
     * Get overall password status
     */
    private function getOverallStatus(array $strengthResult, bool $isRecentlyUsed, bool $isCurrentPassword, array $compromiseResult): array
    {
        $errors = [];
        $warnings = [];
        $isValid = TRUE;

        // Check strength
        if (! $strengthResult['is_valid']) {
            $errors = array_merge($errors, $strengthResult['errors']);
            $isValid = FALSE;
        }

        // Check history
        if ($isCurrentPassword) {
            $errors[] = 'Cannot use your current password';
            $isValid = FALSE;
        } elseif ($isRecentlyUsed) {
            $errors[] = 'This password was used recently';
            $isValid = FALSE;
        }

        // Check compromise
        if ($compromiseResult['is_compromised']) {
            if ($compromiseResult['severity'] === 'critical' || $compromiseResult['breach_count'] >= 100) {
                $errors[] = $compromiseResult['message'];
                $isValid = FALSE;
            } else {
                $warnings[] = $compromiseResult['message'];
            }
        }

        return [
            'is_valid'       => $isValid,
            'errors'         => $errors,
            'warnings'       => $warnings,
            'strength_score' => $strengthResult['strength_percentage'],
            'strength_label' => $strengthResult['strength_label'],
        ];
    }
}
