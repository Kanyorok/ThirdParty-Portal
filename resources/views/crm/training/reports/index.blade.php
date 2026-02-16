@extends('layouts.app')

@section('title', 'Training Reports')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Training Reports</h2>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Programs</div>
                    <div class="fs-4 fw-semibold">{{ $totalPrograms }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Sessions</div>
                    <div class="fs-4 fw-semibold">{{ $totalSessions }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Participants</div>
                    <div class="fs-4 fw-semibold">{{ $totalParticipants }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Completed Sessions</div>
                    <div class="fs-4 fw-semibold">{{ $completedSessions }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="mb-3">Attendance Summary (Recent Sessions)</h5>
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>Program</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Present</th>
                            <th>Late</th>
                            <th>Absent</th>
                            <th>Feedback Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendanceBySession as $session)
                            @php($fb = $feedbackSummary[$session->Id] ?? null)
                            <tr>
                                <td>{{ $session->Title ?? $session->SessionCode ?? ('Session #' . $session->Id) }}</td>
                                <td>{{ $session->program?->Title ?? '-' }}</td>
                                <td>{{ $session->StartDate?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $session->total_participants }}</td>
                                <td>{{ $session->present_count }}</td>
                                <td>{{ $session->late_count }}</td>
                                <td>{{ $session->absent_count }}</td>
                                <td>
                                    @if($fb)
                                        {{ number_format($fb->AvgContent ?? 0, 2) }} /
                                        {{ number_format($fb->AvgTrainer ?? 0, 2) }} /
                                        {{ number_format($fb->AvgRelevance ?? 0, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No attendance data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="small text-muted mt-2">Feedback averages shown as Content / Trainer / Relevance.</div>
        </div>
    </div>

    <div class="row g-3 mt-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Training Hours by Client Type</h5>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Client Type</th>
                                    <th>Clients</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hoursByDepartment as $row)
                                    <tr>
                                        <td>{{ $row->Department }}</td>
                                        <td>{{ $row->EmployeeCount }}</td>
                                        <td>{{ number_format($row->Hours ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No hours recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Training Hours by Client</h5>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hoursByEmployee as $row)
                                    <tr>
                                        <td>{{ $row->EmployeeName }}</td>
                                        <td>{{ number_format($row->Hours ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">No hours recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="mb-3">Mandatory Training Compliance</h5>
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Targets</th>
                            <th>Completed</th>
                            <th>Completion %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mandatoryCompliance as $row)
                            <tr>
                                <td>{{ $row['program']->Title }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['completed'] }}</td>
                                <td>{{ number_format($row['percent'], 2) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No mandatory programs configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Certificates Expiring Soon (90 days)</h5>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Program</th>
                                    <th>Expiry</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringCertificates as $certificate)
                                    <tr>
                                        <td>{{ $certificate->client?->Name ?? $certificate->ClientID }}</td>
                                        <td>{{ $certificate->session?->program?->Title ?? '-' }}</td>
                                        <td>{{ $certificate->ExpiresOn?->format('Y-m-d') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No upcoming expiries.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Program Cost vs Budget</h5>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Program</th>
                                    <th>Budgeted</th>
                                    <th>Actual</th>
                                    <th>Variance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programCosts as $program)
                                    @php($variance = ($program->ActualCost ?? 0) - ($program->BudgetedCost ?? 0))
                                    <tr>
                                        <td>{{ $program->Title }}</td>
                                        <td>{{ number_format($program->BudgetedCost ?? 0, 2) }}</td>
                                        <td>{{ number_format($program->ActualCost ?? 0, 2) }}</td>
                                        <td>{{ number_format($variance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">No programs found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
