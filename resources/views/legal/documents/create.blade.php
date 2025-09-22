@extends('layouts.app')
@section('title', 'Add Legal Document')
@section('content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card p-2 shadow rounded-4 border-0">
        {{-- <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-info"><i class="fas fa-plus me-1"></i> Add New Legal Document</h5>
        </div> --}}
        <div class="card-body px-4 py-4">
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
                            @foreach($docTypes as $docType)
                                <option value="{{ $docType->Description }}">{{ $docType->Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="SourceModule" class="form-label">Source Module</label>
                        <select name="SourceModule" class="form-control">
                            @foreach($modules as $module)
                                <option value="{{ $module->ModuleID }}">{{ $module->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="LinkedDMSDocID" class="form-label">Upload Doc</label>
                        <input required type="file" name="LinkedDMSDocID" class="form-control"
                               accept=".pdf,.jpeg,.png,.docx,.xlsx,application/pdf,image/jpeg,image/jpg,image/png,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        <small class="form-text text-muted">Accepted formats: PDF, JPEG, PNG, DOCX, XLSX</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="Remarks" class="form-label">Remarks</label>
                    <textarea name="Remarks" class="form-control" required></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a class="btn btn-secondary" href="{{ route('legal.documents.index') }}">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button
                        type="submit" class="btn btn-info"
                        onclick="if(this.form.checkValidity()){this.disabled = true; this.innerText = 'Saving...'; this.form.submit();}"
                    ><i class="fas fa-save"></i> Save Document
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
