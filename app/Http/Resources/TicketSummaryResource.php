<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class TicketSummaryResource extends JsonResource
{
  /**
   * @return array<string, mixed>
   */
  #[Override]
  public function toArray(Request $request): array
  {
    $t = $this->resource; // Expect ScrapedTicket model

    return [
      'id'               => $t->id,
      'title'            => $t->title ?? 'Sports Event',
      'venue'            => $t->venue ?? 'TBD',
      'sport'            => $t->sport ?? 'Sports',
      'platform'         => $t->platform ?? 'Unknown',
      'min_price'        => $t->min_price ? number_format($t->min_price, 2) : null,
      'max_price'        => $t->max_price ? number_format($t->max_price, 2) : null,
      'event_date'       => $t->event_date ? $t->event_date->format('M j, Y') : null,
      'event_time'       => $t->event_time ?? null,
      'scraped_at'       => $t->scraped_at?->diffForHumans(),
      'is_available'     => (bool) $t->is_available,
      'is_high_demand'   => (bool) ($t->is_high_demand ?? false),
      'popularity_score' => $t->popularity_score ?? 0,
      // Derived placeholders (controller helper analogues could migrate here later)
      'price_trend'      => 'stable',
      'demand_level'     => $this->getDemandLevel($t->popularity_score ?? 0),
    ];
  }

  private function getDemandLevel(int $popularity): string
  {
    if ($popularity >= 80) return 'high';
    if ($popularity >= 50) return 'medium';
    return 'low';
  }
}
