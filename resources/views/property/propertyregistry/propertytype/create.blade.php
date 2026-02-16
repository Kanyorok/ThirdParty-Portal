@extends('layouts.app')
@section('title', 'Add Property Type')

@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="container mt-4">
<form action="{{ route('propertytype.store') }}" method="POST">
@csrf

<div class="card shadow">
<div class="card-header bg-primary fw-bold">Property Details</div>

<div class="card-body">

<div class="row g-3 mb-3">

<div class="col-md-6">
<label class="form-label">Type Name <span class="text-danger">*</span></label>
<input type="text"
       name="PropertyTypeName"
       class="form-control"
       required
       value="{{ old('PropertyTypeName') }}">
</div>

<div class="col-md-6">
<label class="form-label">Category <span class="text-danger">*</span></label>
<select name="PropertyCategoryId" class="form-select" required>
<option value="">-- Select Category --</option>

@foreach ($categories as $category)
<option value="{{ $category->Id }}"
    {{ old('PropertyCategoryId') == $category->Id ? 'selected' : '' }}>
    {{ $category->Name }}
</option>
@endforeach

</select>
</div>

</div>

<div class="row g-3 mb-3">
<div class="col-md-6">
<label class="form-label">Description</label>
<textarea name="Description" class="form-control" rows="3">{{ old('Description') }}</textarea>
</div>
</div>

<div class="d-flex gap-2">
<button type="submit" class="btn btn-success"
onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
Add Type
</button>

<a href="{{ route('propertytype.index') }}" class="btn btn-secondary">Cancel</a>
</div>

</div>
</div>
</form>
</div>

@endsection
