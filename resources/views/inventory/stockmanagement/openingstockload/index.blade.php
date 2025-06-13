@extends('layouts.app')
@section('title', 'Opening Stock Records')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 Opening Stock Records</h4>
  <a href="{{ route('openingstock.create') }}" class="btn btn-success">➕ Add New Stock Take</a>
  <table id="openstock" class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Store</th>
        <th>Item</th>
        <th>Qty Entered</th>
        <th>UOM</th>
        <th>Value</th>
        <th>Date</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      @foreach( $entries as $entry)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $entry->branch->Name?? '-' }}</td>
        <td>{{ $entry->store->StoreName ?? '-' }}</td>
        <td>{{ $entry->item->ItemName?? '-' }}</td>
        <td>{{ $entry->Quantity?? '-' }}</td>
        <td>{{ $entry->uom->Code?? '-' }}</td>
        <td>{{ $entry->Value?? '-' }}</td>
        <td>{{ $entry->Date?? '-' }}</td>
        <td>{{ $entry->Remarks?? '-' }}</td>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#openstock').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection