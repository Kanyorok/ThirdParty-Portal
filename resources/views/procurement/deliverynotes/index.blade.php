@extends('layouts.app')
@section('title', 'Delivery Notes')

@section('content')
<div class="container mt-4">
<div class="mb-2 d-flex justify-content-between">
  <a href="{{ route('deliverynotes.create') }}"  class="btn btn-success">➕ New Delivery</a>
  </div>
    <h4 class="mb-3">📦 Delivery Notes</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Delivery Note No</th>
                <th>PO Number</th>
                <th>Supplier</th>
                <th>Delivery Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>DN-001</td>
                <td>PO-789</td>
                <td>ABC Supplies Ltd</td>
                <td>2025-07-01</td>
                <td><span class="badge bg-warning">Pending Inspection</span></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
