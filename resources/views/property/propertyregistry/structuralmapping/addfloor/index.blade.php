@extends('layouts.app')
@section('title', 'Floors per Block')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

    <a href="{{ route('addfloor.create') }}" class="btn btn-primary mb-3">Add Floor</a>

    <p><small>List of floors in property blocks</small></p>

    @if($floors->count())
        <table id="propertyfloors" class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
          <th>#</th>
          <th>Property</th>
          <th>Block</th>
          <th>Floor Name</th>
          <th>Notes</th>
          <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($floors as $floor)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $floor->property->PropertyName ?? '-'}}</td>
            <td>{{ $floor->block->BlockName ?? '-'}}</td>
            <td>{{ $floor->FloorLabel ?? '-'}}</td>
            <td>{{ $floor->FloorNotes ?? '-'}}</td>
        <td>
            <a href="{{ route('addfloor.edit', $floor->Id) }}" class="btn btn-sm btn-warning">Edit</a>
            <form action="{{ route('addfloor.destroy', $floor->Id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger"
                        onclick="return confirm('Are you sure you want to delete this property?');">Delete
                </button>
            </form>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No property floor registered yet.</p>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertyfloors').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
