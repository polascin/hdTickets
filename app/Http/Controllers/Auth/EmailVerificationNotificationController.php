<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    /**
     * Store
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: FALSE));
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'We could not send the verification email. Please try again later.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
