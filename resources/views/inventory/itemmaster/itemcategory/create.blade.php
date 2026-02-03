@extends('layouts.app')

@section('content')

@if($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="container">
    <h2>Add New Category</h2>

    <form action="{{ route('itemcategory.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="Name" class="form-label">Category Name:<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="Name" required>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description:<span class="text-danger">*</span></label>
            <textarea class="form-control" name="Description"></textarea>
        </div>

        <div class="mb-3">
            <label for="ParentId" class="form-label">Parent Category:</label>
            <select class="form-control" name="ParentId">
                <option value="">None (Top-Level Category)</option>
                @foreach($categories as $category)
                <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="ItemTypeId" class="form-label">Item Type (Required for Tenders):</label>
            <select class="form-control" name="ItemTypeId">
                <option value="">Select Item Type</option>
                @foreach($itemTypes as $type)
                <option value="{{ $type->Id }}">{{ $type->type->Description ?? $type->TypeName }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-success"
                    onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save Category
                </button>
                <a href="{{ route('itemcategory.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection