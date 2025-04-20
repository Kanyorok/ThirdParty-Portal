@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
<div class="container">
    <h1>Edit Supplier</h1>
    <form action="{{ route('suppliers.update', $supplier->Id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Supplier Name</label>
            <input type="text" class="form-control" id="SupplierName" name="SupplierName" value="{{ old('SupplierName', $supplier->SupplierName) }}" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Contact Email</label>
            <input type="email" class="form-control" id="ContactEmail" name="ContactEmail" value="{{ old('ContactEmail', $supplier->ContactEmail) }}" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="ContactPhone" name="ContactPhone" value="{{ old('ContactPhone', $supplier->ContactPhone) }}" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="Address" name="Address" value="{{ old('Address', $supplier->Address) }}">
        </div>

        <div class="mb-3">
            <label for="is_prequalified" class="form-label">Prequalified</label>
            <select name="IsPrequalified" id="IsPrequalified" class="form-control">
                <option value="1" {{ $supplier->IsPrequalified ? 'selected' : '' }}>Prequalified</option>
                <option value="0" {{ !$supplier->IsPrequalified ? 'selected' : '' }}>Not Prequalified</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Supplier</button>
    </form>
@endsection