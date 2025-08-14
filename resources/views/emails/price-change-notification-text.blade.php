PRICE CHANGE ALERT

Event: {{ $ticket['event_name'] }}
Platform: {{ $platform }}
Old Price: ${{ number_format($oldPrice, 2) }}
New Price: ${{ number_format($newPrice, 2) }}
Change: @if($isIncrease)Increased by @else Decreased by @endif ${{ number_format(abs($priceChange), 2) }} ({{ $changePercentage }}%)

@if($isSignificant)
WARNING: This is a significant {{ $isIncrease ? 'increase' : 'decrease' }} exceeding 10%!
@endif

@if($ticket['url'])
View Ticket Details: {{ $ticket['url'] }}
@endif

Thanks,
{{ config('app.name') }}
