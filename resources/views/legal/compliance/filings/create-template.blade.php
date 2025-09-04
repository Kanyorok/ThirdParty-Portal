@extends('layouts.app')
@section('title','Add Filing Template')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Add Filing Template</h4>
    <form method="POST" action="{{ route('legal.compliance.filings.templates.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="Name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Filing Type *</label>
                <select name="FilingTypeID" class="form-select" required>
                    @foreach($types as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Regulator *</label>
                <select name="RegulatorID" class="form-select" required>
                    @foreach($regulators as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Format</label>
                <select name="FormatID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($formats as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Frequency</label>
                <input type="text" name="Frequency" class="form-control" placeholder="Monthly/Quarterly/Annually">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Due Day</label>
                <input type="text" name="DueDay" class="form-control" placeholder="e.g. 15th of every month">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control"></textarea>
            </div>
            <div class="col-12 mb-3">
    <label class="form-label">Portal URL</label>
    <input type="url" name="PortalURL" class="form-control" placeholder="https://portal.regulator.go.ke">
</div>
        </div>
        <button class="btn btn-success">💾 Save</button>
    </form>
</div>
@endsection
