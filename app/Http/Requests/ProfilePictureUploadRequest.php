<?php declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfilePictureUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return TRUE; // Authorization is handled by auth middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public function rules(): array
    {
        return [
            'profile_picture' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120', // 5MB in KB
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
            ],
            'crop_data' => [
                'nullable',
                'json',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'profile_picture.required'   => 'Please select an image file to upload.',
            'profile_picture.file'       => 'The upload must be a valid file.',
            'profile_picture.image'      => 'The uploaded file must be a valid image.',
            'profile_picture.mimes'      => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.',
            'profile_picture.max'        => 'The image file size cannot exceed 5MB.',
            'profile_picture.dimensions' => 'The image must be at least 100x100 pixels and no larger than 4000x4000 pixels.',
            'crop_data.json'             => 'Invalid crop data format provided.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'profile_picture' => 'profile picture',
            'crop_data'       => 'crop data',
        ];
    }
}
