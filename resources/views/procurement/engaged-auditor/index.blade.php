@extends('layouts.app')
@section('title', 'Engaged Auditors')
@section('content')
<div class="container">
    <h3>List of Engaged SASRA Auditors</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('engaged-auditors.create') }}" class="btn btn-primary mb-3">Engage New Auditor</a>

    @if ($engagedAuditors->count())
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Auditor Name</th>
                    <th>Firm Name</th>
                    <th>Engagement Start Date</th>
                    <th>Engagement End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($engagedAuditors as $engaged)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $engaged->sasraAuditor->AuditorName ?? '-' }}</td>
                        <td>{{ $engaged->sasraAuditor->FirmName ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($engaged->EngagementStartDate)->format('Y-m-d') }}</td>
                        <td>{{ $engaged->EngagementEndDate ? \Carbon\Carbon::parse($engaged->EngagementEndDate)->format('Y-m-d') : 'Ongoing' }}</td>
                        <td>{{ $engaged->EngagementStatus }}</td>
                        <td>
                            <!-- Optional: Add edit/delete buttons if applicable -->
                            {{-- <a href="{{ route('engaged-auditors.edit', $engaged->Id) }}" class="btn btn-sm btn-warning">Edit</a> --}}
                            {{-- <form method="POST" action="{{ route('engaged-auditors.destroy', $engaged->Id) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form> --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No engaged auditors available.</p>
    @endif
</div>
@endsection
