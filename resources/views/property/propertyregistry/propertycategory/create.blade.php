@extends('layouts.app')
@section('title', 'Add Property Category')
@section('content')
<div class="container mt-4">
  <form action="{{ route('propertycategory.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card shadow">
      <div class="card-header bg-light fw-bold">Property Category Details</div>
      <div class="card-body">

        {{-- Show global errors (like "category already exists") --}}
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="PropertyCategoryName" class="form-label">
              Category Name <span class="text-danger">*</span>
            </label>
            <input 
              type="text" 
              name="Name" 
              class="form-control @error('Name') is-invalid @enderror"
              value="{{ old('Name') }}"
              required
            >
            @error('Name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="Description" class="form-label">
              Description <span class="text-danger">*</span>
            </label>
            <textarea 
              name="Description" 
              class="form-control @error('Description') is-invalid @enderror" 
              rows="3"
            >{{ old('Description') }}</textarea>
            @error('Description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <button 
          type="submit" 
          class="btn btn-success"
          onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"
        >
          Add Category
        </button>
        <a href="{{ route('propertycategory.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
</div>
@endsection
