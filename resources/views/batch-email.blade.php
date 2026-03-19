<h2>Approval Batch</h2>

<p>Hello {{ $approver->name }}</p>

<p>You have {{ $items->count() }} items pending approval.</p>

<table border="1" cellpadding="5">

<thead>
<tr>
@foreach($columns as $label)
<th>{{ $label }}</th>
@endforeach
</tr>
</thead>

<tbody>

@foreach($items as $item)

<tr>

@foreach($columns as $key => $label)

<td>

@if(str_contains($key,'.'))

{{ data_get($item,$key) }}

@else

{{ $item->$key }}

@endif

</td>

@endforeach

</tr>

@endforeach

</tbody>

</table>

<p>

<a href="{{ $approve_url }}">Approve All</a>

</p>
