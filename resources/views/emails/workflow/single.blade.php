@component('mail::message')
# New Approval Requested

A new **{{ ucfirst($instance->module) }}** requires your attention.

**Details:**
- **Amount:** {{ number_get($record, 'total_amount', 'N/A') }}
- **Submitted By:** {{ $record->creator->name ?? 'System' }}

@component('mail::button', ['url' => url('/approval/instance/' . $instance->id)])
View & Approve
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
