<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Category</th>
            <th>Unit Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->Name }}</td>
                <td>{{ ucfirst($item->Type) }}</td>
                <td>{{ optional($item->category)->Name }}</td>
                <td>{{ $item->UnitPrice }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
