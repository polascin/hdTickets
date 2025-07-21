<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
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
            "file" => "required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip,rar", // Max 10MB
            "ticket_id" => "sometimes|exists:tickets,id",
            "comment_id" => "sometimes|exists:comments,id",
            "metadata" => "nullable|array",
        ];
    }

    /**
     * Get the validation error messages.
     */
    public function messages(): array
    {
        return [
            "file.required" => "Please select a file to upload.",
            "file.file" => "The uploaded file is not valid.",
            "file.max" => "The file size cannot exceed 10MB.",
            "file.mimes" => "The file must be a valid type: jpg, jpeg, png, pdf, doc, docx, txt, zip, rar.",
            "ticket_id.exists" => "The selected ticket does not exist.",
            "comment_id.exists" => "The selected comment does not exist.",
            "metadata.array" => "Metadata must be provided as an array.",
        ];
    }
}
