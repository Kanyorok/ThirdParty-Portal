@extends('layouts.app')
@section('title', 'Property Blocks')
@section('content')
<div class="container mt-4">
  
<a href="{{ route('addblock.create') }}" class="btn btn-primary mb-3">Add Block</a>

  <h4 class="fw-bold mb-3">📋 Property Blocks</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Block Name</th>
        <th>Property</th>
        <th>Description</th>
        <th>Floors</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Block A</td>
        <td>Sunset Plaza</td>
        <td>Main tower facing west</td>
        <td>5</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Tower 1</td>
        <td>Mountain View Estate</td>
        <td>High-rise wing</td>
        <td>10</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">👁 View</button>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection