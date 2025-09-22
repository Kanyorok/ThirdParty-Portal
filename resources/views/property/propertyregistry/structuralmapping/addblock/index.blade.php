@extends('layouts.app')
@section('title', 'Property Blocks')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

    <a href="{{ route('addblock.create') }}" class="btn btn-primary mb-3">Add Block</a>

    <p><small>List of blocks in properties</small></p>
@if($blocks->count())
        <table id="propertyblocks" class="table table-bordered table-striped align-middle">
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
           <td>{{ $block->property->PropertyName ?? '-'}}</td>
           <td>{{ $block->BlockName ?? '-'}}</td>
           <td>{{ $block->Description ?? '-'}}</td>
        <td>
            <a href="{{ route('addblock.edit', $block->Id) }}" class="btn btn-sm btn-warning">Edit</a>
            <form action="{{ route('addblock.destroy', $block->Id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger"
                        onclick="return confirm('Are you sure you want to delete this property?');">Delete
                </button>
            </form>
        </td>
       </tr>
       @endforeach
    </tbody>
  </table>
   @else
<p>No property block registered yet.</p>
@endif
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertyblocks').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
