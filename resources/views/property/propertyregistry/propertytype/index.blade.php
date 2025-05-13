@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('propertytype.create') }}" class="btn btn-primary mb-3">Add Type</a>

  <h4 class="fw-bold mb-3">📋 Property Types</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Type Name</th>
        <th>Is Rentable?</th>
        <th>Can Contain Units?</th>
        <th>Description</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Building</td>
        <td>Yes</td>
        <td>Yes</td>
        <td>Multi-floor rentable structure</td>
        <td><button class="btn btn-sm btn-outline-warning">✏️ Edit</button></td>
      </tr>
      <tr>
        <td>2</td>
        <td>Land</td>
        <td>No</td>
        <td>No</td>
        <td>Raw land with no structures</td>
        <td><button class="btn btn-sm btn-outline-warning">✏️ Edit</button></td>
      </tr>
    </tbody>
  </table>
</div>
@endsection