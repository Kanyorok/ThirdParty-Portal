@extends('layouts.app')
@section('title', 'Assign Legal Counsel')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ Assign Counsel to: {{ $case->CaseTitle }}</h4>

    <form method="POST" action="{{ route('legal.disputes.counsels.store', $case->ID) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Counsel Name</label>
            <input type="text" name="CounselName" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Law Firm</label>
            <input type="text" name="FirmName" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="Email" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="Phone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Role in Case</label>
            <input type="text" name="Role" class="form-control" placeholder="e.g. Lead Counsel">
        </div>

        <div class="mb-3">
            <label class="form-label">Is External?</label>
            <select name="IsExternal" class="form-select">
                <option value="0">No – Internal</option>
                <option value="1">Yes – External</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.disputes.counsels.index', $case->ID) }}" class="btn btn-secondary">↩️ Cancel</a>
    </form>
</div>
@endsection
