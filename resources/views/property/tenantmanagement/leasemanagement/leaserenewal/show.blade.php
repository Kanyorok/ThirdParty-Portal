@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
<div class="container mt-5" style="max-width: 700px;">
  <h3 class="mb-4">Patent Details</h3>
  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-4">Current Lease</dt>
        <dd class="col-sm-8">{{ $leaserenewal->CurrentLease }}</dd>

        <dt class="col-sm-4">End Date of Current Lease</dt>
        <dd class="col-sm-8">{{ $leaserenewal->EndDateCurrentLease ?? '-' }}</dd>

        <dt class="col-sm-4">New Start Date</dt>
        <dd class="col-sm-8">{{ $leaserenewal->NewStartDate ?? '-' }}</dd>

        <dt class="col-sm-4">New End Date</dt>
        <dd class="col-sm-8">{{ $leaserenewal->NewEndDate ?? '-' }}</dd>

        <dt class="col-sm-4">New Monthly Rent</dt>
        <dd class="col-sm-8">{{ $leaserenewal->NewMonthlyRent ?? '-' }}</dd>

        <dt class="col-sm-4">Payment Frequency</dt>
        <dd class="col-sm-8">{{ $leaserenewal->PaymentFrequency ?? '-' }}</dd>

        <dt class="col-sm-4">Remarks</dt>
        <dd class="col-sm-8">{{ $leaserenewal->Remarks ?? '-' }}</dd>
      </dl>
    </div>
    <div class="card-footer">
      <a href="#" class="btn btn-primary">Edit</a>
      <a href="#" class="btn btn-secondary">Back</a>
    </div>
  </div>
</div>
@endsection