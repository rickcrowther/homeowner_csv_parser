@if($output)
<table class="table table-striped">
    <thead>
    <tr class="bg-light rounded">
        @foreach($line_columns as $column)
            <th>{{$column}}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($output as $homeowner)
        <tr>
            @foreach($line_columns as $column)
                <td>{{isset($homeowner[$column]) ? $homeowner[$column]: ''}}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
@endif
