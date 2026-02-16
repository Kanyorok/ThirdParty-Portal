@extends('layouts.app')
@section('title', 'Edit Property Block')

@section('content')
    <div class="container mt-4" style="max-width: 850px;">

        {{-- Validation Errors --}}
    @if ($errors->any())
            <div class="alert alert-danger shadow-sm">
                <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following issues:
                </h6>
                <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <div class="card shadow border-0 rounded-3">
            <div class="card-header bg-primary fw-bold">
                <i class="bi bi-pencil-square"></i> Block Details
            </div>

            <div class="card-body">
                <form action="{{ route('addblock.update', $block->Id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="PropertyID" class="form-label fw-bold">Property <span class="text-danger">*</span></label>
                            <input type="text" name="PropertyName" id="PropertyName" class="form-control mt-2" value="{{ $block->property->PropertyName }}" disabled>
                            <input type="text" name="PropertyID" id="PropertyID" class="form-control mt-2" value="{{ $block->property->Id }}" hidden>
                        </div>

                        <div class="col-md-6">
                            <label for="BlockName" class="form-label fw-bold">Block Name <span class="text-danger">*</span></label>
                            <input type="text" name="BlockName" id="BlockName" class="form-control mt-2" value="{{ $block->BlockName }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Description" class="form-label fw-bold">Description</label>
                        <textarea name="Description" id="Description"
                                  class="form-control" rows="3"
                                  placeholder="Optional description">{{ old('Description', $block->Description) }}</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <a href="{{ route('addblock.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit"
                                class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">
                            <i class="bi bi-check-circle"></i> Update Block
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
