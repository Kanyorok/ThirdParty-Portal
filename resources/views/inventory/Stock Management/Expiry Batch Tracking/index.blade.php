@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('expirytracking.create') }}" class="btn btn-sm btn-light">➕ Add Expiry Batch Manually</a>
  
<h4 class="fw-bold mb-3">📋 Expiry / Perishable Batch List</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Item</th>
        <th>Batch No</th>
        <th>Store</th>
        <th>Branch</th>
        <th>Received</th>
        <th>Expiry</th>
        <th>Qty</th>
        <th>Days to Expiry</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      <tr class="table-warning">
        <td>1</td>
        <td>Vitamin C</td>
        <td>BATCH-00123</td>
        <td>Cold Room</td>
        <td>Branch A</td>
        <td>2025-04-01</td>
        <td>2025-05-15</td>
        <td>80</td>
        <td>13</td>
        <td>Expiring soon</td>
      </tr>
      <tr class="table-danger">
        <td>2</td>
        <td>Milk Powder</td>
        <td>BATCH-00999</td>
        <td>Main Store</td>
        <td>Branch B</td>
        <td>2025-01-10</td>
        <td>2025-04-30</td>
        <td>40</td>
        <td>-2</td>
        <td>Expired</td>
      </tr>
    </tbody>
  </table>
</div>
@endsection