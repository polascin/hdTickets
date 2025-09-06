@component('mail::message')

# {{ $context['report_name'] ?? 'HD Tickets Analytics Report' }}

Hello {{ $recipient }},

Your scheduled analytics report has been generated and is attached to this email.

## Report Details

@component('mail::panel')
**Report Type:** {{ ucfirst($context['report_type'] ?? 'Custom') }}<br>
**Generated:** {{ now()->format('F j, Y \a\t g:i A T') }}<br>
**File Format:** {{ strtoupper($reportData['format']) }}<br>
**File Size:** {{ $reportData['size'] ? number_format($reportData['size'] / 1024 / 1024, 2) . ' MB' : 'Unknown' }}
@endcomponent

@if(!empty($context['description']))
{{ $context['description'] }}
@endif

## Report Summary

@if(isset($reportData['analytics_data']))
@component('mail::table')
| Metric | Value |
|:-------|:------|
@if(isset($reportData['analytics_data']['total_events']))
| Total Events | {{ number_format($reportData['analytics_data']['total_events']) }} |
@endif
@if(isset($reportData['analytics_data']['total_tickets']))
| Total Tickets | {{ number_format($reportData['analytics_data']['total_tickets']) }} |
@endif
@if(isset($reportData['analytics_data']['platforms_analyzed']))
| Platforms Analyzed | {{ $reportData['analytics_data']['platforms_analyzed'] }} |
@endif
@if(isset($reportData['analytics_data']['anomalies_detected']))
| Anomalies Detected | {{ number_format($reportData['analytics_data']['anomalies_detected']) }} |
@endif
@endcomponent
@endif

@if(isset($reportData['analytics_data']['sections_included']) && !empty($reportData['analytics_data']['sections_included']))
### Sections Included:
@foreach($reportData['analytics_data']['sections_included'] as $section)
- {{ ucwords(str_replace('_', ' ', $section)) }}
@endforeach
@endif

## Key Insights

@component('mail::panel')
This report contains comprehensive analytics for sports event ticket monitoring, including platform performance metrics, pricing trends, and market intelligence insights. The data has been filtered and analyzed to provide actionable business intelligence for your decision-making process.
@endcomponent

## Important Notes

- **Data Currency:** All data in this report is generated from the latest available information at the time of generation.
- **Confidentiality:** This report contains sensitive business intelligence. Please handle accordingly.
- **Questions:** If you have questions about this report or need additional analysis, please contact our analytics team.

@component('mail::button', ['url' => route('analytics.dashboard')])
View Analytics Dashboard
@endcomponent

---

**HD Tickets Analytics System**  
Sports Events Entry Tickets Monitoring & Intelligence Platform

*This is an automated report. Please do not reply to this email.*

Best regards,  
The HD Tickets Team

@endcomponent
