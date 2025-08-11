@extends('layouts.app')
@section('title', 'Edit Counsel')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Counsel: {{ $counsel->CounselName }}</h4>

    <form method="POST" action="{{ route('legal.disputes.counsels.update', [$case->ID, $counsel->ID]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Counsel Name</label>
            <input type="text" name="CounselName" value="{{ $counsel->CounselName }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Law Firm</label>
            <input type="text" name="FirmName" value="{{ $counsel->FirmName }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="Email" value="{{ $counsel->Email }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="Phone" value="{{ $counsel->Phone }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" name="Role" value="{{ $counsel->Role }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Is External?</label>
            <select name="IsExternal" class="form-select">
                <option value="0" {{ !$counsel->IsExternal ? 'selected' : '' }}>No – Internal</option>
                <option value="1" {{ $counsel->IsExternal ? 'selected' : '' }}>Yes – External</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control">{{ $counsel->Remarks }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">💾 Update</button>
        <a href="{{ route('legal.disputes.counsels.index', $case->ID) }}" class="btn btn-secondary">↩️ Back</a>
    </form>
</div>
@endsection
