@extends('layouts.app')

@section('title', 'Edit Category')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <h4>✏️ Edit Category: {{ $item->Name }}</h4>

    <form action="{{ route('itemcategory.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="CategoryCode" class="form-label">Item Code</label>
                <input type="text" name="CategoryCode" value="{{ $item->CategoryCode }}" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label for="Name" class="form-label">Category Name</label>
                <input type="text" name="Name" value="{{ $item->Name }}" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="Description" class="form-label">Description</label>
                <input type="text" name="Description" value="{{ $item->Description }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="Status" class="form-label">Status</label>
                <div class="form-check">
                    <input type="hidden" name="Status" value="0"> <!-- Fix checkbox issue -->
                    <input class="form-check-input" type="checkbox" name="Status" id="Status" value="1" {{ $item->Status ? 'checked' : '' }}>
                    <label class="form-check-label" for="Status">Active</label>
                </div>
            </div>
        </div>

        <!-- Auto-assign ModifiedBy -->
        <input type="hidden" name="ModifiedBy" value="{{ auth()->id() }}">

        <button type="submit" class="btn btn-primary">✅ Save Changes</button>
        <a href="{{ route('itemcategory.index') }}" class="btn btn-secondary">🔙 Cancel</a>
    </form>
</div>
@endsection
