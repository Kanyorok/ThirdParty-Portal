@extends('layouts.app')
@section('title', 'Budget Period Setup')
@section('content')

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="post" action="{{ route('budgetperiod.store') }}">
        @csrf
      <div class="card p-4">
        <h5>🗓️ Budget Period Setup</h5>
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
                <option value="{{ $type->Id }}">{{ $type->PeriodType }}</option>                
            @endforeach
            </select>
        </div> --}}
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="Notes" rows="3"></textarea>
        </div>
        
        <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"
            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
            💾 Save
        </button>
        </div>
        </div>
    </form>
@endsection
