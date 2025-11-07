<table>
    <thead>
        <tr>
            <th>Serial</th>
            <th>Item Code</th>
            <th>Name</th>
            <th>Type</th>
            <th>Category</th>
            <th>Description</th>
            <th>Unit Price</th>
            <th>UOM</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->UniqueCode }}</td>
                <td>{{ $item->Name }}</td>
                <td>{{ ucfirst($item->Type) }}</td>
                <td>{{ optional($item->category)->Name }}</td>
                <td>{{ $item->Description }}</td>
                <td>{{ $item->Currency }} {{ $item->UnitPrice }}</td>
                <td>{{ $item->UOM }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
