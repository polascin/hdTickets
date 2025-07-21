<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "content" => $this->content,
            "type" => $this->type,
            "is_internal" => $this->is_internal,
            "is_solution" => $this->is_solution,
            "metadata" => $this->metadata ?? [],
            "edited_at" => $this->edited_at?->toISOString(),
            "created_at" => $this->created_at->toISOString(),
            "updated_at" => $this->updated_at->toISOString(),
            
            // Relationships
            "user" => new UserResource($this->whenLoaded("user")),
            "editor" => new UserResource($this->whenLoaded("editor")),
            "ticket" => new TicketResource($this->whenLoaded("ticket")),
            "attachments" => AttachmentResource::collection($this->whenLoaded("attachments")),
            
            // Computed attributes
            "is_by_staff" => $this->isByStaff(),
            "is_by_customer" => $this->isByCustomer(),
            "is_system" => $this->isSystem(),
            "is_edited" => $this->isEdited(),
            "type_color" => $this->type_color,
            "user_display_name" => $this->user_display_name,
            "formatted_created_at" => $this->formatted_created_at,
            "excerpt" => $this->excerpt,
            
            // Counts (when available)
            "attachments_count" => $this->whenCounted("attachments"),
        ];
    }
}
