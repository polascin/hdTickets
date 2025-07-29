<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:' . implode(',', Ticket::getStatuses()),
            'priority' => 'required|in:' . implode(',', Ticket::getPriorities()),
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The ticket title is required.',
            'title.max' => 'The ticket title cannot exceed 255 characters.',
            'category_id.required' => 'Please select a category for this ticket.',
            'category_id.exists' => 'The selected category is invalid.',
            'status.required' => 'Please select a status for this ticket.',
            'status.in' => 'The selected status is invalid.',
            'priority.required' => 'Please select a priority for this ticket.',
            'priority.in' => 'The selected priority is invalid.',
            'assigned_to.exists' => 'The selected assignee is invalid.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'assigned_to' => 'assignee',
            'due_date' => 'due date',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up tags if present
        if ($this->has('tags')) {
            $tags = $this->input('tags');
            if (is_string($tags)) {
                // If tags come as a comma-separated string, convert to array
                $tags = array_map('trim', explode(',', $tags));
            }
            // Remove empty tags and duplicates
            $tags = array_unique(array_filter($tags, fn($tag) => !empty(trim($tag))));
            $this->merge(['tags' => array_values($tags)]);
        }
    }
}
