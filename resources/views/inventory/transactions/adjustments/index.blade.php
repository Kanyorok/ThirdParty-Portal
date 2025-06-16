@extends('layouts.app')

@section('title', 'Stock Adjustments List')

@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>🛠️ Stock Adjustment List</h4>
        <a href="{{ route('transactionsadjustment.create') }}" class="btn btn-success">➕ New Adjustment</a>
    </div>

    <div class="mb-3">
        <input type="text" class="form-control" placeholder="🔍 Search by Store, Reason, or Adjusted By" disabled>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Store</th>
                    <th>Reason</th>
                    <th>Adjusted By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $index => $adjustment)
                    <tr>
                        <td>{{ $adjustments->firstItem() + $index }}</td>
                        <td>{{ \Carbon\Carbon::parse($adjustment->AdjustmentDate)->format('Y-m-d') }}</td>
                        <td>{{ optional($adjustment->branch)->Name ?? 'N/A' }}</td>
                        <td>{{ $adjustment->Reason }}</td>
                        <td>{{ $adjustment->AdjustedBy }}</td>
                        <td>
                            @php
                                $statusEnum = \App\Enums\Inventory\Transfers::tryFrom($adjustment->Status);
                            @endphp
                            @if($statusEnum)
                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                    {{ $statusEnum->label() }}
                                </span>
                            @else
                                <span class="badge bg-secondary">Unknown</span>
                            @endif
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary">View</a>
                            @if($statusEnum === \App\Enums\Inventory\Transfers::Pending)
                                <a href="#" class="btn btn-sm btn-secondary">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No stock adjustments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $adjustments->links() }}
    </div>
</div>
@endsection
