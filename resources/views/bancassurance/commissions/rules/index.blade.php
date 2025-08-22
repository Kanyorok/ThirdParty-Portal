@extends('layouts.app')
@section('title', 'Commission Rules')

@section('content')
<div class="container mt-4">
    <h4>📋 Commission Rules</h4>

    <a href="{{ route('commissions.rules.create') }}" class="btn btn-primary mb-3">➕ Add Commission Rule</a>

    @if($rules->isEmpty())
        <p class="text-muted">No commission rules found.</p>
    @else
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Policy Type</th>
                    <th>Insurance Provider</th>
                    <th>Type</th>
                    <th>Value (%)</th>
                    <th>Remarks</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rules as $rule)
                <tr>
                    <td>{{ $rule->Id }}</td>
                    <td>{{ $rule->PolicyType }}</td>
                    <td>{{ $rule->InsuranceProvider }}</td>
                    <td>{{ $rule->CommissionType }}</td>
                    <td>{{ number_format($rule->CommissionValue, 2) }}</td>
                    <td>{{ $rule->Remarks }}</td>
                    <td>{{ $rule->IsActive ? '✅ Active' : '❌ Inactive' }}</td>
                                    <td>
                    @if($rule->CommissionType === 'Tiered')
                        <a href="{{ route('bancassurance.commissions.tiers.index', $rule->Id) }}" class="btn btn-sm btn-outline-secondary">⚙ Manage Tiers</a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
