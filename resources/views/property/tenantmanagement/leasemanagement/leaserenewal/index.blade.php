@extends('layouts.app')
@section('title', 'Lease Renewals')
@section('content')
<div class="container mt-4">
<a href="{{ route('renewlease.create') }}" class="btn btn-primary mb-3">Renew Lease</a>
  <h4 class="fw-bold mb-3">📋 Lease Renewals</h4>

@if($leaserenewals->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Current Lease</th>
        <th>End Date of Current Lease</th>
        <th>New Start Date</th>
        <th>New End Date</th>
        <th>New Monthly Rent</th>
        <th>Payment Frequency</th>
        <th>Remarks or Changes</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($leaserenewals as $leaserenewal)
      <tr>
        <td>{{ $loop->iteration ?? '-' }}</td>
        <td>{{ $leaserenewal->CurrentLease ?? '-' }}</td>
        <td>{{ $leaserenewal->EndDateCurrentLease ?? '-' }}</td>
        <td>{{ $leaserenewal->NewStartDate ?? '-' }}</td>
        <td>{{ $leaserenewal->NewEndDate ?? '-' }}</td>
        <td>{{ $leaserenewal->NewMonthlyRent ?? '-' }}</td>
        <td>{{ $leaserenewal->PaymentFrequency ?? '-' }}</td>
        <td>{{ $leaserenewal->Remarks ?? '-' }}</td>
        <td><a href="{{ route('renewlease.show', $leaserenewal->id) }}" class="btn btn-sm btn-outline-secondary">📄 View</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
@else
 <p>No lease renewals registered yet.</p>
@endif
</div>
@endsection