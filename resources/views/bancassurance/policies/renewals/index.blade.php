@extends('layouts.app')
@section('title', 'Policy Renewals')

@section('content')
    <div class="container mt-4">
        <h4>Renewal Candidates (Expiring Soon)</h4>

        <table class="table table-striped mt-3">
            <thead>
            <tr>
                <th>#</th>
                <th>Policy Number</th>
                <th>Customer</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($policies as $policy)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $policy->PolicyNumber }}</td>
                    <td>{{ $policy->customer->FullName }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</td>
                    <td>{{ $policy->Status->label() }}</td>
                    <td><a href="{{ route('bancassurance.policies.renewalForm', $policy->Id) }}" class="btn btn-sm btn-outline-primary">Renew</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No renewable policies found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
