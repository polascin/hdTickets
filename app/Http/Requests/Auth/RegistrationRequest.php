<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return TRUE;
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
            'phone'            => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'password'         => ['required', 'confirmed', Password::defaults()],
            'accept_terms'     => ['required', 'accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required'   => 'Please enter your first name.',
            'first_name.max'        => 'First name cannot exceed 100 characters.',
            'last_name.required'    => 'Please enter your last name.',
            'last_name.max'         => 'Last name cannot exceed 100 characters.',
            'email.required'        => 'Please enter your email address.',
            'email.email'           => 'Please enter a valid email address.',
            'email.unique'          => 'This email is already registered.',
            'phone.regex'           => 'Please enter a valid phone number.',
            'password.required'     => 'Please enter a password.',
            'password.confirmed'    => 'Password confirmation does not match.',
            'accept_terms.required' => 'You must accept the terms and conditions.',
            'accept_terms.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name'            => 'first name',
            'last_name'             => 'last name',
            'email'                 => 'email address',
            'phone'                 => 'phone number',
            'password'              => 'password',
            'password_confirmation' => 'password confirmation',
            'accept_terms'          => 'terms and conditions',
            'marketing_opt_in'      => 'marketing preferences',
        ];
    }
}
