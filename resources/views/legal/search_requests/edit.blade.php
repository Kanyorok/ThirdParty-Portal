@extends('layouts.app')
@section('title', 'Edit Legal Search Request')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4>✏️ Edit Legal Search Request</h4>

    <form method="POST" action="{{ route('legal.search_requests.update', $request->ID) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Request Type</label>
            <select name="RequestType" class="form-select" required>
                <option value="Company" {{ $request->RequestType === 'Company' ? 'selected' : '' }}>🏛️ Company / Business</option>
                <option value="Individual" {{ $request->RequestType === 'Individual' ? 'selected' : '' }}>👥 Individual / Group</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Entity Name</label>
            <input type="text" name="EntityName" class="form-control" value="{{ $request->EntityName }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Request Purpose</label>
            <textarea name="Purpose" rows="3" class="form-control">{{ $request->Purpose }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">💾 Update</button>
    </form>
</div>
@endsection
