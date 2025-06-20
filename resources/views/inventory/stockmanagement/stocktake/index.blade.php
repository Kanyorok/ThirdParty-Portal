@extends('layouts.app')
@section('title', ' Stock Take Records')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 Stock Take Records</h4>
  <a href="{{ route('stocktake.create') }}" class="btn btn-success">➕ Add New Stock Take</a>
  @if($stocks->count())
  <table id="stocktake"  class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Store</th>
        <th>Counted By</th>
        <th>Date</th>
        <th>Posted By</th>
        <th>Posted Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($stocks as $stock)
      <tr>
        <td>{{$loop->iteration}}</td>
        <td>{{ $stock->branch->Name }}</td>
        <td>{{$stock->store->StoreName}}</td>
        <td>{{$stock->countedby->Name ?? 'N/A'}}</td>
        <td>{{ \Carbon\Carbon::parse($stock->CountDate)->format('d/m/Y') }}</td>
        <td>{{$stock->createdby->Name}}</td>
        <td>{{ \Carbon\Carbon::parse($stock->CreatedOn)->format('d/m/Y') }}</td>
        <td>
          <a href="{{ route('stocktake.show', $stock->Id) }}" class="btn btn-sm btn-info">👁 View</a>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#stocktake').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection