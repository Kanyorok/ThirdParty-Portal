@extends('layouts.app')
@section('title', 'Lease Agreements')
@section('content')
<div class="container mt-4">
<a href="{{ route('addlease.create') }}" class="btn btn-primary mb-3">📄 New Lease</a>
  <h4 class="fw-bold mb-3">📋 Lease Agreements</h4>

@if($newleases->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>Property ID</th>
        <th>Block ID</th>
        <th>Floor ID</th>
        <th>Unit</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Payment Frequency</th>
        <th>Monthly Rent</th>
        <th>Deposit</th>
        <th>Due Day</th>
        <th>Special Terms</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($newleases as $newlease)
      <tr>
        <td>{{ $loop->iteration ?? '-' }}</td>
        <td>{{ $newlease->Tenant ?? '-' }}</td>
        <td>{{ $newlease->PropertyID ?? '-' }}</td>
        <td>{{ $newlease->BlockID ?? '-' }}</td>
        <td>{{ $newlease->FloorID ?? '-' }}</td>
        <td>{{ $newlease->Unit ?? '-' }}</td>
        <td>{{ $newlease->StartDate ?? '-' }}</td>
        <td>{{ $newlease->EndDate ?? '-' }}</td>
        <td>{{ $newlease->PaymentFrequency ?? '-' }}</td>
        <td>{{ $newlease->MonthlyRent ?? '-' }}</td>
        <td>{{ $newlease->Deposit ?? '-' }}</td>
        <td>{{ $newlease->DueDay ?? '-' }}</td>
        <td>{{ $newlease->SpecialTerms ?? '-' }}</td>
        <td>
          <a href="{{ route('addlease.show', $newlease->id) }}" class="btn btn-info btn-sm">View</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
@else
  <p>No termination record registered yet.</p>
@endif
</div>
@endsection