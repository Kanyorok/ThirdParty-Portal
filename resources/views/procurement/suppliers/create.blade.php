@extends('layouts.app')
@section('title', 'Create Supplier')
@section('content')
<div class="container">
    <h3>Create Supplier</h3>

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> Please fix the following issues:<br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label for="SupplierName">Supplier Name <span class="text-danger">*</span></label>
            <input type="text" name="SupplierName" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label for="ContactEmail">Contact Email <span class="text-danger">*</span></label>
            <input type="email" name="ContactEmail" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label for="ContactPhone">Contact Phone</label>
            <input type="text" name="ContactPhone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="CategoryId" class="form-control">
                <option value="">-- None --</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('CategoryId', $item->CategoryId ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->Name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="Address">Address</label>
            <input type="text" name="Address" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label for="IsPrequalified">Prequalification Status</label>
            <select name="IsPrequalified" class="form-control">
                <option value="1">Prequalified</option>
                <option value="0">Not Prequalified</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Create Supplier</button>
    </form>
</div>
@endsection