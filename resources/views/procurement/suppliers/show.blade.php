@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"> Supplier Details</h3>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Supplier Name:</label>
                <div>{{ $supplier->SupplierName }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Prequalified:</label>
                <div>
                    @if($supplier->IsPrequalified)
                        <span class="badge bg-success">Yes</span>
                    @else
                        <span class="badge bg-warning text-dark">No</span>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Category:</label>
                <div>{{ $supplier->category->Name ?? '-' }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Contact Email:</label>
                <div>{{ $supplier->ContactEmail ?? '-' }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Contact Phone:</label>
                <div>{{ $supplier->ContactPhone ?? '-' }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Address:</label>
                <div>{{ $supplier->Address ?? '-' }}</div>
            </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between">
            <a href="{{ route('suppliers.edit', $supplier->Id) }}" class="btn btn-warning">✏️ Edit</a>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
