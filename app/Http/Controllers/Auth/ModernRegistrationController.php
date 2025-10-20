<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

use function strlen;

/**
 * Modern Registration Controller
 *
 * Handles public user registration with clean architecture,
 * real-time validation, and progressive enhancement.
 */
class ModernRegistrationController extends Controller
{
    /**
     * Show the registration form
     */
    public function create(): View
    {
        return view('auth.modern-register');
    }

    /**
     * Handle registration submission
     */
    public function store(RegistrationRequest $request): RedirectResponse
    {
        // Anti-bot honeypot check
        if ($request->filled('website_url')) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Registration failed. Please try again.']);
        }

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Create user
            $user = User::create([
                'name'                => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'first_name'          => $validated['first_name'],
                'last_name'           => $validated['last_name'],
                'email'               => $validated['email'],
                'phone'               => $validated['phone'] ?? NULL,
                'password'            => Hash::make($validated['password']),
                'role'                => User::ROLE_CUSTOMER,
                'is_active'           => TRUE,
                'registration_source' => 'public_web',
                'password_changed_at' => now(),
                'terms_accepted_at'   => now(),
                'privacy_accepted_at' => now(),
                'marketing_opt_in'    => $request->boolean('marketing_opt_in'),
            ]);

            DB::commit();

            // Fire registered event
            event(new Registered($user));

            // Log user in
            Auth::login($user);

            // Send email verification
            $user->sendEmailVerificationNotification();

            return redirect()
                ->route('verification.notice')
                ->with('status', 'Registration successful! Please check your email to verify your account.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['registration' => 'Registration failed. Please try again.']);
        }
    }

    /**
     * Real-time email availability check
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'available' => ! $exists,
            'message'   => $exists ? 'This email is already registered.' : 'Email is available.',
        ]);
    }

    /**
     * Real-time password strength validation
     */
    public function checkPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid'   => FALSE,
                'message' => $validator->errors()->first('password'),
            ]);
        }

        // Check password strength
        $password = $request->password;
        $strength = $this->calculatePasswordStrength($password);

        return response()->json([
            'valid'    => TRUE,
            'strength' => $strength,
            'message'  => $this->getPasswordStrengthMessage($strength),
        ]);
    }

    /**
     * Comprehensive field validation for progressive enhancement
     */
    public function validateField(Request $request): JsonResponse
    {
        $field = $request->field;
        $value = $request->value;

        $rules = $this->getFieldValidationRules($field);

        if (! $rules) {
            return response()->json(['valid' => FALSE, 'message' => 'Invalid field']);
        }

        $validator = Validator::make([$field => $value], [$field => $rules]);

        if ($validator->fails()) {
            return response()->json([
                'valid'   => FALSE,
                'message' => $validator->errors()->first($field),
            ]);
        }

        // Special handling for email uniqueness
        if ($field === 'email') {
            $exists = User::where('email', $value)->exists();
            if ($exists) {
                return response()->json([
                    'valid'   => FALSE,
                    'message' => 'This email is already registered.',
                ]);
            }
        }

        return response()->json([
            'valid'   => TRUE,
            'message' => 'Valid',
        ]);
    }

    /**
     * Calculate password strength score
     */
    private function calculatePasswordStrength(string $password): int
    {
        $score = 0;

        // Length
        if (strlen($password) >= 8) {
            $score += 25;
        }
        if (strlen($password) >= 12) {
            $score += 25;
        }

        // Character types
        if (preg_match('/[a-z]/', $password)) {
            $score += 15;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $score += 15;
        }
        if (preg_match('/[0-9]/', $password)) {
            $score += 10;
        }
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 10;
        }

        return min(100, $score);
    }

    /**
     * Get password strength message
     */
    private function getPasswordStrengthMessage(int $strength): string
    {
        return match (TRUE) {
            $strength >= 80 => 'Strong password',
            $strength >= 60 => 'Good password',
            $strength >= 40 => 'Fair password',
            default         => 'Weak password',
        };
    }

    /**
     * Get validation rules for specific field
     */
    private function getFieldValidationRules(string $field): ?array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'password'   => ['required', 'string', 'min:8'],
        ];

        return $rules[$field] ?? NULL;
    }
}
