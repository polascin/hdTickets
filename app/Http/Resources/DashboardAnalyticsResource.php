<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property-read array $totals
 */
class DashboardAnalyticsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        /** @var array{generated_at?:string,totals?:array,trends?:array,platforms?:array} $data */
        $data = $this->resource;

        return [
          'generated_at' => $data['generated_at'] ?? NULL,
          'totals'       => $data['totals'] ?? [],
          'trends'       => $data['trends'] ?? [
            'demand' => [
              'high_demand'       => 0,
              'demand_percentage' => 0,
            ],
            'pricing' => [],
          ],
          'platforms' => $data['platforms'] ?? [],
        ];
    }
}
