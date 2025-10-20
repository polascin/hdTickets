<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    /**
     * Create
     */
    public function create(): View
    {
        // Check for enhanced login first (tests expect this)
        if (config('auth.enhanced_login', FALSE)) {
            return view('auth.login-enhanced');
        }

        // Check if comprehensive login is enabled
        if (config('auth.comprehensive_login', TRUE)) {
            return view('auth.login-comprehensive');
        }

        // Fallback to basic login
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    /**
     * Store
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Check if 2FA is required
        if ($request->requires2FA()) {
            return redirect()->route('2fa.challenge');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: FALSE));
    }

    /**
     * Destroy an authenticated session.
     */
    /**
     * Destroy
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
