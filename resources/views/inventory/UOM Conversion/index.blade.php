@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📋 UOM Conversion Mappings</h4>
  <a href="{{ route('uomconversion.create') }}" class="btn btn-success">➕ Add New Stock Take</a>
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Item</th>
        <th>Base UOM</th>
        <th>Alternate UOM</th>
        <th>Conversion Factor</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>A4 Paper</td>
        <td>pcs</td>
        <td>dozen</td>
        <td>12</td>
        <td>Standard box count</td>
      </tr>
      <tr>
        <td>2</td>
        <td>Printer</td>
        <td>unit</td>
        <td>box</td>
        <td>1</td>
        <td>One printer per box</td>
      </tr>
    </tbody>
  </table>
</div>
@endsection