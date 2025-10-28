@extends('layouts.app')
@section('title', 'Edit Regulatory Body')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-3">✏️ Edit Regulatory Body</h4>

        <form method="POST" action="{{ route('legal.setup.regulatory_bodies.update', $body->Id) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="Name" value="{{ $body->Name }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Jurisdiction</label>
                <input type="text" name="Jurisdiction" value="{{ $body->Jurisdiction }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" name="ContactPerson" value="{{ $body->ContactPerson }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Email</label>
                <input type="email" name="ContactEmail" value="{{ $body->ContactEmail }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="ContactPhone" value="{{ $body->ContactPhone }}" class="form-control">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="IsActive" class="form-check-input"
                       value="1" {{ $body->IsActive ? 'checked' : '' }}>
                <label class="form-check-label">Active</label>
            </div>
            <button class="btn btn-success">💾 Update</button>
            <a href="{{ route('legal.setup.regulatory_bodies.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
