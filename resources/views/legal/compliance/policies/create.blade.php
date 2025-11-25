@extends('layouts.app')
@section('title','Add Policy')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Add Policy</h4>
    <form method="POST" action="{{ route('legal.compliance.policies.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="Title" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Category</label>
                <select name="CategoryID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($categories as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Compliance Area</label>
                <select name="ComplianceAreaID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($areas as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Effective Date</label>
                <input type="date" name="EffectiveDate" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Version</label>
                <input type="text" name="Version" class="form-control">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">Policy Document</label>
                <input type="file" name="File" class="form-control">
            </div>
        </div>
        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.compliance.policies.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
