@extends('layouts.app')
@section('title', 'Property Documents')
@section('content')
<div class="container mt-4">

<a href="{{ route('attachments.create') }}" class="btn btn-primary mb-3">Attach Document</a>
  <h4 class="fw-bold mb-3">📋 Property Documents</h4>
@if($propertyattachments->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property</th>
        <th>Document Title</th>
        <th>Type</th>
        <th>Description</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($propertyattachments as $propertyattachment)
      <tr>
        <td>{{ $propertyattachment->Id }}</td>
        <td>{{ $propertyattachment->PropertyID }}</td>
        <td>{{ $propertyattachment->DocumentTitle }}</td>
        <td>{{ $propertyattachment->DocumentType }}</td>
        <td>{{ $propertyattachment->Description}}</td>       
        <td>
          <a href="#" class="btn btn-sm btn-outline-primary">⬇ Download</a>
          <button class="btn btn-sm btn-outline-danger">🗑 Delete</button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
   @else
<p>No property attachments registered yet.</p>
@endif
</div>
@endsection