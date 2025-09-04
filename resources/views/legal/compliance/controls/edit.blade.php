@extends('layouts.app')
@section('title','Add Control')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Edit Control</h4>
    <form method="POST" action="{{ route('legal.compliance.controls.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="Title" class="form-control" required>
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
            <div class="col-md-6 mb-3">
                <label class="form-label">Control Type</label>
                <select name="ControlTypeID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($types as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Owner</label>
                <select name="OwnerID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($owners as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">Linked Obligations</label>
                <select name="Obligations[]" class="form-select" multiple>
                    @foreach($obligations as $id=>$title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control"></textarea>
            </div>
        </div>
        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.compliance.controls.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
