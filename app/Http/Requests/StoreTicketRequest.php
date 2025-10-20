<?php declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Override;

use function in_array;
use function is_string;

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
        return TRUE;
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
            'description' => 'nullable|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'priority'    => 'nullable|in:' . implode(',', Ticket::getPriorities()),
            'due_date'    => 'nullable|date|after:today',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    /**
     * Messages
     */
    #[Override]
    public function messages(): array
    {
        return [
            'title.required'       => 'The ticket title is required.',
            'title.max'            => 'The ticket title cannot exceed 255 characters.',
            'category_id.required' => 'Please select a category for this ticket.',
            'category_id.exists'   => 'The selected category is invalid.',
            'description.max'      => 'The description cannot exceed 5,000 characters.',
            'priority.in'          => 'The selected priority is invalid.',
            'due_date.after'       => 'The due date must be a future date.',
            'tags.*.max'           => 'Each tag cannot exceed 50 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    /**
     * Attributes
     */
    #[Override]
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'due_date'    => 'due date',
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
        // Clean up tags if present
        if ($this->has('tags')) {
            $tags = $this->input('tags');
            if (is_string($tags)) {
                // If tags come as a comma-separated string, convert to array
                $tags = array_map('trim', explode(',', $tags));
            }
            // Remove empty tags and duplicates
            $tags = array_unique(array_filter($tags, fn ($tag): bool => ! in_array(trim((string) $tag), ['', '0'], TRUE)));
            $this->merge(['tags' => array_values($tags)]);
        }
    }
}
