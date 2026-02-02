@extends('layouts.app')
@section('title', 'Add Property Attachment')

@section('content')
<div class="container mt-4" style="max-width: 900px;">

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-sm border-0">

            {{-- Header --}}
            <div class="card-header bg-primary">
                <h6 class="mb-0 text-white">
                    Upload Property Document
                </h6>
            </div>

            {{-- Body --}}
            <div class="card-body px-4 pt-3">

                {{-- Property & Title --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label ">
                            Select Property <span class="text-danger">*</span>
                        </label>
                        <select name="PropertyID" class="form-select form-select-sm" required>
                            <option value="">-- Select Property --</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->Id }}">
                                    {{ $property->PropertyName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label ">
                            Document Title <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control form-control-sm"
                               name="DocumentTitle"
                               placeholder="e.g. Title Deed, Blueprint"
                               required>
                    </div>
                </div>

                {{-- Type & File --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label ">
                            Document Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm" name="DocumentType" required>
                            <option value="">-- Select Document Type --</option>
                            @foreach ($documenttypes as $documenttype)
                                <option value="{{ $documenttype->ID }}">
                                    {{ $documenttype->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label ">
                            Upload File <span class="text-danger">*</span>
                        </label>
                        <small class="text-muted d-block mb-1">
                            Allowed: PDF, JPG, PNG, DOCX, XLSX (Max 25MB)
                        </small>
                        <input type="file"
                               name="file[]"
                               class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                               required>
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label class="form-label ">Description / Notes</label>
                    <textarea class="form-control form-control-sm"
                              rows="3"
                              name="Description"
                              placeholder="Optional notes or context..."></textarea>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('attachments.index') }}" class="btn btn-outline-secondary btn-sm px-4">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-success btn-sm px-4"
                            onclick="this.disabled=true; this.innerText='Uploading...'; this.form.submit();">
                        Upload Document
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
