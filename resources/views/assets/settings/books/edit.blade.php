@extends('layouts.app')
@section('title','Edit Asset Book')
@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Edit Book</h6></div>
    <div class="card-body p-3">
      <form method="post" action="{{ route('assets.settings.books.update',$book->Id) }}">
        @csrf @method('PUT')
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Code</label>
            <input name="Code" value="{{ old('Code',$book->Code) }}" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input name="Name" value="{{ old('Name',$book->Name) }}" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="IsActive" id="isactive" {{ $book->IsActive ? 'checked' : '' }}>
              <label for="isactive" class="form-check-label">Active</label>
            </div>
          </div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.settings.books.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
