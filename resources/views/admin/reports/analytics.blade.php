<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Report - {{ strtoupper($period) }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: bold; color: #111827; }
        .subtitle { font-size: 12px; color: #6b7280; }
        .kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin: 12px 0; }
        .kpi { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .kpi-title { font-size: 12px; color: #6b7280; }
        .kpi-value { font-size: 18px; font-weight: bold; color: #111827; }
        .kpi-change { font-size: 12px; }
        .kpi-up { color: #059669; }
        .kpi-down { color: #dc2626; }
        .section { margin-top: 18px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; font-size: 12px; }
        th { background: #f9fafb; text-align: left; }
        .footer { margin-top: 24px; font-size: 10px; color: #6b7280; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">HD Tickets - Analytics Report</div>
            <div class="subtitle">Period: {{ strtoupper($period) }}</div>
        </div>
        <div class="subtitle">Generated: {{ $generated_at->format('Y-m-d H:i') }}</div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi-title">Total Revenue</div>
            <div class="kpi-value">${{ number_format($analytics['metrics']['revenue']['total'] ?? 0, 2) }}</div>
            @php $chg = $analytics['metrics']['revenue']['change'] ?? 0; @endphp
            <div class="kpi-change {{ ($chg ?? 0) >= 0 ? 'kpi-up' : 'kpi-down' }}">{{ number_format(abs($chg), 2) }}% vs previous</div>
        </div>
        <div class="kpi">
            <div class="kpi-title">Total Users</div>
            <div class="kpi-value">{{ number_format($analytics['metrics']['users']['total'] ?? 0) }}</div>
            @php $chg = $analytics['metrics']['users']['change'] ?? 0; @endphp
            <div class="kpi-change {{ ($chg ?? 0) >= 0 ? 'kpi-up' : 'kpi-down' }}">{{ number_format(abs($chg), 2) }}% vs previous</div>
        </div>
        <div class="kpi">
            <div class="kpi-title">Tickets Sold</div>
            <div class="kpi-value">{{ number_format($analytics['metrics']['tickets']['sold'] ?? 0) }}</div>
            @php $chg = $analytics['metrics']['tickets']['change'] ?? 0; @endphp
            <div class="kpi-change {{ ($chg ?? 0) >= 0 ? 'kpi-up' : 'kpi-down' }}">{{ number_format(abs($chg), 2) }}% vs previous</div>
        </div>
        <div class="kpi">
            <div class="kpi-title">Conversion Rate</div>
            <div class="kpi-value">{{ number_format($analytics['metrics']['conversion']['rate'] ?? 0, 2) }}%</div>
            @php $chg = $analytics['metrics']['conversion']['change'] ?? 0; @endphp
            <div class="kpi-change {{ ($chg ?? 0) >= 0 ? 'kpi-up' : 'kpi-down' }}">{{ number_format(abs($chg), 2) }}% vs previous</div>
        </div>
    </div>

    {{-- Top Events --}}
    <div class="section">
        <div class="section-title">Top Events</div>
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Venue</th>
                    <th style="text-align:right;">Tickets Sold</th>
                    <th style="text-align:right;">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($analytics['topEvents'] ?? []) as $event)
                    <tr>
                        <td>{{ $event['name'] ?? '-' }}</td>
                        <td>{{ $event['venue'] ?? '-' }}</td>
                        <td style="text-align:right;">{{ number_format($event['tickets_sold'] ?? 0) }}</td>
                        <td style="text-align:right;">${{ number_format($event['revenue'] ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No event data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Popular Categories --}}
    <div class="section">
        <div class="section-title">Popular Categories</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th style="text-align:right;">Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($analytics['topCategories'] ?? []) as $cat)
                    <tr>
                        <td>{{ $cat['name'] ?? '-' }}</td>
                        <td style="text-align:right;">{{ number_format($cat['percentage'] ?? 0, 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="2">No category data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Traffic Sources --}}
    <div class="section">
        <div class="section-title">Traffic Sources</div>
        <table>
            <thead>
                <tr>
                    <th>Source</th>
                    <th style="text-align:right;">Visitors</th>
                    <th style="text-align:right;">Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($analytics['trafficSources'] ?? []) as $src)
                    <tr>
                        <td>{{ $src['name'] ?? '-' }}</td>
                        <td style="text-align:right;">{{ number_format($src['visitors'] ?? 0) }}</td>
                        <td style="text-align:right;">{{ number_format($src['percentage'] ?? 0, 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No traffic data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- System Health --}}
    <div class="section">
        <div class="section-title">System Health</div>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Description</th>
                    <th style="text-align:right;">Status</th>
                    <th style="text-align:right;">Uptime</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($analytics['systemHealth'] ?? []) as $svc)
                    <tr>
                        <td>{{ $svc['name'] ?? '-' }}</td>
                        <td>{{ $svc['description'] ?? '-' }}</td>
                        <td style="text-align:right;">{{ ucfirst($svc['status'] ?? 'unknown') }}</td>
                        <td style="text-align:right;">{{ $svc['uptime'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No system health data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        HD Tickets &mdash; Generated on {{ $generated_at->toDayDateTimeString() }}
    </div>
</body>
</html>
