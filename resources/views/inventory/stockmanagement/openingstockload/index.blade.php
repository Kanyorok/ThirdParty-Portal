@extends('layouts.app')
@section('title', 'Opening Stock Records')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 Opening Stock Records</h4>
  <a href="{{ route('openingstock.create') }}" class="btn btn-success">➕ Add New Stock Take</a>
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Branch</th>
        <th>Store</th>
        <th>Item</th>
        <th>Qty Entered</th>
        <th>UOM</th>
        <th>Qty (Base)</th>
        <th>Value</th>
        <th>Date</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Branch A</td>
        <td>Main Store</td>
        <td>A4 Paper</td>
        <td>10</td>
        <td>dozen</td>
        <td>120</td>
        <td>2400</td>
        <td>2025-05-01</td>
        <td>Initial load</td>
      </tr>
      <tr>
        <td>2</td>
        <td>Branch B</td>
        <td>Back Store</td>
        <td>Printer</td>
        <td>5</td>
        <td>pcs</td>
        <td>5</td>
        <td>50000</td>
        <td>2025-05-01</td>
        <td>Opening stock</td>
      </tr>
    </tbody>
  </table>
</div>
@endsection