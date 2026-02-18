@extends('layouts.app')

@section('title', 'Exit Clearances')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Clearances</h2>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['Pending','Cleared','Waived','On Hold'] as $status)
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
                            <th>Exit No</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Cleared On</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clearances as $clearance)
                            <tr>
                                <td>{{ $clearance->exit?->ExitNo ?? '-' }}</td>
                                <td>{{ $clearance->exit?->employee?->FirstName }} {{ $clearance->exit?->employee?->LastName }}</td>
                                <td>{{ $clearance->department?->Name ?? '-' }}</td>
                                <td>{{ $clearance->Status }}</td>
                                <td>{{ $clearance->ClearedOn ? $clearance->ClearedOn->format('Y-m-d H:i') : '-' }}</td>
                                <td class="text-end">
                                    @if($clearance->exit)
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.exit.requests.show', $clearance->exit->Id) }}">View Exit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No clearances found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $clearances->links() }}
        </div>
    </div>
</div>
@endsection
