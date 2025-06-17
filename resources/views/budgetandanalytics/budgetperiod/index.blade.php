@extends('layouts.app')
@section('title', 'Budget Overview')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>There were some errors with your submission:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('budgetperiod.create') }}" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                + New Budget</a>
        </div>
        <h5>📋 Budget List</h5>
        
        <p class="text-muted">
            Create and manage budgets linked to defined budget periods for effective financial planning and tracking.
        </p>

        @if ($periods->count())
        <table class="table table-bordered text-center">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Budget Name</th>
                <th>Fiscal Year</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($periods as $period)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$period->fiscalYear ?? '-'}}</td>
                <td>{{$period->periodType ?? '-'}}</td>
                <td>{{$period->periodType ?? '-'}}</td>
                <td>{{$period->notes ?? '-'}}</td>
                <td><span class="badge bg-success">Open</span></td>
                <td>{{$period->notes ?? '-'}}</td>
                <td>
                    <div class="d-flex gap-2">
                    <a href="{{route('budgetperiod.edit', $period->Id)}}" class="btn btn-sm btn-info">✏️ Edit</a>
                    
                    <form action="{{route('budgetperiod.destroy', $period->Id)}}" method="POST" style="display: inline-flexbox" onsubmit="return confirm('Are you sure you want to delete this Period?');">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @else
        <div class="alert alert-info">
            No Saved Periods
        </div>
        @endif
    </div>




<!-- Add Driver Modal -->
<div class="modal fade" id="addBudgetModal" tabindex="-1" aria-labelledby="addSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">📈 New Budget Setup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="modal-body">
                <form method="post" action="{{ route('budgetperiod.store') }}">
                 @csrf
                 @method('POST')
                 <div class="card p-4">
                    {{-- <h5>🗓️ New Budget</h5> --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="Name" class="form-label">Budget Name</label>
                            <input type="text" class="form-control" id="Name" name="Name" placeholder="Enter the Budget Name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="FiscalYear" class="form-label">Fiscal Year</label>
                            <input type="number" class="form-control" id="FiscalYear" min="2020" name="FiscalYear" placeholder="e.g., 2025">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="Name" class="form-label">From (Date)</label>
                            <input type="date" class="form-control" id="From" name="From" placeholder="From Date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="FiscalYear" class="form-label">To (Date)</label>
                            <input type="date" class="form-control" id="To" name="To" placeholder="To date">
                        </div>
                    </div>
                    {{-- <div class="mb-3">
                        <label for="periodType" class="form-label">Periods</label>
                        <select class="form-select" id="periodType" name="periodType">
                        <option disabled selected>Select Period Type</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->PeriodType }}">{{ $type->PeriodType }}</option>                
                        @endforeach
                        </select>
                    </div> --}}
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="Notes" name="Notes" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Creating...'; this.form.submit(); }">
                        💾 Create
                    </button>
                    </div>
                    </div>
                </form>
        </div>
    </div>
</div>
@endsection
