@extends('layouts.app')
@section('title', 'Upload New Document')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">➕ Upload New Document</h4>

        <form method="POST" enctype="multipart/form-data" action="#">
            @csrf
            <div class="card mb-4">
                <div class="card-header bg-light">📄 Document Metadata</div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" placeholder="e.g. Staff Appraisal Template">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Document Type</label>
                        <select class="form-select">
                            <option>Invoice</option>
                            <option>Contract</option>
                            <option>Policy</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3"
                                  placeholder="Short description of document content"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tags (comma-separated)</label>
                        <input type="text" class="form-control" placeholder="e.g. finance, supplier, urgent">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select class="form-select">
                            <option>Legal</option>
                            <option>HR</option>
                            <option>Finance</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">📁 Upload File</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Choose File</label>
                        <input type="file" class="form-control" name="document_file">
                        <small class="form-text text-muted">Allowed formats: PDF, DOCX, JPG, PNG. Max size: 20MB</small>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <a href="{{ route('drepositorymanagement.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary">Upload Document</button>
            </div>
        </form>
</div>
@endsection
