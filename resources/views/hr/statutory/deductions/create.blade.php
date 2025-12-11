@extends('layouts.app')

@section('title', 'New Deduction')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Deduction</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.deductions.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.statutory.deductions.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsMandatory" value="1" id="IsMandatory" @checked(old('IsMandatory'))>
                            <label for="IsMandatory" class="form-check-label">Mandatory for all staff</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="ShowInPayslip" value="1" id="ShowInPayslip" @checked(old('ShowInPayslip', true))>
                            <label for="ShowInPayslip" class="form-check-label">Show in Payslip</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Applicable For</label>
                        <select name="ApplyFor" class="form-select">
                            <option value="">All</option>
                            @foreach(['Regular','Contract','Intern'] as $type)
                                <option value="{{ $type }}" @selected(old('ApplyFor')==$type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
