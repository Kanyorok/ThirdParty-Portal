@extends('layouts.app')
@section('title', 'Payment Frequencies')
@section('content')
<div class="container mt-4">
<a href="{{ route('paymentfrequency.create') }}" class="btn btn-primary mb-3">New Payment Frequency</a>
  <h4 class="fw-bold mb-3">📋 Payment Frequencies</h4>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Frequency Name</th>
        <th>Code</th>
        <th>Months</th>
        <th>Description</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Monthly</td>
        <td>MTH</td>
        <td>1</td>
        <td>Bills every calendar month</td>
        <td>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Quarterly</td>
        <td>QTR</td>
        <td>3</td>
        <td>Bills every 3 months</td>
        <td>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
      <tr>
        <td>3</td>
        <td>Annually</td>
        <td>ANL</td>
        <td>12</td>
        <td>Bills once every year</td>
        <td>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
@endsection