@extends('layouts.app')
@section('title', 'Counsel Assignment – ' . $case->CaseTitle)

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>👨‍⚖️ Legal Counsels for: {{ $case->CaseTitle }}</h4>
        <a href="{{ route('legal.disputes.counsels.create', $case->ID) }}" class="btn btn-primary">➕ Assign New Counsel</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Firm</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>External?</th>
                <th>Assigned On</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($counsels as $counsel)
                <tr>
                    <td>{{ $counsel->CounselName }}</td>
                    <td>{{ $counsel->FirmName }}</td>
                    <td>{{ $counsel->Email }}</td>
                    <td>{{ $counsel->Phone }}</td>
                    <td>{{ $counsel->Role }}</td>
                    <td>{{ $counsel->IsExternal ? 'Yes' : 'No' }}</td>
                    <td>{{ \Carbon\Carbon::parse($counsel->AssignedOn)->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No counsels assigned yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
