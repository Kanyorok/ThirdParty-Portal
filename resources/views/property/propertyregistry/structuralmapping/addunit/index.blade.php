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
        <td>{{ $unit->PropertyID }}</td>
        <td>{{ $unit->BlockID }}</td>
        <td>{{ $unit->FloorID }}</td>
        <td>{{ $unit->UnitCode }}</td>
        <td>{{ $unit->UnitSize }}</td>
        <td>{{ $unit->IsRentable? 'Yes' : 'No' }}</td>
        <td>{{ $unit->CurrentStatus }}</td> 
        <td>{{ $unit->Remarks }}</td>
        <td>
          <a href="{{ route('addunit.show', $unit->id) }}" class="btn btn-sm btn-info">👁 View</a>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
<p>No property unit registered yet.</p>
@endif
</div>
@endsection