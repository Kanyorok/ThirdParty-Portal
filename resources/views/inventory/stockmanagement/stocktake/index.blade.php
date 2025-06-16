@extends('layouts.app')
@section('title', ' Stock Take Records')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 Stock Take Records</h4>
  <a href="{{ route('stocktake.create') }}" class="btn btn-success">➕ Add New Stock Take</a>
  @if($stocks->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Store</th>
        <th>Counted By</th>
        <th>Date</th>
        <th>Status</th>
        <th>Posted By</th>
        <th>Posted Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($stocks as $stock)
      <tr>
        <td>{{$loop->iteration}}</td>
        <td>{{$stock->BranchId}}</td>
        <td>{{$stock->StoreId}}</td>
        <td>{{$stock->CountedBy}}</td>
        <td>{{$stock->CountDate}}</td>
        <td><span class="badge bg-warning text-dark">Pending</span></td>
        <td>{{$stock->CreatedBy}}</td>
        <td>{{$stock->CreatedOn}}</td>
        <td>
          <a href="{{ route('stocktake.show', $stock->Id) }}" class="btn btn-sm btn-info">👁 View</a>
          <button class="btn btn-sm btn-outline-success">📌 Post</button>
          <a href="{{ route('stocktake.edit', $stock->Id) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('stocktake.destroy', $stock->Id) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this type?');">Delete</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
<p>No property stock registered yet.</p>
@endif
</div>
@endsection