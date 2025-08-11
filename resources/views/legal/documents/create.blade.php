@extends('layouts.app')
@section('title', 'Add Legal Document')
@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ Add New Legal Document</h4>
    <form method="POST" action="{{ route('legal.documents.store') }}">
        @csrf
        <div class="mb-3">
            <label for="DocumentTitle" class="form-label">Title</label>
            <input type="text" name="DocumentTitle" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="DocumentType" class="form-label">Document Type</label>
            <select name="DocumentType" class="form-control" required>
                <option>Contract</option>
                <option>Lease</option>
                <option>NDA</option>
                <option>MOU</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="SourceModule" class="form-label">Source Module</label>
            <select name="SourceModule" class="form-control">
                <option value="Legal">Legal</option>
                <option value="Procurement">Procurement</option>
                <option value="Property">Property</option>
                <option value="HR">HR</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="LinkedDMSDocID" class="form-label">DMS Document ID</label>
            <input type="number" name="LinkedDMSDocID" class="form-control">
        </div>
        <div class="mb-3">
            <label for="Remarks" class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save Document</button>
    </form>
</div>
@endsection
