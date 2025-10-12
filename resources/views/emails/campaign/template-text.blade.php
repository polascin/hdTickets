HD TICKETS - {{ $content['subject'] }}
==============================================

Hello {{ $user->name }}!

{{ $content['body'] }}

@if (isset($content['features']) && is_array($content['features']))
  What's Included:
  @foreach ($content['features'] as $feature)
    ✓ {{ $feature }}
  @endforeach
@endif

@if (isset($content['action_url']) && isset($content['action_text']))
  {{ $content['action_text'] }}: {{ $content['action_url'] }}
@endif

--
HD Tickets - Professional Sports Ticket Monitoring
Monitor ticket prices, get instant alerts, automate purchases

Support: support@hdtickets.com
Website: {{ url('/') }}

You're receiving this email because you're subscribed to HD Tickets updates.
Unsubscribe: {{ $unsubscribeUrl }}
