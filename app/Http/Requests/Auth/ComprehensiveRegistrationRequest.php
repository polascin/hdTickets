<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class ComprehensiveRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public registration is allowed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Personal Information
            'first_name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\'\.]+$/',
            ],
            'last_name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\'\.]+$/',
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:filter,spoof',
                'max:255',
                'unique:users,email',
            ],
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_\-\.]+$/',
                'unique:users,username',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[1-9][\d]{0,15}$/',
            ],

            // Account Security
            'password' => [
                'required',
                'confirmed',
                Password::defaults()
                    ->min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => ['required'],

            // Account Type & Preferences
            'role' => [
                'required',
                Rule::in([User::ROLE_CUSTOMER, User::ROLE_AGENT]),
            ],
            'timezone' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(timezone_identifiers_list()),
            ],
            'language' => [
                'nullable',
                'string',
                'max:5',
                Rule::in(['en', 'es', 'fr', 'de', 'it']),
            ],

            // Legal Acceptances
            'legal_acceptances' => ['required', 'array'],
            'legal_acceptances.*' => ['required', 'boolean', 'accepted'],

            // Marketing Preferences
            'marketing_emails' => ['boolean'],
            'newsletter_subscription' => ['boolean'],

            // Security Options
            'enable_2fa' => ['boolean'],

            // Optional Fields
            'referral_code' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\-_]+$/',
            ],

            // reCAPTCHA (if enabled)
            'g-recaptcha-response' => [
                config('services.recaptcha.enabled') ? 'required' : 'nullable',
                'string',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Personal Information Messages
            'first_name.required' => 'Please enter your first name.',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.required' => 'Please enter your last name.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered. Please use a different email or sign in.',
            'username.min' => 'Username must be at least 3 characters long.',
            'username.regex' => 'Username can only contain letters, numbers, underscores, hyphens, and periods.',
            'username.unique' => 'This username is already taken. Please choose a different one.',
            'phone.regex' => 'Please enter a valid phone number.',

            // Password Messages
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password_confirmation.required' => 'Please confirm your password.',

            // Account Type Messages
            'role.required' => 'Please select an account type.',
            'role.in' => 'Please select a valid account type.',

            // Legal Acceptance Messages
            'legal_acceptances.required' => 'You must accept the terms and conditions to continue.',
            'legal_acceptances.*.accepted' => 'You must accept all required legal documents.',

            // reCAPTCHA Messages
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ];
    }

    /**
     * Get custom attribute names for error display.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'username' => 'username',
            'phone' => 'phone number',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'role' => 'account type',
            'legal_acceptances.*' => 'legal agreement',
            'g-recaptcha-response' => 'reCAPTCHA',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up phone number
        if ($this->phone) {
            $this->merge([
                'phone' => preg_replace('/[^\+\d]/', '', $this->phone),
            ]);
        }

        // Ensure email is lowercase
        if ($this->email) {
            $this->merge([
                'email' => strtolower($this->email),
            ]);
        }

        // Clean up username
        if ($this->username) {
            $this->merge([
                'username' => strtolower(trim($this->username)),
            ]);
        }

        // Ensure boolean fields are properly set
        $this->merge([
            'marketing_emails' => $this->boolean('marketing_emails'),
            'newsletter_subscription' => $this->boolean('newsletter_subscription'),
            'enable_2fa' => $this->boolean('enable_2fa'),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic can go here
            
            // Validate reCAPTCHA if enabled
            if (config('services.recaptcha.enabled')) {
                $this->validateRecaptcha($validator);
            }

            // Custom business logic validation
            $this->validateBusinessRules($validator);
        });
    }

    /**
     * Validate reCAPTCHA response
     */
    private function validateRecaptcha($validator): void
    {
        $recaptchaResponse = $this->input('g-recaptcha-response');
        
        if (!$recaptchaResponse) {
            return; // Required validation will handle this
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $recaptchaResponse,
            'remoteip' => $this->ip(),
        ]);

        $result = $response->json();
        
        if (!$result['success'] || ($result['score'] ?? 0) < config('services.recaptcha.minimum_score', 0.5)) {
            $validator->errors()->add('g-recaptcha-response', 'reCAPTCHA verification failed. Please try again.');
        }
    }

    /**
     * Apply custom business rules validation
     */
    private function validateBusinessRules($validator): void
    {
        // Example: Check if registration is currently allowed
        if (!config('app.registration_enabled', true)) {
            $validator->errors()->add('general', 'Registration is currently disabled. Please try again later.');
        }

        // Example: Validate role-specific requirements
        if ($this->role === User::ROLE_AGENT) {
            // Agents might require additional verification
            if (!$this->phone) {
                $validator->errors()->add('phone', 'Phone number is required for business accounts.');
            }
        }

        // Example: Validate email domain restrictions (if any)
        $restrictedDomains = config('auth.restricted_email_domains', []);
        if (!empty($restrictedDomains) && $this->email) {
            $domain = substr(strrchr($this->email, '@'), 1);
            if (in_array($domain, $restrictedDomains)) {
                $validator->errors()->add('email', 'Registration with this email domain is not allowed.');
            }
        }
    }

    /**
     * Get the validated data with additional processing
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        
        // Set defaults
        $validated['timezone'] = $validated['timezone'] ?? config('app.timezone');
        $validated['language'] = $validated['language'] ?? config('app.locale');
        $validated['marketing_emails'] = $validated['marketing_emails'] ?? false;
        $validated['newsletter_subscription'] = $validated['newsletter_subscription'] ?? false;
        $validated['enable_2fa'] = $validated['enable_2fa'] ?? false;

        // Generate username if not provided
        if (empty($validated['username'])) {
            $validated['username'] = $this->generateUsername($validated['first_name'], $validated['last_name']);
        }

        return $validated;
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
}