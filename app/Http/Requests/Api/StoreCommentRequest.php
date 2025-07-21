<?php

namespace App\Http\Requests\Api;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "content" => "required|string",
            "type" => "sometimes|in:" . implode(",", Comment::getTypes()),
            "is_internal" => "sometimes|boolean",
            "is_solution" => "sometimes|boolean",
            "metadata" => "nullable|array",
        ];
    }

    /**
     * Get the validation error messages.
     */
    public function messages(): array
    {
        return [
            "content.required" => "The comment content is required.",
            "content.string" => "The comment content must be a string.",
            "type.in" => "The selected comment type is invalid.",
            "is_internal.boolean" => "The is_internal field must be true or false.",
            "is_solution.boolean" => "The is_solution field must be true or false.",
            "metadata.array" => "Metadata must be provided as an array.",
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default type if not provided
        if (!$this->has("type")) {
            $this->merge([
                "type" => Comment::TYPE_COMMENT,
            ]);
        }

        // Set default internal status if not provided
        if (!$this->has("is_internal")) {
            $this->merge([
                "is_internal" => false,
            ]);
        }

        // Set default solution status if not provided
        if (!$this->has("is_solution")) {
            $this->merge([
                "is_solution" => false,
            ]);
        }
    }
}
