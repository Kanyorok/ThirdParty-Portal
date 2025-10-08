@extends('layouts.app')
@section('title', 'Budget Entry Monthly Allocations')
@section('content')

    <div class="card mt-4 shadow-sm rounded-3">
        {{--        <div class="card-header bg-light text-white fw-semibold">--}}
        {{--            Budget Entry Overview (By Lines)--}}
        {{--        </div>--}}
        <div class="card-body">
            @if($entries->isEmpty())
                <div class="alert alert-warning">No budget entries found for this BudgetID.</div>
            @else
                <div class="mb-4">
                    @php
                        $totalAllocation = $entries->flatMap(fn($entry) => $entry->allocations ?? collect())->sum('Allocation');
                        $budgetName = optional($entries->first()->budget)->Name ?? '';
                    @endphp
                    <p><strong>Budget:</strong> {{ $budgetName }}</p>
                    <p><strong>Total Allocations:</strong> {{ number_format($totalAllocation, 2) }}</p>
{{--                    <p><strong>Source:</strong> Manual Entry</p>--}}
                </div>

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-hover text-center align-middle mb-0">
                        <thead class="table-light sticky-top">
                        <tr>
                            <th rowspan="2">#</th>
                            <th rowspan="2">Branch</th>
                            <th rowspan="2">Budget Line</th>
                            <th rowspan="2">Amount</th>
                            <th colspan="12">Allocations by Month</th>
                        </tr>
                        <tr>
                            @for($month = 1; $month <= 12; $month++)
                                <th>{{ DateTime::createFromFormat('!m', $month)->format('M') }}</th>
                            @endfor
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($entries as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->branch->Name ?? '-' }}</td>
                                <td>{{ $item->budgetLine->LineName ?? '-' }}</td>
                                <td>{{ number_format($item->Amount, 2) }}</td>
                                @for($month = 1; $month <= 12; $month++)
                                    @php
                                        $allocation = $item->allocations->firstWhere('Month', str_pad($month, 2, '0', STR_PAD_LEFT));
                                    @endphp
                                    <td>{{ $allocation ? number_format($allocation->Allocation, 2) : '-' }}</td>
                                @endfor
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
