@extends('layouts.app')
@section('title', 'Policy Renewals')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Renewal Candidates (Expiring Soon)</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:5%">#</th>
                        <th>Policy Number</th>
                        <th>Customer</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="text-center" style="width:10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold text-primary">{{ $policy->PolicyNumber ?? '-' }}</span></td>
                            <td>{{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d M Y') }}</td>                           <td><span class="badge bg-primary">{{ $policy->Status->label() ?? '-' }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('bancassurance.policies.renewalForm', $policy->Id) }}"
                                   class="btn btn-sm btn-primary px-3">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Renew
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                <i class="bi bi-inbox me-1"></i> No renewable policies found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
