<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @property-read array $statistics */
class DashboardRealtimeResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  #[Override]
  public function toArray(Request $request): array
  {
    /** @var array{statistics?:array,recent_tickets?:array,user_metrics?:array,system_status?:array,notifications?:array,last_updated?:string} $data */
    $data = $this->resource;

    return [
      'statistics'     => $data['statistics']     ?? [],
      'recent_tickets' => $data['recent_tickets'] ?? [],
      'user_metrics'   => $data['user_metrics']   ?? [],
      'system_status'  => $data['system_status']  ?? [],
      'notifications'  => $data['notifications']  ?? [],
      'last_updated'   => $data['last_updated']   ?? null,
    ];
  }
}
