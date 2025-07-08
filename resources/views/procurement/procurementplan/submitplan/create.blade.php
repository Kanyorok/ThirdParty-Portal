@extends('layouts.app')
@section('title', 'Procurement Plan Details')
@section('content')
    <div class="container mt-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>📊 Procurement Plan Dashboard – Annual Procurement Plan {{ $plan->FiscalYear }}</h4>
            <a href="{{ route('Procurement-Plan-Submission.index') }}" class="btn btn-sm btn-outline-secondary">← Back
                to Plans</a>
        </div>

        <!-- Summary Info -->
        <div class="row mb-4 bg-light border rounded p-3">
            <div class="col-md-3"><strong>Plan Ref:</strong> {{ $plan->ReferenceNumber }}</div>
            <div class="col-md-3"><strong>Year:</strong> {{ $plan->FiscalYear }}</div>
            <div class="col-md-3">
                <strong>Status:</strong>
                <span class="badge bg-warning text-dark">{{ $plan->Status->label() }}</span>
            </div>
            <div class="col-md-3"><strong>Created By:</strong> {{ $plan->creator->Name }}</div>
        </div>

        <!-- KPI Cards -->
        @php
            $total = $plan->lineItems->count();
            $budgetLinked = $plan->lineItems->whereNotNull('BudgetLineID')->count();
            $methodAssigned = $plan->lineItems->whereNotNull('ProcurementMethod')->count();
            $scheduled = $plan->lineItems->filter(function ($line) {
            return $line->schedulePlan && $line->schedulePlan->periods->isNotEmpty();
        })->count();
        @endphp
        <div class="row text-center mb-4">
            <div class="col-md-3">
                <div class="border p-3 rounded bg-white"><h6>Total Items</h6><h4>{{ $total }}</h4></div>
            </div>
            <div class="col-md-3">
                <div class="border p-3 rounded bg-white"><h6>Budget Linked</h6><h4
                        class="text-warning">{{ $budgetLinked }} / {{ $total }}</h4></div>
            </div>
            <div class="col-md-3">
                <div class="border p-3 rounded bg-white"><h6>Method Assigned</h6><h4
                        class="text-warning">{{ $methodAssigned }} / {{ $total }}</h4></div>
            </div>
            <div class="col-md-3">
                <div class="border p-3 rounded bg-white"><h6>Scheduled</h6><h4 class="text-warning">{{ $scheduled }}
                        / {{ $total }}</h4></div>
            </div>
        </div>

        <!-- Item List Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Branch</th>
                    <th>Qty</th>
                    <th>Cost</th>
                    <th>Budget Line</th>
                    <th>Method</th>
                    <th>Schedule</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($plan->lineItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->ItemName ?? '—' }}</td>
                        <td>{{ $item->Branch->Name ?? '—' }}</td>
                        <td>{{ $item->MergedQty }}</td>
                        <td>{{ number_format($item->EstimatedUnitCost, 0) }}</td>
                        <td>
                            @if ($item->budgetline->Description)
                                {{ $item->budgetline->Description }}
                            @else
                                <span class="text-danger">Unlinked</span>
                            @endif
                        </td>
                        <td>{{ $item->setMethod->Name ?? '—' }}</td>
                        <td>
                            @if ($item->schedulePlan && $item->schedulePlan->periods->isNotEmpty())
                                @foreach ($item->schedulePlan->periods as $period)
                                    {{ $period->SchedulePeriod }}: {{ $period->ScheduleQTY }}@if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            @else
                                <span class="text-danger">Not Scheduled</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $mergedQty = (int) $item->MergedQty;
                                $scheduledQty = (int) $item->schedulePlan?->periods->sum('ScheduleQTY');
                            @endphp

                            @if ($scheduledQty === 0)
                                <span class="badge bg-danger">Not Scheduled</span>
                            @elseif ($scheduledQty < $mergedQty)
                                <span class="badge bg-warning text-dark">Partially Scheduled</span>
                            @elseif ($scheduledQty === $mergedQty)
                                <span class="badge bg-success">Completed</span>
                            @else
                                <span class="badge bg-secondary">Overscheduled</span>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No line items found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Submit for Approval Form -->
        <div class="mt-4 p-4 bg-light border rounded">
            <h5>📤 Submit Plan for Approval</h5>
            <p>This will forward the plan for multi-level approval once you are confident all details are correctly
                filled.</p>
            <form action="{{ route('Procurement-Plan-Submission.update', $plan->PlanID) }}" method="POST"
                  class="d-inline">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-success">Submit</button>
            </form>
        </div>
    </div>
@endsection
