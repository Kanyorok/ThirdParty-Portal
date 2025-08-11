@extends('layouts.app')
@section('title', 'New Legal Search Request')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4>➕ New Legal Search Request</h4>

    <form method="POST" action="{{ route('legal.search_requests.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Request Type</label>
            <select name="RequestType" class="form-select" required>
                <option value="">-- Select --</option>
                <option value="Company">🏛️ Company / Business</option>
                <option value="Individual">👥 Individual / Group</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Entity Name</label>
            <input type="text" name="EntityName" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Request Purpose</label>
            <textarea name="Purpose" rows="3" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">💾 Submit Request</button>
    </form>
</div>
@endsection
