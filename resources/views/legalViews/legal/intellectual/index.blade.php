@extends('layouts.app')
@section('title', 'IP & Trademark Registry')

@section('content')
<div class="card shadow p-4 rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">🧠 IP & Trademark Registry</h4>
        <a href="{{ route('legal.intellectual.create') }}" class="btn btn-primary">➕ Register IP</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>Owner</th>
                <th>Status</th>
                <th>Registration No.</th>
                <th>Expiry Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                <tr>
                    <td>{{ $record->IPType }}</td>
                    <td>{{ $record->Title }}</td>
                    <td>{{ $record->Owner }}</td>
                    <td>{{ $record->Status }}</td>
                    <td>{{ $record->RegistrationNumber }}</td>
                    <td>{{ $record->ExpiryDate }}</td>
                    <td>
                        <a href="{{ route('legal.intellectual.show', $record->ID) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('legal.intellectual.edit', $record->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
