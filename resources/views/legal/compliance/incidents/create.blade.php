@extends('layouts.app')
@section('title','Log Incident')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Log New Incident</h4>
    <form method="POST" action="{{ route('legal.compliance.incidents.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="Title" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Incident Date *</label>
                <input type="date" name="IncidentDate" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Obligation</label>
                <select name="ObligationID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($obligations as $id=>$title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Severity *</label>
                <select name="SeverityID" class="form-select" required>
                    @foreach($severities as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Responsible User</label>
                <select name="ResponsibleUserID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($owners as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control"></textarea>
            </div>
        </div>
        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.compliance.incidents.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
