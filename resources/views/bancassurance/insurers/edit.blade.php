@extends('layouts.app')
@section('title', 'Edit Insurance Provider')

@section('content')
<div class="container mt-4">
    <h4>✏️ Edit Provider – {{ $provider->Name }}</h4>

    <form method="POST" action="{{ route('bancassurance.insurers.update', $provider->Id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Provider Name</label>
            <input type="text" name="Name" class="form-control" value="{{ $provider->Name }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Country</label>
            <input type="text" name="Country" class="form-control" value="{{ $provider->Country }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Contact Person</label>
            <input type="text" name="ContactPerson" class="form-control" value="{{ $provider->ContactPerson }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="Email" class="form-control" value="{{ $provider->Email }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="Phone" class="form-control" value="{{ $provider->Phone }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="IsActive" class="form-select">
                <option value="1" {{ $provider->IsActive ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$provider->IsActive ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">💾 Update Provider</button>
        </div>
    </form>
</div>
@endsection
