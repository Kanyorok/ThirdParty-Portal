@php
    use Carbon\Carbon;
    use App\Enums\Core\ApprovalEnum;
@endphp
@extends('layouts.app')
@section('title', 'Lease Approval')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">Lease {{ $lease->LeaseNumber ?? 'N/A' }}</h4>

    <table class="table table-sm">
        <tbody>
            <tr>
                <th>Tenant</th>
                <td>{{ $lease->tenant->TenantName ?? 'N/A' }}</td>
                <th>Property</th>
                <td>{{ $lease->property->PropertyName ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Unit</th>
                <td>{{ $lease->unit->UnitName ?? 'N/A' }}</td>
                <th>Block / Floor</th>
                <td>{{ $lease->block->BlockName ?? 'N/A' }} / {{ $lease->floor->FloorName ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td>{{ $lease->StartDate ? Carbon::parse($lease->StartDate)->format('d/m/Y') : 'N/A' }}</td>
                <th>End Date</th>
                <td>{{ $lease->EndDate ? Carbon::parse($lease->EndDate)->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Rent</th>
                <td>{{ number_format($lease->MonthlyRent ?? 0, 2, '.', ',') }}</td>
                <th>Deposit</th>
                <td>{{ number_format($lease->Deposit ?? 0, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <th>Special Terms</th>
                <td colspan="3">{{ $lease->SpecialTerms ?? 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-3">
        <form method="POST" action="{{ route('propertyapproval.approve', $lease->Id) }}" class="d-inline">@csrf
            <button type="submit" class="btn btn-success">Approve</button>
        </form>

        <button class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#rejectForm">Reject</button>

        <div class="collapse mt-3" id="rejectForm">
            <form method="POST" action="{{ route('propertyapproval.reject', $lease->Id) }}">@csrf
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-danger">Confirm Reject</button>
            </form>
        </div>
    </div>
</div>
@endsection