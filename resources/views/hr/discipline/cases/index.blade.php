@extends('layouts.app')

@section('title', 'Disciplinary Cases')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Disciplinary Cases</h2>
        <a class="btn btn-primary" href="{{ route('hr.discipline.cases.create') }}">+ New Case</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <select name="employee" class="form-select">
                        <option value="">All</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->Id }}" @selected(request('employee') == $employee->Id)>
                                {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['Reported','Under Investigation','Response Submitted','Investigation Approved','Hearing Scheduled','Decision Pending','Sanction Applied','Appealed','Appeal Decided','Closed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Case No</th>
                            <th>Employee</th>
                            <th>Offence</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Reported</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cases as $case)
                            <tr>
                                <td>{{ $case->CaseNo }}</td>
                                <td>{{ $case->employee?->FirstName }} {{ $case->employee?->LastName }}</td>
                                <td>{{ $case->offence?->Name ?? '-' }}</td>
                                <td>{{ $case->Severity ?? '-' }}</td>
                                <td>{{ $case->Status }}</td>
                                <td>{{ $case->ReportedDate ? $case->ReportedDate->format('Y-m-d') : '-' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.discipline.cases.show', $case->Id) }}">View</a>
                                    @if($case->Status !== 'Closed')
                                        <form action="{{ route('hr.discipline.cases.close', $case->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Close this case?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Close</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No cases found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $cases->links() }}
        </div>
    </div>
</div>
@endsection
