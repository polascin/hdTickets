@component('mail::message')
# Ticket Availability Update

{{ $statusMessage }}

**Event:** {{ $ticket['event_name'] }}  
**Platform:** {{ $platform }}  
**Status:** {{ ucfirst(str_replace('_', ' ', $newStatus)) }}  
@if($quantity)
**Available Quantity:** {{ $quantity }}  
@endif

@if($urgency === 'high')
@component('mail::panel')
🎉 Great news! Tickets just became available. Act fast to secure your spot!
@endcomponent
@elseif($urgency === 'medium')
@component('mail::panel')
⏰ Limited availability! Only a few tickets remaining.
@endcomponent
@elseif($isSoldOut)
@component('mail::panel')
😔 Unfortunately, all tickets have been sold out.
@endcomponent
@endif

@if($isNowAvailable)
@component('mail::button', ['url' => $ticket['url'] ?? '#'])
Get Tickets Now!
@endcomponent
@else
@component('mail::button', ['url' => $ticket['url'] ?? '#'])
View Event Details
@endcomponent
@endif

@if($ticket['date'])
**Event Date:** {{ $ticket['date'] }}  
@endif
@if($ticket['venue'])
**Venue:** {{ $ticket['venue'] }}  
@endif

Thanks,  
{{ config('app.name') }}
@endcomponent
