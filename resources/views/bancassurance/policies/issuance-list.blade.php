@extends('layouts.app')
@section('title', 'Policies Ready for Issuance')

@section('content')
<div class="container mt-4">
    <h4>📄 Policies Approved for Issuance</h4>

    <table class="table table-bordered mt-3">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy #</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Insurer</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($policies as $policy)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $policy->PolicyNumber ?? '—' }}</td>
                <td>{{ $policy->CustomerName }}</td>
                <td>{{ $policy->ProductName }}</td>
                <td>{{ $policy->InsurerName }}</td>
                <td>{{ $policy->Status }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->CreatedAt)->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('bancassurance.policies.issueForm', $policy->Id) }}"
                       class="btn btn-sm btn-success">✅ Issue Now</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">No policies ready for issuance.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
