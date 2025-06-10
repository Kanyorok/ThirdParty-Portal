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
            <div class="mb-3">
                <label for="fiscalYear" class="form-label">Fiscal Year</label>
                <input type="number" class="form-control" id="fiscalYear" name="fiscalYear" placeholder="e.g., 2025">
            </div>
            <div class="mb-3">
                <label for="periodType" class="form-label">Periods</label>
                <select class="form-select" id="periodType" name="periodType">
                    <option disabled selected>Select Period Type</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->PeriodType }}">{{ $type->PeriodType }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">💾 Save
                </button>
                <a href="{{ route('budgetperiod.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
@endsection
