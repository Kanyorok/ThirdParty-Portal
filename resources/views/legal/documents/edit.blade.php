@extends('layouts.app')
@section('title', 'Edit Legal Document')
@section('content')
    <div class="container">
        <div class="card shadow rounded-4 border-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">
                    <i class="fas fa-edit me-2"></i> Edit Legal Document
                </h5>
                <a href="{{ route('legal.documents.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body px-4 py-4">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('legal.documents.update', $doc->Id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="DocumentTitle" class="form-label">Title</label>
                            <input type="text" name="DocumentTitle" id="DocumentTitle"
                                   class="form-control @error('DocumentTitle') is-invalid @enderror"
                                   value="{{ old('DocumentTitle', $doc->DocumentTitle) }}" required>
                            @error('DocumentTitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="DocumentType" class="form-label">Document Type</label>
                            <select name="DocumentType" id="DocumentType"
                                    class="form-select @error('DocumentType') is-invalid @enderror" required>
                                @foreach (['Contract','Lease','NDA','MOU'] as $type)
                                    <option value="{{ $type }}" @selected(old('DocumentType',$doc->DocumentType) === $type)>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('DocumentType')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="SourceModule" class="form-label">Source Module</label>
                            <select name="SourceModule" id="SourceModule"
                                    class="form-select @error('SourceModule') is-invalid @enderror">
                                @foreach (['Legal','Procurement','Property','HR','Insurance'] as $mod)
                                    <option value="{{ $mod }}" @selected(old('SourceModule',$doc->SourceModule) === $mod)>
                                        {{ $mod }}
                                    </option>
                                @endforeach
                            </select>
                            @error('SourceModule')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="LinkedDMSDocID" class="form-label">Replace File (optional)</label>
                            <input type="file" name="LinkedDMSDocID" id="LinkedDMSDocID"
                                   class="form-control @error('LinkedDMSDocID') is-invalid @enderror"
                                   accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx">
                            <small class="text-muted">Leave blank to keep current file. Current:
                                @if($doc->LinkedDMSDocID)
                                    <span class="badge bg-primary">{{ $doc->LinkedDMSDocID }}</span>
                                @else
                                    none
                                @endif
                            </small>
                            @error('LinkedDMSDocID')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Remarks" class="form-label">Remarks</label>
                        <textarea name="Remarks" id="Remarks"
                                  class="form-control @error('Remarks') is-invalid @enderror"
                                  rows="3">{{ old('Remarks', $doc->Remarks) }}</textarea>
                        @error('Remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Optional: let admins update statuses --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ReviewStatus" class="form-label">Review Status</label>
                            <select name="ReviewStatus" id="ReviewStatus" class="form-select">
                                @foreach (['Draft','In Review','Approved','Rejected'] as $r)
                                    <option value="{{ $r }}" @selected(old('ReviewStatus',$doc->ReviewStatus) === $r)>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ExecutionStatus" class="form-label">Execution Status</label>
                            <select name="ExecutionStatus" id="ExecutionStatus" class="form-select">
                                @foreach (['Pending','Signed','Archived'] as $e)
                                    <option value="{{ $e }}" @selected(old('ExecutionStatus',$doc->ExecutionStatus) === $e)>{{ $e }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Update Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
