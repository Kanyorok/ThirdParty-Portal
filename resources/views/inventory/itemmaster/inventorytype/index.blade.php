@extends('layouts.app')
@section('title', 'Inventory Types')
@section('content')
<div class="container mt-4">
    <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('inventorytype.create') }}" class="btn btn-success">➕ Add Inventory Type</a>
     </div>

  <h4>Inventory Types</h4>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Type</th>
        <th>Active</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Example Row -->
      <tr>
        <td>1</td>
        <td>Perishable</td>
        <td>✔️</td>
        <td>
          <button class="btn btn-sm btn-info">Edit</button>
          <button class="btn btn-sm btn-danger">Delete</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

@endsection