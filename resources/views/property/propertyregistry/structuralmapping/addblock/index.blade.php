@extends('layouts.app')
@section('title', 'Property Blocks')
@section('content')
<div class="container mt-4">
  
<a href="{{ route('addblock.create') }}" class="btn btn-primary mb-3">Add Block</a>

  <h4 class="fw-bold mb-3">📋 Property Blocks</h4>
@if($blocks->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property</th>
        <th>Block Name</th>
        <th>Description</th>
        <th>Action</th>
      </tr>
    </thead>    
    <tbody>
       @foreach($blocks as $block)
       <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $block->property->PropertyName }}</td>
        <td>{{ $block->BlockName}}</td>
        <td>{{ $block->Description}}</td>
        <td>
          <a href="{{ route('addblock.show', $block->id) }}" class="btn btn-sm btn-info">👁 View</a>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
       </tr>
        @endforeach      
    </tbody>
  </table>
   @else
<p>No property block registered yet.</p>
@endif
</div>
@endsection