@extends('layouts.app')
@section('title', 'Budget Entry Monthly Allocations')
@section('content')
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">📅 Monthly Allocations for Entry</div>
        <div class="card-body">
            <a href="{{ route('entrybyglline.index') }}" class="btn btn-secondary mb-3">&larr; Back to List</a>
            <div class="mb-3">
                <strong>Budget:</strong> {{ $entry->budget->Name ?? '-' }}<br>
                <strong>Branch:</strong> {{ $entry->branch->Name ?? '-' }}<br>
                <strong>Budget Line:</strong> {{ $entry->budgetLine->LineName ?? '-' }}<br>
                <strong>Amount:</strong> {{ number_format($entry->Amount, 2) }}
            </div>
            <div class="table-responsive">
                @if($monthlyAllocations->count())
                    <table class="table table-bordered table-striped text-center">
                        <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Allocation Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($monthlyAllocations as $allocation)
                            <tr>
                                <td>Month {{ $allocation->Month }}</td>
                                <td>{{ number_format($allocation->Allocation, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info text-center">
                        <strong>No monthly allocations found for this entry.</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
