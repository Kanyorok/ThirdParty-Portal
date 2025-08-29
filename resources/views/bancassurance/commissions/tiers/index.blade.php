@extends('layouts.app')
@section('title', 'Commission Tiers')

@section('content')
    <div class="container mt-4">
        <h4>📊 Manage Tiers for Rule #{{ $ruleId }}</h4>

        <form action="{{ route('bancassurance.commissions.tiers.store', $ruleId) }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="number" step="0.01" name="MinValue" class="form-control" placeholder="Min Value"
                           required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="MaxValue" class="form-control"
                           placeholder="Max Value (or leave blank)">
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="CommissionRate" class="form-control" placeholder="Rate (%)"
                           required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-success" type="submit">➕ Add Tier</button>
                </div>
            </div>
        </form>

        <hr>

        <table class="table mt-3">
            <thead class="table-light">
            <tr>
                <th>Min Value</th>
                <th>Max Value</th>
                <th>Rate (%)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tiers as $tier)
                <tr>
                    <td>{{ number_format($tier->MinValue, 2) }}</td>
                    <td>{{ $tier->MaxValue ? number_format($tier->MaxValue, 2) : 'No Limit' }}</td>
                    <td>{{ $tier->CommissionRate }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
