@extends('layouts.app')
@section('title', ' Committee Roles Overview')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📄 Committee Roles Overview</h4>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Reference</th>
                    <th>Member Name</th>
                    <th>Previous Role</th>
                    <th>New Role</th>
                    <th>Changed On</th>
                    <th>Status</th>
                    <th>Responded On</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roleHistory as $history)
                    <tr>
                        <td>{{ $history->reference }}</td>
                        <td>{{ $history->member_name }}</td>
                        <td>{{ $history->PreviousRole ?? '—' }}</td>
                        <td>{{ $history->NewRole }}</td>
                        <td>{{ $history->ChangedOn ? \Carbon\Carbon::parse($history->ChangedOn)->format('d/m/Y H:i') : '—' }}</td>
                        <td>
                            @if ((int) $history->Status === 1)
                                <span class="badge bg-success">Accepted</span>
                            @elseif ((int) $history->Status === 2)
                                <span class="badge bg-danger">Declined</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>{{ $history->RespondedOn ? \Carbon\Carbon::parse($history->RespondedOn)->format('d/m/Y H:i') : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No role changes have been made yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

