@extends('layouts.app')
@section('title', 'Policy Register')

@section('content')
<div class="container mt-4">
    <h4>Issued Policies Register</h4>

<table class="table table-bordered mt-3">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Policy Number</th>
            <th>Customer</th>
            <th>Insurer</th>
            <th>Status</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($policies as $policy)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $policy->PolicyNumber }}</td>
            <td>{{ $policy->customer->FullName }}</td>
            <td>{{ $policy->insurer->Name }}</td>
            <td><span class="badge bg-success">{{ $policy->Status->Label() }}</span></td>
            <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</td>
<td>
    <a href="{{ route('bancassurance.policies.show', $policy->Id) }}" class="btn btn-sm btn-outline-info">View</a>
    <a href="{{ route('bancassurance.policies.endorsementForm', $policy->Id) }}" class="btn btn-sm btn-outline-primary">Endorse</a>
</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center text-muted">No issued policies found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

    </div>
@endsection
