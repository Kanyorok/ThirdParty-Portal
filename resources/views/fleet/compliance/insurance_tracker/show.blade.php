@extends('layouts.app')
@section('title', 'View Insurance Record')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">Insurance Record Details</h4>

    <div class="row">
        <div class="col-md-6">
            <dl class="row">
                <dt class="col-sm-5">Vehicle:</dt>
                <dd class="col-sm-6">{{ $record->vehicle->RegistrationNo ?? 'N/A' }}</dd>

                <dt class="col-sm-5">Policy Number:</dt>
                <dd class="col-sm-6">{{ $record->PolicyNumber }}</dd>

                <dt class="col-sm-5">Insurance Provider:</dt>
                <dd class="col-sm-6">{{ $record->insurance->Name ?? 'N/A' }}</dd>

                <dt class="col-sm-5">Premium Amount:</dt>
                <dd class="col-sm-6">KES {{ number_format($record->PremiumAmount, 2) }}</dd>

                <dt class="col-sm-5">Coverage Period:</dt>
                <dd class="col-sm-6">
                    {{ \Carbon\Carbon::parse($record->CoverageStartDate)->format('d/m/Y') }} —
                    {{ \Carbon\Carbon::parse($record->CoverageEndDate)->format('d/m/Y') }}
                </dd>
            </dl>
        </div>

        <div class="col-md-6">
            <dl class="row">
                <dt class="col-sm-5">Renewal Reminder:</dt>
                <dd class="col-sm-6">{{ \Carbon\Carbon::parse($record->RenewalReminderDate)->format('d/m/Y') }}</dd>

                <dt class="col-sm-5">Status:</dt>
                    @php
                        $statusDescription = $record->insuranceStatus->Description ?? 'N/A';
                        $statusColor = match(strtolower($statusDescription)) {
                            'active' => 'success',
                            'expired' => 'danger',
                            'pending' => 'warning',
                            default => 'secondary',
                        };
                    @endphp
                    <dd class="col-sm-6">
                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($statusDescription) }}</span>
                    </dd>


                <dt class="col-sm-5">Notes:</dt>
                <dd class="col-sm-6">{{ $record->Notes ?? '—' }}</dd>

                <dt class="col-sm-5">Document:</dt>
                <dd class="col-sm-6">
                    @if($record->DocumentPath)
                        <a href="{{ Storage::url($record->DocumentPath) }}" target="_blank">📄 View Document</a>
                    @else
                        No document uploaded.
                    @endif
                </dd>
            </dl>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-start gap-2">
        <a href="{{ route('fleet.insurance_tracker.edit', $record->Id) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('fleet.insurance_tracker.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
