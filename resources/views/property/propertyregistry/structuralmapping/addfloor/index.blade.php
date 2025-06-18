@extends('layouts.app')
@section('title', 'Floors per Block')
@section('content')
<div class="container mt-4">

    <a href="{{ route('addfloor.create') }}" class="btn btn-primary mb-3">Add Floor</a>

    <h4 class="fw-bold mb-3">📋 Floors per Block</h4>

    @if($floors->count())
  <table class="table table-bordered table-striped align-middle">
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
            <td>{{ $floor->PropertyID }}</td>
            <td>{{ $floor->BlockID }}</td>
            <td>{{ $floor->FloorLabel }}</td>
            <td>{{ $floor->FloorNotes }}</td>
        <td>
        <a href="{{ route('addfloor.edit', $floor->Id) }}" class="btn btn-sm btn-warning">Edit</a>
        <form action="{{ route('addfloor.destroy', $floor->Id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this property?');">Delete</button>
         </form>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No property floor registered yet.</p>
    @endif
</div>
@endsection
