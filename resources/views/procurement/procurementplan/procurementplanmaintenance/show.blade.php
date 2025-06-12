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

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('procurementplanmaintain.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to List
            </a>

            @if($plan->Status->label() === 'Draft')
                <a href="{{ route('planmanualinput.create', ['plan_id' => $plan->PlanID, 'title' => $plan->Title]) }}"
                   class="btn btn-outline-primary">
                    <i class="fas fa-plus me-1"></i>
                    Add Items Manually
                </a>

                <a href="{{ route('planfromneeds.create', ['plan_id' => $plan->PlanID]) }}"
                   class="btn btn-outline-success">
                    <i class="fas fa-file-import me-1"></i>
                    Generate Items from Needs
                </a>
            @endif
        </div>

    </div>
@endsection
