@extends('layouts.app')
@section('title', 'Add Regulatory Body')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Add Regulatory Body</h4>

    <form method="POST" action="{{ route('legal.setup.regulatory_bodies.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name *</label>
            <input type="text" name="Name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Jurisdiction</label>
            <input type="text" name="Jurisdiction" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Person</label>
            <input type="text" name="ContactPerson" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Email</label>
            <input type="email" name="ContactEmail" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Phone</label>
            <input type="text" name="ContactPhone" class="form-control">
        </div>
        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.setup.regulatory_bodies.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
