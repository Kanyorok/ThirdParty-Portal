@extends('layouts.app')
@section('title', 'Link New Document')

@section('content')
<div class="container">
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <div class="card p-2 shadow rounded-4 mb-0">
        <div class="card-body mb-0">
            <p class="text-muted">
                Fill out the form below to link new document to the case:
                <strong class="text-dark">{{ $case->CaseTitle }}</strong>
            </p>

            <form method="POST" action="{{ route('legal.cases.evidence.store', $case->Id) }}"
                enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Document Title</label>
                    <input type="text" name="EvidenceTitle" value="{{ old('EvidenceTitle') }}" class="form-control"
                        required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Upload Document </label>
                        <input type="file" name="DMSDocumentID" class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,application/pdf,image/jpeg,image/jpg,image/png,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        <small class="form-text text-muted">Accepted formats: PDF, JPG, JPEG, PNG, DOCX, XLSX</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">External File Link (optional)</label>
                        <input type="url" name="ExternalLink" value="{{ old('ExternalLink') }}" class="form-control"
                            placeholder="https://example.com/document.pdf">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="Description" class="form-control" rows="3"
                        required>{{ old('Description') }}</textarea>
                </div>


                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('legal.cases.evidence.index', $case->Id) }}"
                        class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-info"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Save Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection