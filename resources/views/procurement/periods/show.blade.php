@extends('layouts.app')
@section('title', 'Procurement Period Details')
@section('content')
<div class="container">
    <h2>{{ $period->Title }}</h2>
    <p><strong>Start Date:</strong> {{ $period->StartDate }}</p>
    <p><strong>End Date:</strong> {{ $period->EndDate }}</p>

    <a href="{{ route('procurement-periods.plans.create', $period->Id) }}" class="btn btn-primary mb-3">+ Add Procurement Plan</a>

    <hr>

    <h4>Linked Suppliers</h4>

    <div class="mb-4">

        @if($period->Suppliers->isEmpty())
        <p class="text-gray-500">No suppliers assigned to this period.</p>
        @else
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach($period->Suppliers as $supplier)
            <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
                {{ $supplier->SupplierName }}
            </span>
            @endforeach
        </div>
        @endif
    </div>

    <hr/>

    <h4> Procurement Plans</h4>

    @if($plans->ProcurementPlans->isEmpty())
    <p class="text-gray-500">No procurement plans available for this period.</p>
    @else
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Procurement Plan</th>
                <th>Unit</th>
                <th>Description</th>
                <th>Estimated Unit Cost</th>
                <th>Quantity</th>
                <th>Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans->ProcurementPlans as $plan)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $plan->item->Name }}</td>
                <td>{{ $plan->item->UOM }}</td>
                <td>{{ $plan->item->Description }}</td>
                <td>{{ number_format($plan->item->UnitPrice, 2) }}</td>
                <td>{{ $plan->Quantity }}</td>
                <td>{{ number_format($plan->TotalCost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</div>
@endsection