@extends('layouts.app')

@section('title', 'New Allowance')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Allowance</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.allowances.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.statutory.allowances.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="IsTaxable" value="1" id="IsTaxable" @checked(old('IsTaxable', true))>
                            <label for="IsTaxable" class="form-check-label">Taxable</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="IsMandatory" value="1" id="IsMandatory" @checked(old('IsMandatory', false))>
                            <label for="IsMandatory" class="form-check-label">Mandatory (auto-load)</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Applicable Job Grades</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($grades as $grade)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="Grades[]" value="{{ $grade->Id }}" id="grade_{{ $grade->Id }}" @checked(collect(old('Grades', []))->contains($grade->Id))>
                                    <label class="form-check-label" for="grade_{{ $grade->Id }}">{{ $grade->Name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">If none selected, allowance is available to all grades.</div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Allowance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
