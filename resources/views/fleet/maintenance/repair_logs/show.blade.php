@extends('layouts.app')
@section('title', 'Repair Log Details')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">🔍 {{ $repair->RepairID }} Repair Log Details</h4>

        <dl class="row">
            <dt class="col-sm-3">Vehicle</dt>
            <dd class="col-sm-9">{{ $repair->vehicle->RegistrationNo ?? '-' }}</dd>

            <dt class="col-sm-3">Repair Type</dt>
            <dd class="col-sm-9">{{ $repair->repairType->Description }}</dd>

            <dt class="col-sm-3">Repair Date</dt>
            <dd class="col-sm-9">{{ $repair->RepairDate }}</dd>

            <dt class="col-sm-3">Vendor</dt>
            <dd class="col-sm-9">{{ $repair->vendor->party->ThirdPartyName ?? 'N/A' }}</dd>

            <dt class="col-sm-3">Cost</dt>
            <dd class="col-sm-9">{{ number_format($repair->Cost, 2) }} KES</dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $repair->Description }}</dd>

            <dt class="col-sm-3">Notes</dt>
            <dd class="col-sm-9">{{ $repair->Notes }}</dd>
{{-- 
            <dt class="col-sm-3">Maintenance Schedule</dt>
            <dd class="col-sm-9">{{ $repair->schedule->ScheduleID }}</dd> --}}
        </dl>
        <div class="mt-4">
            <a href="{{ route('fleet.repair_logs.index') }}" class="btn btn-secondary">⬅ Back</a>
            <a href="{{ route('fleet.repair_logs.edit', $repair->Id) }}" class="btn btn-warning">✏ Edit</a>
        </div>
@endsection
