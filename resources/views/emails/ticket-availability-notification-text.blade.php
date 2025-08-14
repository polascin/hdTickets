TICKET AVAILABILITY UPDATE

{{ $statusMessage }}

Event: {{ $ticket['event_name'] }}
Platform: {{ $platform }}
Status: {{ ucfirst(str_replace('_', ' ', $newStatus)) }}
@if($quantity)
Available Quantity: {{ $quantity }}
@endif

@if($urgency === 'high')
URGENT: Great news! Tickets just became available. Act fast to secure your spot!
@elseif($urgency === 'medium')
LIMITED: Only a few tickets remaining.
@elseif($isSoldOut)
SOLD OUT: Unfortunately, all tickets have been sold out.
@endif

@if($ticket['url'])
@if($isNowAvailable)
Get Tickets Now: {{ $ticket['url'] }}
@else
View Event Details: {{ $ticket['url'] }}
@endif
@endif

@if($ticket['date'])
Event Date: {{ $ticket['date'] }}
@endif
@if($ticket['venue'])
Venue: {{ $ticket['venue'] }}
@endif

Thanks,
{{ config('app.name') }}
