@extends('layouts.app')
@section('title','Add Certification')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Add Certification</h4>
    <form method="POST" action="{{ route('legal.compliance.certifications.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Staff *</label>
                <select name="UserID" class="form-select" required>
                    @foreach($users as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Certification Name *</label>
                <input type="text" name="CertificationName" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Issue Date</label>
                <input type="date" name="IssueDate" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="ExpiryDate" class="form-control">
            </div>
        </div>
        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.compliance.certifications.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
