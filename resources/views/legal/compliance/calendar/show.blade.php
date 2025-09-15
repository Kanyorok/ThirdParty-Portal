@extends('layouts.app')
@section('title', 'Calendar Entry Details')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">📄 Calendar Entry Details</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p><strong>Title:</strong> {{ $entry->Title }}</p>
    <p><strong>Description:</strong> {{ $entry->Description }}</p>
    <p><strong>Obligation:</strong> {{ $entry->obligation->Title ?? '-' }}</p>
    <p><strong>Start Date:</strong> {{ $entry->StartDate }}</p>
    <p><strong>End Date:</strong> {{ $entry->EndDate ?? '-' }}</p>
    <p><strong>Owner:</strong> {{ \DB::table('t_Users')->where('Id', $entry->OwnerID)->value('Name') }}</p>
    <p><strong>Status:</strong> {{ $entry->DeletedOn ? 'Deleted' : 'Active' }}</p>

    <hr>

    <h5>🔔 Alerts & Escalations</h5>
    <form method="POST" action="{{ route('legal.compliance.calendar.addAlert', $entry->Id) }}">
        @csrf
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Days Before *</label>
                <input type="number" name="DaysBefore" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Escalation Level</label>
                <input type="text" name="EscalationLevel" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Channel</label>
                <select name="Channel" class="form-select">
                    <option value="Email">Email</option>
                    <option value="SMS">SMS</option>
                    <option value="Dashboard">Dashboard</option>
                    <option value="Teams">Teams</option>
                </select>
            </div>
            <div class="col-md-2 mb-3 d-flex align-items-end">
                <button class="btn btn-primary">➕ Add Alert</button>
            </div>
        </div>
    </form>

    <table class="table table-sm mt-3">
        <thead><tr><th>Days Before</th><th>Escalation Level</th><th>Channel</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($entry->alerts as $alert)
            <tr>
                <td>{{ $alert->DaysBefore }}</td>
                <td>{{ $alert->EscalationLevel ?? '-' }}</td>
                <td>{{ $alert->Channel }}</td>
                <td>{{ $alert->IsActive ? 'Active' : 'Inactive' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
