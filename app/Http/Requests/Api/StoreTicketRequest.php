<?php declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * Authorize
     */
    public function authorize(): bool
    {
        return TRUE; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    /**
     * Rules
     */
    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'priority'    => 'required|in:' . implode(',', Ticket::getPriorities()),
            'source'      => 'nullable|in:' . implode(',', Ticket::getSources()),
            'due_date'    => 'nullable|date|after:now',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'metadata'    => 'nullable|array',
        ];
    }

    /**
     * Get the validation error messages.
     */
    /**
     * Messages
     */
    public function messages(): array
    {
        return [
            'title.required'       => 'The ticket title is required.',
            'title.max'            => 'The ticket title cannot exceed 255 characters.',
            'description.required' => 'The ticket description is required.',
            'category_id.required' => 'Please select a category for the ticket.',
            'category_id.exists'   => 'The selected category does not exist.',
            'priority.required'    => 'Please select a priority level.',
            'priority.in'          => 'The selected priority is invalid.',
            'source.in'            => 'The selected source is invalid.',
            'due_date.date'        => 'Please provide a valid due date.',
            'due_date.after'       => 'The due date must be in the future.',
            'tags.array'           => 'Tags must be provided as an array.',
            'tags.*.string'        => 'Each tag must be a string.',
            'tags.*.max'           => 'Each tag cannot exceed 50 characters.',
            'assigned_to.exists'   => 'The selected assignee does not exist.',
            'metadata.array'       => 'Metadata must be provided as an array.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    /**
     * PrepareForValidation
     */
    protected function prepareForValidation(): void
    {
        // Set default source if not provided
        if (!$this->has('source')) {
            $this->merge([
                'source' => Ticket::SOURCE_API,
            ]);
        }

        // Set default priority if not provided
        if (!$this->has('priority')) {
            $this->merge([
                'priority' => Ticket::PRIORITY_MEDIUM,
            ]);
        }
    }
}
