@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('addunit.create') }}" class="btn btn-primary mb-3">Add Unit</a>

  <h4 class="fw-bold mb-3">📋 Property Units</h4>
    @if($units->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
          <th>Property</th>
          <th>Block</th>
          <th>Floor</th>
          <th>Unit Code</th>
          <th>Size (sq.ft)</th>
          <th>Rentable?</th>
          <th>Status</th>
          <th>Remarks</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($units as $unit)
      <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $unit->property->PropertyName }}</td>
          <td>{{ $unit->blocks?->BlockName ?? 'N/A' }}</td>
          <td>{{ $unit->floors?->FloorLabel ?? 'N/A' }}</td>
          <td>{{ $unit->UnitCode }}</td>
          <td>{{ $unit->UnitSize }}</td>
          <td>{{ $unit->IsRentable ? 'Yes' : 'No' }}</td>
          <td>{{ $unit->CurrentStatus ? 'Vacant' : 'Occupied' }}</td>
          <td>{{ $unit->Remarks }}</td>
        <td>
        <a href="{{ route('addunit.edit', $unit->Id) }}" class="btn btn-sm btn-warning">Edit</a>
        <form action="{{ route('addunit.destroy', $unit->Id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this property?');">Delete</button>
         </form>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No property unit registered yet.</p>
    @endif
</div>
@endsection
