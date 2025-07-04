@extends('layouts.app')

@section('title', 'Lease Termination Details')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Lease Termination Details</h3>
        <a href="{{ route('terminatelease.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    <form>
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label">Lease Number</label>
                    <input type="text" class="form-control" value="{{ $leasetermination->lease->LeaseNumber ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Termination Date</label>
                    <input type="text" class="form-control" 
                           value="{{ $leasetermination->TerminationDate ? \Carbon\Carbon::parse($leasetermination->TerminationDate)->format('d/m/Y') : '-' }}" 
                           readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Termination Reason</label>
                    <input type="text" class="form-control" value="{{ $leasetermination->code->Description ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" rows="3" readonly>{{ $leasetermination->Remarks ?? '—' }}</textarea>
                </div>

                @if($leasetermination->ClearanceDocument)
                <div class="mb-3">
                    <label class="form-label">Clearance Document</label>
                    <div>
                        <a href="{{ asset('storage/' . $leasetermination->ClearanceDocument) }}" target="_blank" class="btn btn-outline-info btn-sm">
                            View Uploaded Document
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <div class="card-footer bg-light d-flex justify-content-end">
                <a href="{{ route('terminatelease.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </form>
</div>
@endsection
