@extends('layouts.app')
@section('title','Compliance Incidents')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <div class="d-flex justify-content-between mb-3">
            <h4>⚠️ Breaches & Incidents</h4>
            <div>
                <a href="{{ route('legal.compliance.incidents.dashboard') }}"
                   class="btn btn-sm btn-outline-primary me-2">📊 Dashboard</a>
                <a href="{{ route('legal.compliance.incidents.create') }}" class="btn btn-sm btn-primary">➕ Log
                    Incident</a>
            </div>
        </div>

        <div class="row mb-4 text-center">
            <div class="col-md-3">
                <div class="card bg-light p-3 shadow-sm">
                    <h6>Total Incidents</h6>
                    <h3>{{ $total }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning p-3 shadow-sm">
                    <h6>Open</h6>
                    <h3>{{ $open }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success p-3 shadow-sm text-white">
                    <h6>Resolved</h6>
                    <h3>{{ $resolved }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger p-3 shadow-sm text-white">
                    <h6>Escalated</h6>
                    <h3>{{ $escalated }}</h3>
                </div>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered table-striped">
            <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Obligation</th>
                <th>Severity</th>
                <th>Date</th>
                <th>Status</th>
                <th>Owner</th>
                <th style="width:120px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($incidents as $incident)
                <tr>
                    <td>{{ $incident->Title }}</td>
                    <td>{{ $incident->obligation->Title ?? '-' }}</td>
                    <td>{{ $incident->severity->Name ?? '-' }}</td>
                    <td>{{ $incident->IncidentDate }}</td>
                    <td>{{ $incident->Status }}</td>
                    <td>{{ $owners[$incident->ResponsibleUserID] ?? '-' }}</td>
                    <td>
                        <a href="{{ route('legal.compliance.incidents.show',$incident->Id) }}"
                           class="btn btn-sm btn-info">🔍</a>
                        <a href="{{ route('legal.compliance.incidents.edit',$incident->Id) }}"
                           class="btn btn-sm btn-warning">✏️</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No incidents found</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
