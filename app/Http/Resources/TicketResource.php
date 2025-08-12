<?php declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'uuid'             => $this->uuid,
            'title'            => $this->title,
            'description'      => $this->description,
            'status'           => $this->status,
            'priority'         => $this->priority,
            'source'           => $this->source,
            'due_date'         => $this->due_date?->toISOString(),
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'tags'             => $this->tags ?? [],
            'metadata'         => $this->metadata ?? [],
            'created_at'       => $this->created_at->toISOString(),
            'updated_at'       => $this->updated_at->toISOString(),

            // Relationships
            'user'        => new UserResource($this->whenLoaded('user')),
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'category'    => new CategoryResource($this->whenLoaded('category')),

            // Computed attributes
            'is_open'          => $this->isOpen(),
            'is_closed'        => $this->isClosed(),
            'is_overdue'       => $this->isOverdue(),
            'is_high_priority' => $this->isHighPriority(),
            'status_color'     => $this->status_color,
            'priority_color'   => $this->priority_color,
            'formatted_title'  => $this->formatted_title,
        ];
    }
}
