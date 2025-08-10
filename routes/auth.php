<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
        
    // Two-Factor Authentication routes (guest access)
    Route::get('2fa/challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
});

Route::middleware('auth')->group(function () {
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
