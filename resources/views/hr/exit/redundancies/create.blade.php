@extends('layouts.app')

@section('title', 'New Redundancy')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Redundancy</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.exit.redundancies.index') }}">Back</a>
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
            <form action="{{ route('hr.exit.redundancies.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Reason</label>
                        <input type="text" name="Reason" class="form-control" value="{{ old('Reason') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Selection Method</label>
                        <input type="text" name="SelectionMethod" class="form-control" value="{{ old('SelectionMethod') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Criteria</label>
                        <textarea name="Criteria" class="form-control" rows="3">{{ old('Criteria') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="UnionNotified" value="1" id="unionNotified" @checked(old('UnionNotified'))>
                            <label class="form-check-label" for="unionNotified">Union notified</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Union Notified On</label>
                        <input type="date" name="UnionNotifiedOn" class="form-control" value="{{ old('UnionNotifiedOn') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="LabourOfficeNotified" value="1" id="labourNotified" @checked(old('LabourOfficeNotified'))>
                            <label class="form-check-label" for="labourNotified">Labour office notified</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Labour Office Notified On</label>
                        <input type="date" name="LabourOfficeNotifiedOn" class="form-control" value="{{ old('LabourOfficeNotifiedOn') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="Notes" class="form-control" rows="3">{{ old('Notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.exit.redundancies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
