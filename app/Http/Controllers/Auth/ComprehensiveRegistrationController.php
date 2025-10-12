<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ComprehensiveRegistrationRequest;
use App\Models\LegalDocument;
use App\Models\User;
use App\Models\UserLegalAcceptance;
use App\Services\PhoneVerificationService;
use App\Services\TwoFactorAuthService;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ComprehensiveRegistrationController extends Controller
{
    public function __construct(
        private PhoneVerificationService $phoneService,
        private TwoFactorAuthService $twoFactorService,
    ) {
    }

    /**
     * Show the comprehensive registration form
     */
    public function create(): View
    {
        // Get all required legal documents
        $legalDocuments = [];
        if (class_exists(LegalDocument::class) && method_exists(LegalDocument::class, 'getActiveRequiredDocuments')) {
            $legalDocuments = LegalDocument::getActiveRequiredDocuments();
        }

        return view('auth.register-new', [
            'legalDocuments'       => $legalDocuments,
            'availableRoles'       => $this->getAvailableRoles(),
            'passwordRequirements' => $this->getPasswordRequirements(),
        ]);
    }

    /**
     * Handle comprehensive registration request
     */
    public function store(ComprehensiveRegistrationRequest $request): RedirectResponse|JsonResponse
    {
        // Rate limiting
        $key = 'registration-attempt:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Too many registration attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($key, 900); // 15 minutes

        // Get validated data with defaults
        $validated = $request->validatedWithDefaults();

        DB::beginTransaction();

        try {
            // Create the user
            $user = $this->createUser($validated);

            // Handle legal document acceptances
            $this->handleLegalAcceptances($user, $validated['legal_acceptances'] ?? []);

            // Send email verification
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
            }

            // Fire registered event
            event(new Registered($user));

            DB::commit();

            // Log successful registration
            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'role'    => $user->role,
                'ip'      => $request->ip(),
            ]);

            // Clear rate limiter on success
            RateLimiter::clear($key);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'      => TRUE,
                    'message'      => 'Registration successful! Please check your email to verify your account.',
                    'redirect_url' => route('verification.notice'),
                ]);
            }

            // Auto-login for customers (agents/admins should be manually activated)
            if ($user->role === User::ROLE_CUSTOMER) {
                Auth::login($user);

                return redirect()->route('verification.notice')
                    ->with('success', 'Welcome! Your account has been created. Please verify your email address.');
            }

            return redirect()->route('login')
                ->with('success', 'Your account has been created and is pending approval. You will receive an email once activated.');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Registration failed', [
                'email' => $validated['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Registration failed. Please try again.',
                    'errors'  => ['general' => [$e->getMessage()]],
                ], 422);
            }

            return back()->withInput()->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Check email availability (AJAX endpoint)
     */
    public function checkEmailAvailability(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'available' => !$exists,
            'message'   => $exists ? 'This email is already registered.' : 'Email is available.',
        ]);
    }

    /**
     * Check username availability (AJAX endpoint)
     */
    public function checkUsernameAvailability(Request $request): JsonResponse
    {
        $request->validate(['username' => ['required', 'string', 'min:3']]);

        $exists = User::where('username', $request->username)->exists();

        return response()->json([
            'available' => !$exists,
            'message'   => $exists ? 'This username is already taken.' : 'Username is available.',
        ]);
    }

    /**
     * Validate password strength (AJAX endpoint)
     */
    public function validatePasswordStrength(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', Password::defaults()],
        ]);

        $strength = $this->calculatePasswordStrength($request->password ?? '');

        return response()->json([
            'valid'        => !$validator->fails(),
            'strength'     => $strength,
            'errors'       => $validator->errors()->get('password'),
            'requirements' => $this->getPasswordRequirements(),
        ]);
    }

    /**
     * Progressive validation for registration form
     */
    public function validateStep(Request $request): JsonResponse
    {
        $step = $request->input('step');
        $rules = $this->getValidationRulesForStep($step);

        $validator = Validator::make($request->all(), $rules);

        return response()->json([
            'valid'  => !$validator->fails(),
            'errors' => $validator->errors(),
        ]);
    }

    /**
     * Validate the complete registration request
     */
    private function validateRegistration(Request $request): array
    {
        $rules = [
            // Personal Information
            'first_name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'last_name'  => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'username'   => ['nullable', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_\-\.]+$/', 'unique:users'],
            'phone'      => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[1-9][\d]{0,15}$/'],

            // Account Security
            'password'              => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required'],

            // Account Type & Preferences
            'role'     => ['required', 'in:customer,agent'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:5'],

            // Legal Acceptances
            'legal_acceptances'   => ['required', 'array'],
            'legal_acceptances.*' => ['required', 'boolean', 'accepted'],

            // Marketing
            'marketing_emails'        => ['boolean'],
            'newsletter_subscription' => ['boolean'],

            // Security Options
            'enable_2fa' => ['boolean'],

            // Referral (optional)
            'referral_code' => ['nullable', 'string', 'max:50'],
        ];

        return $request->validate($rules);
    }

    /**
     * Create a new user with the validated data
     */
    private function createUser(array $validated): User
    {
        return User::create([
            'name'                    => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'surname'                 => $validated['last_name'],
            'email'                   => $validated['email'],
            'username'                => $validated['username'],
            'phone'                   => $validated['phone'] ?? NULL,
            'password'                => Hash::make($validated['password']),
            'role'                    => $validated['role'],
            'timezone'                => $validated['timezone'] ?? config('app.timezone'),
            'language'                => $validated['language'] ?? config('app.locale'),
            'is_active'               => $validated['role'] === User::ROLE_CUSTOMER,
            'require_2fa'             => $validated['enable_2fa'] ?? FALSE,
            'marketing_emails'        => $validated['marketing_emails'] ?? FALSE,
            'newsletter_subscription' => $validated['newsletter_subscription'] ?? FALSE,
            'registration_source'     => 'comprehensive_public',
            'referral_code'           => $validated['referral_code'] ?? NULL,
            'password_changed_at'     => now(),
            'registration_ip'         => request()->ip(),
            'registration_user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle legal document acceptances
     */
    private function handleLegalAcceptances(User $user, array $acceptances): void
    {
        if (!class_exists(LegalDocument::class) || !class_exists(UserLegalAcceptance::class)) {
            return; // Skip if legal document system is not available
        }

        foreach ($acceptances as $documentType => $accepted) {
            if ($accepted && method_exists(LegalDocument::class, 'getActiveDocument')) {
                $document = LegalDocument::getActiveDocument($documentType);
                if ($document) {
                    UserLegalAcceptance::create([
                        'user_id'           => $user->id,
                        'legal_document_id' => $document->id,
                        'accepted_at'       => now(),
                        'ip_address'        => request()->ip(),
                        'user_agent'        => request()->userAgent(),
                    ]);
                }
            }
        }
    }

    /**
     * Generate a unique username from first and last name
     */
    private function generateUsername(string $firstName, string $lastName): string
    {
        $baseUsername = strtolower($firstName . '.' . $lastName);
        $baseUsername = preg_replace('/[^a-z0-9\.]/', '', $baseUsername);

        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get available roles for public registration
     */
    private function getAvailableRoles(): array
    {
        return [
            User::ROLE_CUSTOMER => [
                'label'       => 'Sports Fan',
                'description' => 'Perfect for individual sports enthusiasts',
                'features'    => [
                    'Monitor ticket prices across platforms',
                    'Set price drop alerts',
                    'Purchase tickets with ease',
                    'Access to mobile app',
                ],
                'price' => '$29.99/month',
                'trial' => '7-day free trial',
            ],
            User::ROLE_AGENT => [
                'label'       => 'Business/Professional',
                'description' => 'For businesses and professional ticket buyers',
                'features'    => [
                    'Advanced analytics and reporting',
                    'Bulk ticket purchasing',
                    'API access for integrations',
                    'Priority customer support',
                    'Custom price alerts',
                ],
                'price' => '$99.99/month',
                'trial' => '14-day free trial',
            ],
        ];
    }

    /**
     * Calculate password strength score
     */
    private function calculatePasswordStrength(string $password): array
    {
        $score = 0;
        $checks = [
            'length'    => strlen($password) >= 8,
            'lowercase' => preg_match('/[a-z]/', $password),
            'uppercase' => preg_match('/[A-Z]/', $password),
            'numbers'   => preg_match('/[0-9]/', $password),
            'special'   => preg_match('/[^a-zA-Z0-9]/', $password),
            'long'      => strlen($password) >= 12,
        ];

        foreach ($checks as $check) {
            if ($check) {
                $score++;
            }
        }

        $levels = [
            0 => ['level' => 'very-weak', 'text' => 'Very Weak', 'color' => 'red'],
            1 => ['level' => 'weak', 'text' => 'Weak', 'color' => 'red'],
            2 => ['level' => 'fair', 'text' => 'Fair', 'color' => 'orange'],
            3 => ['level' => 'good', 'text' => 'Good', 'color' => 'yellow'],
            4 => ['level' => 'strong', 'text' => 'Strong', 'color' => 'green'],
            5 => ['level' => 'very-strong', 'text' => 'Very Strong', 'color' => 'green'],
            6 => ['level' => 'excellent', 'text' => 'Excellent', 'color' => 'green'],
        ];

        return array_merge($levels[$score] ?? $levels[0], [
            'score'     => $score,
            'max_score' => 6,
            'checks'    => $checks,
        ]);
    }

    /**
     * Get password requirements
     */
    private function getPasswordRequirements(): array
    {
        return [
            'min_length'                  => 8,
            'requires_lowercase'          => TRUE,
            'requires_uppercase'          => TRUE,
            'requires_numbers'            => TRUE,
            'requires_special_characters' => TRUE,
            'description'                 => 'Password must be at least 8 characters long and contain uppercase, lowercase, numbers, and special characters.',
        ];
    }

    /**
     * Get validation rules for specific registration step
     */
    private function getValidationRulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'role' => ['required', 'in:customer,agent'],
            ],
            2 => [
                'first_name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
                'last_name'  => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
                'email'      => ['required', 'email', 'max:255', 'unique:users'],
                'username'   => ['nullable', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_\-\.]+$/', 'unique:users'],
                'phone'      => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[1-9][\d]{0,15}$/'],
            ],
            3 => [
                'password'              => ['required', 'confirmed', Password::defaults()],
                'password_confirmation' => ['required'],
            ],
            4 => [
                'legal_acceptances'   => ['required', 'array'],
                'legal_acceptances.*' => ['required', 'boolean', 'accepted'],
            ],
            default => [],
        };
    }
}
