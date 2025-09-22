@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
    <div class="container py-4">
        <h3 class="fw-bold mb-4">Add New Supplier</h3>

    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf

        @include('procurement.suppliers.partials.form', ['supplier' => null])

        <button type="submit" class="btn btn-primary">Create Supplier</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
