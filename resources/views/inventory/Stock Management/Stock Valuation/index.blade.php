@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('stockvaluationhistory.create') }}" class="btn btn-success">➕ Manual Stock Valuation</a>

  <h4 class="fw-bold mb-3">📊 Stock Valuation History</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Date</th>
        <th>Item</th>
        <th>Store</th>
        <th>Movement Type</th>
        <th>Ref No</th>
        <th>Qty</th>
        <th>Unit Cost</th>
        <th>Total Value</th>
        <th>Cost Method</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>2025-05-01</td>
        <td>A4 Paper</td>
        <td>Main Store</td>
        <td>GRN</td>
        <td>GRN-2025-0001</td>
        <td>100</td>
        <td>25</td>
        <td>2500</td>
        <td>AVERAGE</td>
        <td>Opening stock</td>
      </tr>
      <tr>
        <td>2</td>
        <td>2025-05-02</td>
        <td>Printer</td>
        <td>Back Store</td>
        <td>Adjust</td>
        <td>ADJ-2025-0003</td>
        <td>-1</td>
        <td>10000</td>
        <td>-10000</td>
        <td>STANDARD</td>
        <td>Damaged item removed</td>
      </tr>
    </tbody>
  </table>
</div>
@endsection