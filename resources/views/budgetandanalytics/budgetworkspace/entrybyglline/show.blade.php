@extends('layouts.app')
@section('title', 'Budget Entry Monthly Allocations')
@section('content')

<div class="card mt-4">
    <div class="card-header bg-dark text-white">Entry by Lines OverView</div>
    <div class="card-body">
        @if($entries->isEmpty())
            <div class="alert alert-warning">No budget entries found for this BudgetID.</div>
        @else
        <p class="text-muted mb-3">
            This page displays budget entries grouped by budget lines, branches, and amounts.
        </p>
        <div class="mb-3">
            @php    
                $totalAllocation = $entries->flatMap(function($entry) { return $entry->allocations ?? collect(); })->sum('Allocation');
                $budgetName = $entries->first() && isset($entries->first()->budget) ? $entries->first()->budget->Name : '';
            @endphp
            <strong>Budget:</strong> {{ $budgetName }}<br>
            <strong>Total Allocations:</strong> {{ number_format($totalAllocation, 2) }}<br>        
            <strong>Source:</strong> Manual Entry<br>
        </div>
        <table class="table table-bordered table-striped text-center" style="min-width: 700px;">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Branch</th>
                    <th>Budget Line</th>
                    <th>Amount</th>
                    <th colspan="12">Allocations by Month</th>
                </tr>
                <tr>
                    <th colspan="4"></th>
                    @for($month = 1; $month <= 12; $month++)
                        <th>Month {{ $month }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->branch->Name ?? '-' }}</td>
                    <td>{{ $item->budgetLine->LineName ?? '-' }}</td>
                    <td>{{ $item->Amount }}</td>
                    @for($month = 1; $month <= 12; $month++)
                        @php
                            $allocation = $item->allocations->where('Month', str_pad($month, 2, '0', STR_PAD_LEFT))->first();
                        @endphp
                        <td>{{ $allocation ? $allocation->Allocation : '-' }}</td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@endsection
