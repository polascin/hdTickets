<?php declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    /**
     * ToArray
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'color'       => $this->color ?? '#3B82F6',
            'is_active'   => $this->is_active ?? TRUE,
            'created_at'  => $this->created_at->toISOString(),
            'updated_at'  => $this->updated_at->toISOString(),

            // Counts (when available)
            'tickets_count' => $this->whenCounted('tickets'),
        ];
    }
}
