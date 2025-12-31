@php use Carbon\Carbon; @endphp
@php use App\Enums\Inventory\Transfers; @endphp
@extends('layouts.app')

@section('title', 'View Stock Adjustment')

@section('content')
    <div class="container bg-white shadow-sm rounded p-4">
        <h4>🔍 Stock Adjustment Details</h4>
        <div class="card mb-4">
            <div class="card-body">
                <p><strong>Adjustment ID:</strong> {{ $adjustment->AdjustmentId }}</p>
                <p><strong>Adjustment Date:</strong> {{ Carbon::parse($adjustment->AdjustmentDate)->format('Y-m-d') }}</p>
                <p><strong>Branch:</strong> {{ optional($adjustment->branch)->Name ?? 'N/A' }}</p>
                <p><strong>Adjusted By:</strong> {{ $adjustment->adjustedBy->Name ?? 'N/A' }}</p>
                <p><strong>Status:</strong>
                    @php
                        $statusEnum = Transfers::tryFrom($adjustment->Status);
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
                    <th>UOM</th>
                    <th>Unit Cost</th>
                    <th>Current Qty</th>
                    <th>Adjustment Qty</th>
                    <th>New Qty</th>
                    <th>Adjustment Reason</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                @foreach($adjustment->items as $index => $item)
                    @php
                        // Calculate new quantity
                        $currentQty = $item->current_stock_qty ?? 0;
                        $adjustmentQty = $item->AdjustmentQty ?? 0;
                        $newQty = $currentQty + $adjustmentQty;
                        
                        // Find reason description
                        $reasonDescription = 'N/A';
                        if ($item->Reason && $reasons) {
                            $reason = $reasons->firstWhere('ID', $item->Reason);
                            $reasonDescription = $reason ? $reason->Description : 'N/A';
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->ItemCode ?? 'N/A'}}</td>
                        <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                        <td>{{ $item->item->uom->Code ?? 'N/A' }}</td>
                        <td>{{ number_format($item->UnitCost ?? 0, 2) }}</td>
                        <td>{{ $currentQty }}</td>
                        <td>
                            <span class="{{ $adjustmentQty >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $adjustmentQty >= 0 ? '+' : '' }}{{ $adjustmentQty }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $newQty >= 0 ? 'text-primary fw-bold' : 'text-danger fw-bold' }}">
                                {{ $newQty }}
                            </span>
                        </td>
                        <td>{{ $reasonDescription }}</td>
                        <td>{{ $item->Remarks ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <a href="{{ route('transactionsadjustment.index') }}" class="btn btn-secondary mt-3">← Back to List</a>
    </div>
@endsection