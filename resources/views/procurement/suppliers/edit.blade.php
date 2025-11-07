@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold mb-4">Edit Supplier</h3>

    <form action="{{ route('suppliers.update', $supplier->Id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('procurement.suppliers.partials.form', ['supplier' => $supplier])

        <button type="submit" class="btn btn-warning">Update Supplier</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection