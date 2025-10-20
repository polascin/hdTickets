<?php declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password;
use Override;

class PublicRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return TRUE; // Public registration is allowed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string|ValidationRule>
     */
    public function rules(): array
    {
        return [
            'first_name'       => ['required', 'string', 'max:100'],
            'last_name'        => ['required', 'string', 'max:100'],
            'email'            => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone'            => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'], // E.164 format
            'password'         => ['required', 'confirmed', Password::defaults()],
            'accept_terms'     => ['required', 'accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'enable_2fa'       => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    #[Override]
    public function messages(): array
    {
        return [
            'first_name.required'   => 'Please enter your first name.',
            'last_name.required'    => 'Please enter your last name.',
            'email.required'        => 'Please enter your email address.',
            'email.unique'          => 'This email address is already registered.',
            'phone.regex'           => 'Please enter a valid phone number with country code.',
            'password.required'     => 'Please enter a password.',
            'password.confirmed'    => 'Please confirm your password.',
            'accept_terms.required' => 'You must accept the terms and conditions.',
            'accept_terms.accepted' => 'You must accept the terms and conditions to register.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    #[Override]
    public function attributes(): array
    {
        return [
            'first_name'       => 'first name',
            'last_name'        => 'last name',
            'email'            => 'email address',
            'phone'            => 'phone number',
            'password'         => 'password',
            'accept_terms'     => 'terms and conditions',
            'marketing_opt_in' => 'marketing preferences',
            'enable_2fa'       => 'two-factor authentication',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean phone number
        if ($this->filled('phone')) {
            $phone = preg_replace('/[^\d+]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }

        // Normalize email
        if ($this->filled('email')) {
            $this->merge(['email' => strtolower(trim($this->email))]);
        }
    }
}
