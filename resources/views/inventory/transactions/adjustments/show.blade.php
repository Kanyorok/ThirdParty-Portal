@extends('layouts.app')

@section('title', 'View Stock Adjustment')

@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <h4>🔍 Stock Adjustment Details</h4>
    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Adjustment ID:</strong> {{ $adjustment->AdjustmentId }}</p>
            <p><strong>Adjustment Date:</strong> {{ \Carbon\Carbon::parse($adjustment->AdjustmentDate)->format('Y-m-d') }}</p>
            <p><strong>Branch:</strong> {{ optional($adjustment->branch)->Name ?? 'N/A' }}</p>
            <p><strong>Reason:</strong> {{ $adjustment->Reason }}</p>
            <p><strong>Adjusted By:</strong> {{ $adjustment->adjustedBy->Name ?? 'N/A' }}</p>
            <p><strong>Status:</strong> 
                @php
                    $statusEnum = \App\Enums\Inventory\Transfers::tryFrom($adjustment->Status);
                @endphp
                @if($statusEnum)
                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                @else
                    <span class="badge bg-secondary">{{ $adjustment->Status }}</span>
                @endif
            </p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Adjustment Qty</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustment->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->item->ItemCode}}</td>
                    <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                    <td>{{ $item->AdjustmentQty }}</td>
                    <td>{{ $item->Remarks ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <a href="{{ route('transactionsadjustment.index') }}" class="btn btn-secondary mt-3">← Back to List</a>
</div>
@endsection
