@extends('layouts.app')
@section('title', 'Pricing Rules')

@section('content')
    <div class="container mt-4">
        <h4>💰 Insurance Pricing Rules</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-3 text-end">
            <a href="{{ route('bancassurance.pricing.create') }}" class="btn btn-primary">➕ Add Pricing Rule</a>
        </div>

        @if($rules->isEmpty())
            <p class="text-muted">No pricing rules configured yet.</p>
        @else
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Provider</th>
                    <th>Product</th>
                    <th>Coverage Range</th>
                    <th>Age Range</th>
                    <th>Tenure (Months)</th>
                    <th>Rate (%)</th>
                    <th>Remarks</th>
                    <th>Created</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rules as $rule)
                    <tr>
                        <td>{{ $rule->Id }}</td>
                        <td>{{ $rule->ProviderName }}</td>
                        <td>{{ $rule->ProductName }}</td>
                        <td>
                            KES {{ number_format($rule->MinCoverageAmount, 2) }} -
                            {{ number_format($rule->MaxCoverageAmount, 2) }}
                        </td>
                        <td>{{ $rule->MinAge }} - {{ $rule->MaxAge }}</td>
                        <td>{{ $rule->MinTenureMonths }} - {{ $rule->MaxTenureMonths }}</td>
                        <td>{{ number_format($rule->PremiumRate * 100, 2) }}%</td>
                        <td>{{ $rule->Remarks ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($rule->CreatedAt)->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('bancassurance.pricing.edit', $rule->Id) }}"
                               class="btn btn-sm btn-warning">✏️ Edit</a>
                            <form method="POST" action="{{ route('bancassurance.pricing.destroy', $rule->Id) }}"
                                  style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">🗑️ Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
