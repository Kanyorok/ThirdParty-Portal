@extends('layouts.app')
@section('title', 'Policy Proposals')

@section('content')
    <div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('bancassurance.policies.create') }}" class="btn btn-primary">New Proposal</a>
    </div>

        <!-- 🔍 Filter Section -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">-- All --</option>
                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">From (Start Date)</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">To (End Date)</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <input type="text" name="customer" value="{{ request('customer') }}" class="form-control"
                       placeholder="e.g. John Doe">
            </div>

        <div class="col-12 text-end">
            <button class="btn btn-secondary">Filter</button>
            <a href="{{ route('bancassurance.policies.index') }}" class="btn btn-outline-dark">♻️ Reset</a>
        </div>
    </form>

    <!-- Policy Table -->
    <table class="table table-striped table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Insurer</th>
                <th>Sum Assured</th>
                <th>Premium</th>
                <th>Start of Policy</th>
                <th>End of Policy</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($policies as $policy)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                    <td>{{ $policy->product->Name ?? '-'}}</td>
                    <td>{{ $policy->insurer->Name ?? '—' }}</td>
                    <td>{{ number_format($policy->SumAssured, 2) }}</td>
                    <td>{{ number_format($policy->PremiumAmount, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</td>
                    <td>{{$policy->Status->Label()}}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">No policy proposals found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

    </div>
@endsection
