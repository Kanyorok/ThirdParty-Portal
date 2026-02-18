@extends('layouts.app')

@section('title', 'Generate Payroll')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Generate Payroll</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.runs.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if($cycles->isEmpty())
                <div class="alert alert-warning">
                    No open payroll cycles available. Open or reopen a cycle before generating payroll.
                </div>
            @endif
            <form method="POST" action="{{ route('hr.payroll.runs.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Payroll Cycle *</label>
                        <select name="PayrollCycleID" class="form-select" required @disabled($cycles->isEmpty())>
                            <option value="">{{ $cycles->isEmpty() ? 'No open cycles' : 'Select cycle' }}</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->Id }}" @selected(old('PayrollCycleID') == $cycle->Id)>
                                    {{ $cycle->Month }}/{{ $cycle->Year }} ({{ $cycle->Status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Notes</label>
                        <input type="text" name="Notes" class="form-control" value="{{ old('Notes') }}" placeholder="Optional note">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" @disabled($cycles->isEmpty())>Generate Payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
