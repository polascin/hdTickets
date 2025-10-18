<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    /**
     * __invoke
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: FALSE));
        }

        return view('auth.new-verify-email', [
            'email'        => $user->email,
            'name'         => $user->name,
            'createdAt'    => $user->created_at,
            'pendingSince' => $user->email_verified_at === NULL ? $user->created_at : NULL,
        ]);
    }
}