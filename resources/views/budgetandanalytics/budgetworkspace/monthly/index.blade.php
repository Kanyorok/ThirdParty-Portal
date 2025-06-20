@extends('layouts.app')
 
@section('title', 'Monthly Budget Allocations')
 
@section('content')
<div class="card p-4">
    <h5>📊 Monthly Budget Allocations</h5>
    <p class="text-muted">Summary of budget allocations for each month</p>
 
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
 
    @if($monthlyAllocations->count())
        <div class="alert alert-info">
            <strong>Note:</strong> Monthly allocations are set for the current period.
            You can update them as needed.
        </div>
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Month</th>
                <th>Allocation Amount </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($monthlyAllocations as $allocation)
                <tr>
                    <td>{{ $allocation->Month }}</td>
                    <td>{{ number_format($allocation->Amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        @else
        <div class="alert alert-warning">
            No monthly allocations found. Please add allocations to proceed.
        </div>
        @endif
    </table>
 
    <div class="mt-4 text-end">
        <a href="{{ route('budgetprojections.create') }}" class="btn btn-success">
            ➕ Add New Allocation
        </a>
    </div>
</div>
@endsection
 