<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PublicRegistrationRequest;
use App\Models\LegalDocument;
use App\Models\User;
use App\Models\UserLegalAcceptance;
use App\Services\PhoneVerificationService;
use App\Services\TwoFactorAuthService;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class PublicRegistrationController extends Controller
{
    public function __construct(
        private PhoneVerificationService $phoneService,
        private TwoFactorAuthService $twoFactorService,
    ) {
    }

    /**
     * Show the public registration form
     */
    public function create(): View
    {
        // Get all required legal documents
        $legalDocuments = LegalDocument::getActiveRequiredDocuments();

        // Check if all required documents exist
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        $missingDocuments = array_diff($requiredTypes, array_keys($legalDocuments));

        if (!empty($missingDocuments)) {
            abort(503, 'Registration is temporarily unavailable. Missing legal documents: ' . implode(', ', $missingDocuments));
        }

        return view('auth.public-register', compact('legalDocuments'));
    }

    /**
     * Handle public customer registration
     */
    public function store(PublicRegistrationRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Validate legal document acceptances
            $this->validateLegalAcceptances($request);

            // Create the user
            $user = User::create([
                'name'                => $request->validated()['name'],
                'surname'             => $request->validated()['surname'] ?? NULL,
                'email'               => $request->validated()['email'],
                'phone'               => $request->validated()['phone'] ?? NULL,
                'password'            => Hash::make($request->validated()['password']),
                'role'                => User::ROLE_CUSTOMER,
                'is_active'           => TRUE,
                'registration_source' => 'public_web',
                'password_changed_at' => now(),
                'require_2fa'         => $request->boolean('enable_2fa', FALSE),
            ]);

            // Record legal document acceptances
            $this->recordLegalAcceptances($user, $request);

            // Set up 2FA if requested
            if ($request->boolean('enable_2fa', FALSE)) {
                $this->setupTwoFactorAuth($user);
            }

            // Send phone verification if phone provided
            if ($request->filled('phone')) {
                $this->phoneService->sendVerificationCode($user);
            }

            DB::commit();

            // Fire registered event (triggers email verification)
            event(new Registered($user));

            // Log the user in
            Auth::login($user);

            // Redirect to email verification notice
            return redirect()->route('verification.notice')
                ->with('success', 'Registration successful! Please check your email to verify your account.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Registration failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show phone verification form
     */
    public function showPhoneVerification(): View
    {
        $user = Auth::user();

        if (!$user || !$user->phone) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-phone', compact('user'));
    }

    /**
     * Verify phone number
     */
    public function verifyPhone(Request $request): RedirectResponse
    {
        $request->validate([
            'verification_code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (!$user || !$user->phone) {
            return redirect()->route('dashboard');
        }

        if ($this->phoneService->verifyCode($user, $request->verification_code)) {
            $user->update(['phone_verified_at' => now()]);

            return redirect()->route('dashboard')
                ->with('success', 'Phone number verified successfully!');
        }

        return back()
            ->withErrors(['verification_code' => 'Invalid verification code.']);
    }

    /**
     * Resend phone verification code
     */
    public function resendPhoneVerification(): RedirectResponse
    {
        $user = Auth::user();

        if (!$user || !$user->phone) {
            return redirect()->route('dashboard');
        }

        try {
            $this->phoneService->sendVerificationCode($user);

            return back()
                ->with('success', 'Verification code sent!');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to send verification code.']);
        }
    }

    /**
     * Show 2FA setup page
     */
    public function showTwoFactorSetup(): View
    {
        $user = Auth::user();

        if (!$user || !$user->two_factor_secret || $user->two_factor_enabled) {
            return redirect()->route('dashboard');
        }

        $qrCodeUrl = $this->twoFactorService->generateQrCodeUrl($user);

        return view('auth.setup-2fa', compact('user', 'qrCodeUrl'));
    }

    /**
     * Confirm and enable 2FA
     */
    public function confirmTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (!$user || !$user->two_factor_secret || $user->two_factor_enabled) {
            return redirect()->route('dashboard');
        }

        if ($this->twoFactorService->verifyCode($user, $request->code)) {
            $user->update([
                'two_factor_enabled'      => TRUE,
                'two_factor_confirmed_at' => now(),
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Two-factor authentication enabled successfully!');
        }

        return back()
            ->withErrors(['code' => 'Invalid authentication code.']);
    }

    /**
     * Validate that all required legal documents have been accepted
     */
    private function validateLegalAcceptances(PublicRegistrationRequest $request): void
    {
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        $legalDocuments = LegalDocument::getActiveRequiredDocuments();

        foreach ($requiredTypes as $type) {
            if (!$request->boolean("accept_{$type}")) {
                throw new Exception("You must accept the {$legalDocuments[$type]->type_name} to register.");
            }
        }
    }

    /**
     * Record user's legal document acceptances
     */
    private function recordLegalAcceptances(User $user, PublicRegistrationRequest $request): void
    {
        $legalDocuments = LegalDocument::getActiveRequiredDocuments();

        foreach ($legalDocuments as $document) {
            if ($request->boolean("accept_{$document->type}")) {
                UserLegalAcceptance::recordAcceptance(
                    $user->id,
                    $document->id,
                    $document->version,
                    UserLegalAcceptance::METHOD_REGISTRATION,
                );
            }
        }
    }

    /**
     * Set up two-factor authentication for the user
     */
    private function setupTwoFactorAuth(User $user): void
    {
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();

        $user->update([
            'two_factor_secret'  => $secretKey,
            'two_factor_enabled' => FALSE, // Will be enabled after confirmation
        ]);
    }
}
