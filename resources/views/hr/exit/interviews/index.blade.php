@extends('layouts.app')

@section('title', 'Exit Interviews')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Interviews</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Exit No</th>
                            <th>Employee</th>
                            <th>Interview Date</th>
                            <th>Interviewer</th>
                            <th>Mode</th>
                            <th>Reason</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($interviews as $interview)
                            <tr>
                                <td>{{ $interview->exit?->ExitNo ?? '-' }}</td>
                                <td>{{ $interview->exit?->employee?->FirstName }} {{ $interview->exit?->employee?->LastName }}</td>
                                <td>{{ $interview->InterviewDate?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $interview->interviewer?->FirstName }} {{ $interview->interviewer?->LastName }}</td>
                                <td>{{ $interview->Mode ?? '-' }}</td>
                                <td>{{ $interview->AttritionReason ?? '-' }}</td>
                                <td class="text-end">
                                    @if($interview->ExitID)
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.exit.interviews.show', $interview->ExitID) }}">View</a>
                                        <a class="btn btn-sm btn-outline-primary ms-1" href="{{ route('hr.exit.interviews.edit', $interview->ExitID) }}">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No exit interviews found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $interviews->links() }}
        </div>
    </div>
</div>
@endsection
