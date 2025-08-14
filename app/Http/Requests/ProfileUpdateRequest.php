<?php declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    /**
     * Rules
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'surname'  => ['nullable', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone'    => ['nullable', 'string', 'max:20'],
            'bio'      => ['nullable', 'string', 'max:1000'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'size:2'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    /**
     * Messages
     */
    public function messages(): array
    {
        return [
            'name.required'       => 'Please enter your first name.',
            'name.max'            => 'The name cannot be longer than 255 characters.',
            'surname.max'         => 'The surname cannot be longer than 255 characters.',
            'username.unique'     => 'This username is already taken.',
            'username.alpha_dash' => 'The username can only contain letters, numbers, dashes, and underscores.',
            'username.max'        => 'The username cannot be longer than 255 characters.',
            'email.required'      => 'Please enter your email address.',
            'email.email'         => 'Please enter a valid email address.',
            'email.unique'        => 'This email address is already registered.',
            'phone.max'           => 'The phone number cannot be longer than 20 characters.',
            'bio.max'             => 'The bio cannot be longer than 1000 characters.',
            'timezone.max'        => 'The timezone cannot be longer than 50 characters.',
            'language.size'       => 'The language code must be exactly 2 characters.',
        ];
    }
}
