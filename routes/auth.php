<?php declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\LoginEnhancementController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PublicRegistrationController;
use App\Http\Controllers\Auth\PublicRegistrationValidationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ComprehensiveRegistrationController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Middleware\EnhancedLoginSecurity;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', EnhancedLoginSecurity::class])->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // Comprehensive login page route
    Route::get('login/comprehensive', function () {
        return view('auth.login-comprehensive');
    })->name('login.comprehensive');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Login enhancement endpoints
    Route::post('login/check-email', [LoginEnhancementController::class, 'checkEmail'])
        ->name('login.check-email')
        ->middleware('throttle:30,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // Registration routes
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // Comprehensive registration routes (recommended)
    Route::get('register/comprehensive', [ComprehensiveRegistrationController::class, 'create'])
        ->name('register.comprehensive');

    Route::post('register/comprehensive', [ComprehensiveRegistrationController::class, 'store']);

    // Comprehensive registration AJAX endpoints
    Route::post('register/comprehensive/check-email', [ComprehensiveRegistrationController::class, 'checkEmailAvailability'])
        ->name('register.comprehensive.check-email')
        ->middleware('throttle:30,1');

    Route::post('register/comprehensive/check-username', [ComprehensiveRegistrationController::class, 'checkUsernameAvailability'])
        ->name('register.comprehensive.check-username')
        ->middleware('throttle:30,1');

    Route::post('register/comprehensive/validate-password', [ComprehensiveRegistrationController::class, 'validatePasswordStrength'])
        ->name('register.comprehensive.validate-password')
        ->middleware('throttle:60,1');

    Route::post('register/comprehensive/validate-step', [ComprehensiveRegistrationController::class, 'validateStep'])
        ->name('register.comprehensive.validate-step')
        ->middleware('throttle:60,1');

    // Public registration routes (alternative path)
    Route::get('register/public', [PublicRegistrationController::class, 'create'])
        ->name('register.public');

    Route::post('register/public', [PublicRegistrationController::class, 'store']);

    // Progressive enhancement validation endpoints
    Route::post('register/public/validate', [PublicRegistrationValidationController::class, 'validate'])
        ->name('register.public.validate')
        ->middleware('throttle:60,1');

    Route::post('register/public/check-email', [PublicRegistrationValidationController::class, 'checkEmailAvailability'])
        ->name('register.public.check-email')
        ->middleware('throttle:30,1');

    Route::post('register/public/check-password', [PublicRegistrationValidationController::class, 'checkPasswordStrength'])
        ->name('register.public.check-password')
        ->middleware('throttle:60,1');

    // Two-Factor Authentication routes (guest access)
    Route::get('2fa/challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
});

/*
|--------------------------------------------------------------------------
| OAuth Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle OAuth authentication with third-party providers
| like Google, Facebook, etc. for the HD Tickets sports events system.
|
*/

Route::prefix('auth')->name('oauth.')->group(function (): void {
    // OAuth redirect routes (guest access)
    Route::middleware('guest')->group(function (): void {
        Route::get('{provider}', [OAuthController::class, 'redirect'])
            ->where('provider', 'google|facebook|twitter')
            ->name('redirect');
        
        Route::get('{provider}/callback', [OAuthController::class, 'callback'])
            ->where('provider', 'google|facebook|twitter')
            ->name('callback');
    });
    
    // OAuth account linking routes (authenticated access)
    Route::middleware('auth')->group(function (): void {
        Route::get('link', [OAuthController::class, 'linkAccount'])
            ->name('link');
        
        Route::get('{provider}/link', [OAuthController::class, 'redirect'])
            ->where('provider', 'google|facebook|twitter')
            ->name('link.redirect');
        
        Route::get('{provider}/link/callback', [OAuthController::class, 'linkCallback'])
            ->where('provider', 'google|facebook|twitter')
            ->name('link.callback');
        
        Route::delete('{provider}/unlink', [OAuthController::class, 'unlinkAccount'])
            ->where('provider', 'google|facebook|twitter')
            ->name('unlink');
    });
});

Route::middleware('auth')->group(function (): void {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Two-Factor Authentication routes (authenticated access)
    Route::get('2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('2fa/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('2fa.recovery-codes');
    Route::post('2fa/regenerate-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('2fa.regenerate-codes');
    Route::post('2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('2fa/sms-code', [TwoFactorController::class, 'sendSmsCode'])->name('2fa.sms-code');
    Route::post('2fa/email-code', [TwoFactorController::class, 'sendEmailCode'])->name('2fa.email-code');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Enhanced Password Management Routes
    Route::post('password/check-strength', [PasswordController::class, 'checkStrength'])
        ->middleware('throttle:60,1')
        ->name('password.check-strength');

    Route::get('password/requirements', [PasswordController::class, 'requirements'])
        ->name('password.requirements');

    Route::get('password/history-info', [PasswordController::class, 'historyInfo'])
        ->name('password.history-info');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
