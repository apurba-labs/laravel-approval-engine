<!DOCTYPE html>
<html>
<head>
    <style>
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .button { background-color: #4CAF50; border: none; color: white; padding: 15px 32px; 
                  text-align: center; text-decoration: none; display: inline-block; font-size: 16px; 
                  margin: 4px 2px; cursor: pointer; border-radius: 8px; }
    </style>
</head>
<body>
    <p>Hello {{ $batch->role }}</p>
    <h2>Approval Request for {{ ucfirst($batch->module) }}</h2>
    <p>The following records have been approved and are ready for the next stage (Stage {{ $batch->stage }}).</p>

    <table class="table">
        <thead>
            <tr>
                @foreach($module->displayColumns() as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    @foreach($module->displayColumns() as $column => $label)
                        <td>{{ data_get($record, $column, 'N/A') }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 30px;">
        <a href="{{ url('/approval/batch/'.$batch->token) }}" class="button">Approve This Batch</a>
    </p>

    <p><small>If the button doesn't work, copy and paste this link: {{ url('/approval/batch/'.$batch->token) }}</small></p>
</body>
</html>
