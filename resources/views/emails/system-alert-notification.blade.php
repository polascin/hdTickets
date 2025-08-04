@component('mail::message')
# {{ $levelIcon }} System Alert: {{ $alertType }}

{{ $message }}

**Alert Level:** {{ ucfirst($level) }}  
**Timestamp:** {{ $timestamp->format('Y-m-d H:i:s T') }}  
@if($data)
**Additional Data:**  
@foreach($data as $key => $value)
- **{{ ucfirst(str_replace('_', ' ', $key)) }}:** {{ is_array($value) ? json_encode($value) : $value }}  
@endforeach
@endif

@if($actionRequired)
@component('mail::panel')
🚨 **IMMEDIATE ACTION REQUIRED**

This alert requires immediate attention from the system administrator.
@endcomponent
@endif

@if($troubleshootingSteps)
## Troubleshooting Steps

@foreach($troubleshootingSteps as $step)
{{ $loop->iteration }}. {{ $step }}  
@endforeach
@endif

@component('mail::button', ['url' => config('app.url') . '/admin/monitoring'])
View System Dashboard
@endcomponent

@if($isCritical)
@component('mail::subcopy')
**This is a critical system alert.** Please address this issue immediately to prevent service disruption.
@endcomponent
@endif

Thanks,  
{{ config('app.name') }} Monitoring System
@endcomponent
