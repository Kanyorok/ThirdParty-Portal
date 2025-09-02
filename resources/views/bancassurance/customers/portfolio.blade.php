@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Customer Portfolio</h2>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $customer->FullName ?? 'N/A' }}</h5>
            <p class="card-text">
                <strong>National ID:</strong> {{ $customer->NationalID ?? 'N/A' }}<br>
                <strong>Phone:</strong> {{ $customer->PhoneNumber ?? 'N/A' }}<br>
                <strong>Email:</strong> {{ $customer->Email ?? 'N/A' }}<br>
            </p>
        </div>
    </div>

    <h4>Policies</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Policy Number</th>
                <th>Product</th>
                <th>Insurer</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($policies as $policy)
                <tr>
                    <td>{{ $policy->PolicyNumber ?? $policy->Id }}</td>
                    <td>{{ $policy->product->Name ?? 'N/A' }}</td>
                    <td>{{ $policy->insurer->Name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</td>
                    <td>{{ $policy->Status->label() ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No policies found for this customer.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
