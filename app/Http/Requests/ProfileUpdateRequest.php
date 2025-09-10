<?php declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

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
            // Basic Information
            'name'     => ['required', 'string', 'max:255'],
            'surname'  => ['nullable', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'bio' => ['nullable', 'string', 'max:500'],

            // Contact Information
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[+]?[0-9\s\-\(\)]+$/'],

            // Preferences
            'timezone' => ['nullable', 'string', 'max:255', 'in:' . implode(',', timezone_identifiers_list())],
            'language' => ['nullable', 'string', 'size:2', 'in:en,es,fr,de,it,pt,nl,pl,cs,sk'],

            // Notification Preferences
            'email_notifications'             => ['nullable', 'boolean'],
            'push_notifications'              => ['nullable', 'boolean'],
            'preferences'                     => ['nullable', 'array'],
            'preferences.price_alerts'        => ['nullable', 'boolean'],
            'preferences.availability_alerts' => ['nullable', 'boolean'],
            'preferences.marketing_emails'    => ['nullable', 'boolean'],

            // Profile Picture (handled separately via AJAX)
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5MB max
        ];
    }

    /**
     * Get custom validation messages.
     */
    /**
     * Messages
     */
    #[Override]
    public function messages(): array
    {
        return [
            // Basic Information Messages
            'name.required'       => 'Please enter your first name.',
            'name.max'            => 'The first name cannot be longer than 255 characters.',
            'surname.max'         => 'The last name cannot be longer than 255 characters.',
            'username.unique'     => 'This username is already taken. Please choose a different one.',
            'username.alpha_dash' => 'The username can only contain letters, numbers, dashes, and underscores.',
            'username.max'        => 'The username cannot be longer than 255 characters.',
            'bio.max'             => 'The biography cannot be longer than 500 characters.',

            // Contact Information Messages
            'email.required'  => 'Please enter your email address.',
            'email.email'     => 'Please enter a valid email address.',
            'email.unique'    => 'This email address is already registered to another account.',
            'email.lowercase' => 'Email address must be in lowercase.',
            'phone.max'       => 'The phone number cannot be longer than 20 characters.',
            'phone.regex'     => 'Please enter a valid phone number format.',

            // Preferences Messages
            'timezone.max'  => 'Invalid timezone selection.',
            'timezone.in'   => 'Please select a valid timezone from the list.',
            'language.size' => 'The language code must be exactly 2 characters.',
            'language.in'   => 'Please select a supported language.',

            // Notification Preferences Messages
            'email_notifications.boolean' => 'Email notifications setting must be yes or no.',
            'push_notifications.boolean'  => 'Push notifications setting must be yes or no.',
            'preferences.array'           => 'Preferences must be a valid format.',

            // Profile Picture Messages
            'profile_picture.image' => 'The profile picture must be an image file.',
            'profile_picture.mimes' => 'The profile picture must be a JPEG, JPG, PNG, or WEBP file.',
            'profile_picture.max'   => 'The profile picture size cannot exceed 5MB.',
        ];
    }
}
