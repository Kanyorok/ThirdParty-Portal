@extends('layouts.app')
@section('title', 'Floors per Block')
@section('content')
<div class="container mt-4">
<a href="{{ route('addfloor.create') }}" class="btn btn-primary mb-3">Add Floor</a>
  <h4 class="fw-bold mb-3">📋 Floors per Block</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Floor Name</th>
        <th>Block</th>
        <th>Property</th>
        <th>Units</th>
        <th>Notes</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Ground Floor</td>
        <td>Block A</td>
        <td>Sunset Plaza</td>
        <td>4</td>
        <td>Shared entrance with lobby</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>2nd Floor</td>
        <td>Tower 1</td>
        <td>Mountain View Estate</td>
        <td>8</td>
        <td>Executive floor</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection