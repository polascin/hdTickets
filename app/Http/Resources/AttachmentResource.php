<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "filename" => $this->filename,
            "filepath" => $this->filepath,
            "filetype" => $this->filetype,
            "filesize" => $this->filesize,
            "metadata" => $this->metadata ?? [],
            "created_at" => $this->created_at->toISOString(),
            "updated_at" => $this->updated_at->toISOString(),
            
            // Relationships
            "user" => new UserResource($this->whenLoaded("user")),
            "ticket" => new TicketResource($this->whenLoaded("ticket")),
            "comment" => new CommentResource($this->whenLoaded("comment")),
            
            // Computed attributes
            "filesize_human" => $this->getFilesizeHumanAttribute(),
            "download_url" => route("api.attachments.download", $this->uuid),
        ];
    }
    
    /**
     * Get human readable filesize
     */
    protected function getFilesizeHumanAttribute(): string
    {
        $size = $this->filesize;
        $units = ["B", "KB", "MB", "GB"];
        
        for ($i = 0; $size >= 1024 && $i < 3; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . " " . $units[$i];
    }
}
