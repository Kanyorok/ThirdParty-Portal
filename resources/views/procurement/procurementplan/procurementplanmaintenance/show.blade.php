@extends('layouts.app')
@section('title', 'View Procurement Plan')

@section('content')
    <div class="container mt-4">
        <h3>Plan Details</h3>

        <div class="mb-3">
            <strong>Title:</strong> {{ $plan->Title }}<br>
            <strong>Reference Number:</strong> {{ $plan->ReferenceNumber }}<br>
            <strong>Fiscal Year:</strong> {{ $plan->FiscalYear }}<br>
            <strong>Status:</strong> {{ $plan->Status->label() }}<br>
            <strong>Created By:</strong> {{ $plan->createdBy->Name ?? 'N/A' }}<br>
            <strong>Created On:</strong> {{ \Carbon\Carbon::parse($plan->CreatedDate)->format('Y-m-d') ?? 'N/A' }}<br>
        </div>

        <h5>Line Items</h5>
        @if($plan->lineItems->isEmpty())
            <p>No line items found for this plan.</p>
        @else
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Estimated Unit Cost (KES)</th>
                    <th>Estimated Total Cost (KES)</th>
                </tr>
                </thead>
                <tbody>
                @foreach($plan->lineItems as $item)
                    <tr>
                        <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                        <td>{{ $item->MergedQty }}</td>
                        <td>{{ number_format($item->EstimatedUnitCost, 2) }}</td>
                        <td>{{ number_format($item->MergedQty * $item->EstimatedUnitCost, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <a href="{{ route('procurementplanmaintain.index') }}" class="btn btn-secondary mt-3">Back to List</a>
    </div>
@endsection
