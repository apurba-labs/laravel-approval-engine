@component('mail::message')
# Pending Approvals Summary

You have **{{ $batch->item_count }}** pending items waiting for your approval in the **{{ ucfirst($batch->module) }}** module.


| Module | Items | Period |
| :--- | :--- | :--- |
| {{ ucfirst($batch->module) }} | {{ $batch->item_count }} | {{ $batch->window_start->format('M d') }} - {{ $batch->window_end->format('M d') }} |

@component('mail::button', ['url' => url('/approval/batch/' . $batch->token)])
Approve All Items
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent
