<?php declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\LegalDocument;
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
        $rules = [
            'name'       => ['required', 'string', 'max:255'],
            'surname'    => ['nullable', 'string', 'max:255'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone'      => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'], // E.164 format
            'password'   => ['required', 'confirmed', Password::defaults()],
            'enable_2fa' => ['nullable', 'boolean'],
        ];

        // Add validation rules for legal document acceptances
        $requiredDocuments = LegalDocument::getRequiredForRegistration();
        foreach ($requiredDocuments as $type) {
            $rules["accept_{$type}"] = ['required', 'accepted'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    #[Override]
    public function messages(): array
    {
        $messages = [
            'name.required'      => 'Please enter your first name.',
            'email.required'     => 'Please enter your email address.',
            'email.unique'       => 'This email address is already registered.',
            'phone.regex'        => 'Please enter a valid phone number with country code.',
            'password.required'  => 'Please enter a password.',
            'password.confirmed' => 'Please confirm your password.',
        ];

        // Add custom messages for legal document acceptances
        $documents = LegalDocument::getActiveRequiredDocuments();
        foreach ($documents as $type => $document) {
            $messages["accept_{$type}.required"] = "You must accept the {$document->type_name}.";
            $messages["accept_{$type}.accepted"] = "You must accept the {$document->type_name} to register.";
        }

        return $messages;
    }

    /**
     * Get custom attributes for validator errors.
     */
    #[Override]
    public function attributes(): array
    {
        $attributes = [
            'name'       => 'first name',
            'surname'    => 'last name',
            'email'      => 'email address',
            'phone'      => 'phone number',
            'password'   => 'password',
            'enable_2fa' => 'two-factor authentication',
        ];

        // Add attributes for legal document acceptances
        $documents = LegalDocument::getActiveRequiredDocuments();
        foreach ($documents as $type => $document) {
            $attributes["accept_{$type}"] = $document->type_name;
        }

        return $attributes;
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
