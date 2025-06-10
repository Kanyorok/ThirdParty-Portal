@extends('layouts.app')
@section('title', 'Payment Frequencies')
@section('content')
<div class="container mt-4">
<a href="{{ route('paymentfrequency.create') }}" class="btn btn-primary mb-3">New Payment Frequency</a>
  <h4 class="fw-bold mb-3">📋 Payment Frequencies</h4>

    @if($properties->count())
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
    @foreach ($properties as $property)
      <tr>
          <td>{{ $loop->iteration ?? '-' }}</td>
          <td>{{ $property->FrequencyName ?? '-' }}</td>
          <td>{{ $property->FrequencyCode ?? '-' }}</td>
          <td>{{ $property->NumberOfMonths ?? '-' }}</td>
          <td>{{ $property->Description ?? '-' }}</td>
        <td>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No payment frequency records registered yet.</p>
    @endif
</div>
@endsection
