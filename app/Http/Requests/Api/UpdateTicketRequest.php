<?php declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateTicketRequest extends FormRequest
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
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'priority'    => 'sometimes|in:' . implode(',', Ticket::getPriorities()),
            'status'      => 'sometimes|in:' . implode(',', Ticket::getStatuses()),
            'due_date'    => 'nullable|date|after:now',
            'tags'        => 'sometimes|array',
            'tags.*'      => 'string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'metadata'    => 'sometimes|array',
        ];
    }

    /**
     * Get the validation error messages.
     */
    /**
     * Messages
     */
    #[Override]
    public function messages(): array
    {
        return [
            'title.string'       => 'The ticket title must be a string.',
            'title.max'          => 'The ticket title cannot exceed 255 characters.',
            'description.string' => 'The ticket description must be a string.',
            'category_id.exists' => 'The selected category does not exist.',
            'priority.in'        => 'The selected priority is invalid.',
            'status.in'          => 'The selected status is invalid.',
            'due_date.date'      => 'Please provide a valid due date.',
            'due_date.after'     => 'The due date must be in the future.',
            'tags.array'         => 'Tags must be provided as an array.',
            'tags.*.string'      => 'Each tag must be a string.',
            'tags.*.max'         => 'Each tag cannot exceed 50 characters.',
            'assigned_to.exists' => 'The selected assignee does not exist.',
            'metadata.array'     => 'Metadata must be provided as an array.',
        ];
    }
}
