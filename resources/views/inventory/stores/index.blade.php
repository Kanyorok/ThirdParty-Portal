@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('styles')    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">@endsection
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-dark text-white rounded-top-4">
      <h4 class="mb-0">Stores List</h4>
      <a href="{{ route('store.create') }}" class="btn btn-success">Add New Store</a>
    </div>
    <div class="card-body">

      <table id="storesTable" class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Store ID</th>
            <th>Store Name</th>
            <th>Branch</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        @foreach($stores as $key => $store)
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $store->StoreID }}</td>
            <td>{{ $store->StoreName }}</td>
            <td>{{($store->BranchID)}}</td>
            <td>
              <span class="badge {{ $store->Status ? 'bg-success' : 'bg-warning' }}">
              {{ $store->Status ? 'Active' : 'Inactive' }}
              </span>
            <td>{{ $store->Actions }}
              <a href="{{ route('store.show', $store->Id) }}">View</a> |
              <a href="{{ route('store.edit', $store->Id) }}">Edit</a> |
              <a href="#" onclick="confirmDelete('{{ $store->Id }}')">Delete</a>
            </td>
              <form id="delete-form-{{ $store->Id }}" action="{{ route('store.destroy', $store->Id) }}" method="POST" style="display:none;">
               @csrf
               @method('DELETE')
              </form>
              
              <script>
              function confirmDelete(Id) {
              if (confirm('⚠️ Are you sure you want to delete this store?')) {
               document.getElementById('delete-form-' + Id).submit();
               }
               }
               </script>
          </tr>
        @endforeach
      </tbody>

      </table>

    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
 
<script>
    $(document).ready(function () {
        $('#storesTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection