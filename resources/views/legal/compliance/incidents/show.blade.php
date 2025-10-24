@extends('layouts.app')
@section('title','Incident Details')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-3">⚠️ Incident Details</h4>

        <p><strong>Title:</strong> {{ $incident->Title }}</p>
        <p><strong>Date:</strong> {{ $incident->IncidentDate }}</p>
        <p><strong>Obligation:</strong> {{ $incident->obligation->Title ?? '-' }}</p>
        <p><strong>Severity:</strong> {{ $incident->severity->Name ?? '-' }}</p>
        <p><strong>Responsible:</strong> {{ $owners[$incident->ResponsibleUserID] ?? '-' }}</p>
        <p><strong>Status:</strong> {{ $incident->Status }}</p>
        <p><strong>Description:</strong> {{ $incident->Description }}</p>

        <hr>
        <h5>🛠 Corrective Actions</h5>
        <form method="POST" action="{{ route('legal.compliance.incidents.addAction',$incident->Id) }}">
            @csrf
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Root Cause</label>
                    <textarea name="RootCause" class="form-control"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Corrective Action *</label>
                    <textarea name="CorrectiveAction" class="form-control" required></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Action Owner</label>
                    <select name="ActionOwnerID" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($owners as $id=>$name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="DueDate" class="form-control">
                </div>
            </div>
            <button class="btn btn-primary">➕ Add Action</button>
        </form>

        <table class="table table-sm mt-3">
            <thead>
            <tr>
                <th>Root Cause</th>
                <th>Action</th>
                <th>Owner</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($incident->actions as $action)
                <tr>
                    <td>{{ $action->RootCause ?? '-' }}</td>
                    <td>{{ $action->CorrectiveAction }}</td>
                    <td>{{ $owners[$action->ActionOwnerID] ?? '-' }}</td>
                    <td>{{ $action->DueDate ?? '-' }}</td>
                    <td>{{ $action->Status }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
