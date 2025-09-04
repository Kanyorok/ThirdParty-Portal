@extends('layouts.app')
@section('title', 'Compliance Calendar')

@section('content')
<div class="card shadow rounded-4 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">📅 Compliance Calendar</h4>
        <a href="{{ route('legal.compliance.calendar.create') }}" class="btn btn-sm btn-primary">➕ Add Entry</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Obligation</th>
                <th>Start</th>
                <th>End</th>
                <th>Owner</th>
                <th>Status</th>
                <th style="width: 120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($entries as $entry)
            <tr>
                <td>{{ $entry->Title }}</td>
                <td>{{ $entry->obligation->Title ?? '-' }}</td>
                <td>{{ $entry->StartDate }}</td>
                <td>{{ $entry->EndDate ?? '-' }}</td>
                <td>{{ \DB::table('t_Users')->where('Id', $entry->OwnerID)->value('Name') }}</td>
                <td>{{ $entry->DeletedOn ? 'Deleted' : 'Active' }}</td>
                <td>
                    <a href="{{ route('legal.compliance.calendar.show', $entry->Id) }}" class="btn btn-sm btn-info">🔍</a>
                    <a href="{{ route('legal.compliance.calendar.edit', $entry->Id) }}" class="btn btn-sm btn-warning">✏️</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">No entries found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
