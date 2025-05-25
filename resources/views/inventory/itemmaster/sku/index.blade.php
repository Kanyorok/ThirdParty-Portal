@extends('layouts.app')
@section('title', 'Stock Items')
@section('styles')    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">@endsection
@section('content')

<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center" style="background-color: #add8e6;">
      <h4 class="mb-0">SKU Items List</h4>
      <a href="{{ route('sku.create') }}" class="btn btn-success">➕ Add New Stock Item</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
    

      <table id="stockitemsTable" class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>SKU Code</th>
            <th>Item</th>
            <th>Batch</th>
            <th>Serial</th>
            <th>Perishable</th>
            <th>Saleable</th>
            <th>Purchasable</th>
            <th>Store</th>
            <th>Branch</th>
            <th>Current Qty</th>
            <th>Min</th>
            <th>Reorder</th>
            <th>Max</th>
            <th>Last Received</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        @foreach($items as $key => $item)
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $item->SKUCode }}</td>
            <td>{{ $item->item->ItemName }}</td>
            <td>{!! $item->Batch ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
            <td>{!! $item->Serial ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
            <td>{!! $item->Perishable ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
            <td>{!! $item->Saleable ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
            <td>{!! $item->Purchasable ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
            <td>{{($item->branch->Name)}}</td>
            <td>{{($item->store->StoreName)}}</td>
            <td>{{($item->CurrentQty)}}</td>
            <td>{{($item->Min)}}</td>
            <td>{{($item->Reorder)}}</td>
            <td>{{($item->Max)}}</td>
            <td>{{($item->LastReceived)}}</td>
            <td>
              <span class="badge {{ $item->Status ? 'bg-success' : 'bg-warning' }}">
              {{ $item->Status ? 'Active' : 'Inactive' }}
              </span>
            <td>{{ $item->Actions }}
              <a href="{{ route('sku.show', $item->Id) }}" class="btn btn-secondary btn-sm">View</a>
              <a href="{{ route('sku.edit', $item->Id) }}" class="btn btn-warning btn-sm">Edit</a>
              <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $item->Id }}')">Delete</a>
              <form id="delete-form-{{ $item->Id }}" action="{{ route('sku.destroy', $item->Id) }}" method="POST" style="display:none;">
              
            </td>
              <form id="delete-form-{{ $item->Id }}" action="{{ route('sku.destroy', $item->Id) }}" method="POST" style="display:none;">
               @csrf
               @method('DELETE')
              </form>
              
              <script>
              function confirmDelete(Id) {
              if (confirm('⚠️ Are you sure you want to delete this item?')) {
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
        $('#stockitemsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection