@extends('layouts.app')
@section('title', 'Add Legal Document')
@section('content')
<div class="card p-2 shadow rounded-4 border-0">
    {{-- <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-plus me-1"></i> Add New Legal Document</h5>
    </div> --}}
    <div class="card-body px-4 py-4">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        <form method="POST" action="{{ route('legal.documents.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
                <div class=" col-md-6">
                    <label for="DocumentTitle" class="form-label">Title</label>
                    <input type="text" name="DocumentTitle" class="form-control" required>
                </div>
                <div class=" col-md-6">
                    <label for="DocumentType" class="form-label">Document Type</label>
                    <select name="DocumentType" class="form-control" required>
                        <option>Contract</option>
                        <option>Lease</option>
                        <option>NDA</option>
                        <option>MOU</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="SourceModule" class="form-label">Source Module</label>
                    <select name="SourceModule" class="form-control">
                        <option value="Legal">Legal</option>
                        <option value="Procurement">Procurement</option>
                        <option value="Property">Property</option>
                        <option value="HR">HR</option>
                        <option value="Insurance">Insurance</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="LinkedDMSDocID" class="form-label">Upload Doc</label>
                    <input required type="file" name="LinkedDMSDocID" class="form-control"  accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,application/pdf,image/jpeg,image/jpg,image/png,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                    <small class="form-text text-muted">Accepted formats: PDF, JPG, JPEG, PNG, DOCX, XLSX</small>
                </div>
            </div>
            <div class="mb-3">
                <label for="Remarks" class="form-label">Remarks</label>
                <textarea name="Remarks" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save Document</button>
        </form>
    </div>
</div>
@endsection
