<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification['title'] }}</title>
    <link href="{{ asset('css/email.css') }}?t={{ $cssTimestamp }}" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .notification-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .type-price-drop { background: #fef3c7; color: #92400e; }
        .type-ticket-available { background: #dcfce7; color: #166534; }
        .type-system-status { background: #e0e7ff; color: #3730a3; }
        .type-custom-alert { background: #fce7f3; color: #9d174d; }
        .message {
            font-size: 16px;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .ticket-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }
        .detail-value {
            color: #1f2937;
        }
        .price-highlight {
            font-size: 20px;
            font-weight: 700;
            color: #059669;
        }
        .savings-badge {
            background: #dcfce7;
            color: #166534;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        .actions {
            margin: 30px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 0 5px;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .platform-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #e5e7eb;
            color: #374151;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .priority-high {
            border-left: 4px solid #ef4444;
        }
        .priority-critical {
            border-left: 4px solid #dc2626;
            background: #fef2f2;
        }
    </style>
</head>
<body>
    <div class="email-container {{ $notification['priority'] >= 4 ? 'priority-high' : '' }} {{ $notification['priority'] >= 5 ? 'priority-critical' : '' }}">
        <div class="header">
            <h1>{{ $notification['title'] }}</h1>
        </div>

        <div class="content">
            <span class="notification-type type-{{ str_replace('_', '-', $notification['type']) }}">
                {{ ucwords(str_replace('_', ' ', $notification['type'])) }}
            </span>

            <div class="message">
                {{ $notification['message'] }}
            </div>

            @if($notification['type'] === 'price_drop')
                <div class="ticket-details">
                    <div class="detail-row">
                        <span class="detail-label">Event:</span>
                        <span class="detail-value">{{ $notification['data']['event_name'] }}</span>
                    </div>
                    @if(!empty($notification['data']['venue']))
                    <div class="detail-row">
                        <span class="detail-label">Venue:</span>
                        <span class="detail-value">{{ $notification['data']['venue'] }}</span>
                    </div>
                    @endif
                    @if(!empty($notification['data']['event_date']))
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($notification['data']['event_date'])->format('M j, Y g:i A') }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Old Price:</span>
                        <span class="detail-value">${{ number_format($notification['data']['old_price'], 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">New Price:</span>
                        <span class="detail-value price-highlight">${{ number_format($notification['data']['new_price'], 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">You Save:</span>
                        <span class="detail-value">
                            <span class="savings-badge">${{ number_format($notification['data']['savings'], 2) }}</span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Platform:</span>
                        <span class="detail-value">
                            <span class="platform-badge">{{ ucfirst($notification['data']['platform']) }}</span>
                        </span>
                    </div>
                </div>
            @endif

            @if($notification['type'] === 'ticket_available')
                <div class="ticket-details">
                    <div class="detail-row">
                        <span class="detail-label">Event:</span>
                        <span class="detail-value">{{ $notification['data']['event_name'] }}</span>
                    </div>
                    @if(!empty($notification['data']['venue']))
                    <div class="detail-row">
                        <span class="detail-label">Venue:</span>
                        <span class="detail-value">{{ $notification['data']['venue'] }}</span>
                    </div>
                    @endif
                    @if(!empty($notification['data']['event_date']))
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($notification['data']['event_date'])->format('M j, Y g:i A') }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Price:</span>
                        <span class="detail-value price-highlight">{{ $notification['data']['currency'] }}{{ number_format($notification['data']['price'], 2) }}</span>
                    </div>
                    @if(!empty($notification['data']['quantity']))
                    <div class="detail-row">
                        <span class="detail-label">Available:</span>
                        <span class="detail-value">{{ $notification['data']['quantity'] }} tickets</span>
                    </div>
                    @endif
                    @if(!empty($notification['data']['section']))
                    <div class="detail-row">
                        <span class="detail-label">Section:</span>
                        <span class="detail-value">{{ $notification['data']['section'] }}{{ !empty($notification['data']['row']) ? ', Row ' . $notification['data']['row'] : '' }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Platform:</span>
                        <span class="detail-value">
                            <span class="platform-badge">{{ ucfirst($notification['data']['platform']) }}</span>
                        </span>
                    </div>
                    @if($notification['data']['is_high_demand'] ?? false)
                    <div class="detail-row">
                        <span class="detail-label">Demand:</span>
                        <span class="detail-value">
                            <span class="savings-badge" style="background: #fecaca; color: #dc2626;">🔥 High Demand</span>
                        </span>
                    </div>
                    @endif
                </div>
            @endif

            @if($notification['type'] === 'system_status')
                <div class="ticket-details">
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">{{ ucfirst($notification['data']['status']) }}</span>
                    </div>
                    @if(!empty($notification['data']['affected_platforms']))
                    <div class="detail-row">
                        <span class="detail-label">Affected Platforms:</span>
                        <span class="detail-value">{{ implode(', ', array_map('ucfirst', $notification['data']['affected_platforms'])) }}</span>
                    </div>
                    @endif
                    @if(!empty($notification['data']['estimated_resolution']))
                    <div class="detail-row">
                        <span class="detail-label">Estimated Resolution:</span>
                        <span class="detail-value">{{ $notification['data']['estimated_resolution'] }}</span>
                    </div>
                    @endif
                </div>
            @endif

            <div class="actions">
                @if(in_array($notification['type'], ['price_drop', 'ticket_available']))
                    @if(!empty($notification['data']['ticket_url']))
                    <a href="{{ $notification['data']['ticket_url'] }}" class="btn btn-primary" target="_blank">
                        View Tickets on {{ ucfirst($notification['data']['platform']) }}
                    </a>
                    @endif
                    @if(!empty($notification['data']['ticket_id']))
                    <a href="{{ route('tickets.scraping.show', $notification['data']['ticket_id']) }}" class="btn btn-secondary">
                        View Details
                    </a>
                    @endif
                @endif

                @if($notification['type'] === 'system_status')
                    <a href="{{ route('system.status') }}" class="btn btn-primary">
                        View System Status
                    </a>
                @endif

                @if($notification['type'] === 'custom_alert')
                    @if(!empty($notification['data']['rule_id']))
                    <a href="{{ route('tickets.alerts.show', $notification['data']['rule_id']) }}" class="btn btn-primary">
                        View Alert Details
                    </a>
                    @endif
                @endif
            </div>
        </div>

        <div class="footer">
            <p>
                <strong>HDTickets</strong> - Sports Event Ticket Monitoring System<br>
                This notification was sent to {{ $user->email }} at {{ now()->format('M j, Y g:i A') }}
            </p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 15px;">
                You received this email because you have active ticket alerts. 
                <a href="{{ route('tickets.alerts.index') }}" style="color: #3b82f6;">Manage your alerts</a> |
                <a href="{{ route('profile.edit') }}" style="color: #3b82f6;">Update preferences</a>
            </p>
        </div>
    </div>
</body>
</html>
