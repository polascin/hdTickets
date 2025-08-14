SYSTEM ALERT: {{ strtoupper($alertType) }}

{{ $message }}

Alert Level: {{ ucfirst($level) }}
Timestamp: {{ $timestamp->format('Y-m-d H:i:s T') }}
@if($data)

Additional Data:
@foreach($data as $key => $value)
- {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_array($value) ? json_encode($value) : $value }}
@endforeach
@endif

@if($actionRequired)
*** IMMEDIATE ACTION REQUIRED ***

This alert requires immediate attention from the system administrator.
@endif

@if($troubleshootingSteps)

Troubleshooting Steps:
@foreach($troubleshootingSteps as $step)
{{ $loop->iteration }}. {{ $step }}
@endforeach
@endif

View System Dashboard: {{ config('app.url') }}/admin/monitoring

@if($isCritical)
*** CRITICAL ALERT ***
This is a critical system alert. Please address this issue immediately to prevent service disruption.
@endif

Thanks,
{{ config('app.name') }} Monitoring System
