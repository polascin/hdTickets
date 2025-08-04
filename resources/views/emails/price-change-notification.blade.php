@component('mail::message')
# Price Change Alert

**Event:** {{ $ticket['event_name'] }}  
**Platform:** {{ $platform }}  
**Old Price:** ${{ number_format($oldPrice, 2) }}  
**New Price:** ${{ number_format($newPrice, 2) }}  
**Change:** @if($isIncrease) Increased by @else Decreased by @endif ${{ number_format(abs($priceChange), 2) }} ({{ $changePercentage }}%)

@if($isSignificant)
@component('mail::panel')
⚠️ This is a significant {{ $isIncrease ? 'increase' : 'decrease' }} exceeding 10%!
@endcomponent
@endif

@component('mail::button', ['url' => $ticket['url'] ?? '#'])
View Ticket Details
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent

