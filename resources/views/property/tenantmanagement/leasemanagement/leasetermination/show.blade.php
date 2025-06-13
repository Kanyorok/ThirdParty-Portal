@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Patent Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Lease</dt>
                    <dd class="col-sm-8">{{ $leasetermination->LeaseID }}</dd>

                    <dt class="col-sm-4">Termination Date</dt>
                    <dd class="col-sm-8">{{ $leasetermination->TerminationDate ?? '-' }}</dd>

                    <dt class="col-sm-4">Reason</dt>
                    <dd class="col-sm-8">{{ $leasetermination->TerminationReason ?? '-' }}</dd>

                    <dt class="col-sm-4">Remarks</dt>
                    <dd class="col-sm-8">{{ $leasetermination->Remarks ?? '-' }}</dd>

                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="#" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
