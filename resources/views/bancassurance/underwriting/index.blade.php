@extends('layouts.app')
@section('title', 'Underwriting Review')

@section('content')
<div class="container mt-4">
    <h4>🧾 Underwriting Review</h4>

    <table class="table table-bordered mt-3">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy No</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Sum Assured</th>
                <th>Premium</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proposals as $i => $policy)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $policy->PolicyNumber ?? 'N/A' }}</td>
                    <td>{{ $policy->CustomerName }}</td>
                    <td>{{ $policy->ProductName }}</td>
                    <td>{{ number_format($policy->SumAssured, 2) }}</td>
                    <td>{{ number_format($policy->PremiumAmount, 2) }}</td>
                    <td><span class="badge bg-warning text-dark">{{ $policy->Status }}</span></td>
                    <td>
                        <a href="{{ route('bancassurance.underwriting.review', $policy->Id) }}" class="btn btn-sm btn-primary">
                            Review
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No proposals pending underwriting.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
