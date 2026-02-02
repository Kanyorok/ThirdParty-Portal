@extends('layouts.app')
@section('title', 'Maintenance Dashboard')

@section('content')
<div class="container mt-4">

    <p>
        <small>
            This dashboard displays the assigned position of maintenance tasks based on their current status.
        </small>
    </p>

    {{-- ================= SUMMARY ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm text-center">
                <div class="card-body">
                    <h6>Total Requests</h6>
                    <h3>{{ $totalRequests }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning shadow-sm text-center">
                <div class="card-body">
                    <h6>Unassigned Requests</h6>
                    <h3>{{ $unassigned }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning shadow-sm text-center">
                <div class="card-body">
                    <h6>Pending</h6>
                    <h3>{{ $inProgress }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm text-center">
                <div class="card-body">
                    <h6>Completed</h6>
                    <h3>{{ $completed }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= FILTER FORM ================= --}}
    <form method="GET"
          action="{{ route('maintenancedashboard.index') }}"
          class="row g-3 mb-3 align-items-end">

        {{-- Property --}}
        <div class="col-md-3">
            <label class="form-label">Property</label>
            <select name="property_id" class="form-select">
                <option value="">All Properties</option>
                @foreach($properties as $property)
                    <option value="{{ $property->Id }}"
                        {{ request('property_id') == $property->Id ? 'selected' : '' }}>
                        {{ $property->PropertyName }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Priority --}}
        <div class="col-md-3">
            <label class="form-label">Priority</label>
            <select name="priority" class="form-select">
                <option value="">All Priorities</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority->Description }}"
                        {{ request('priority') == $priority->Description ? 'selected' : '' }}>
                        {{ $priority->Description ?? 'N/A' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="{{ \App\Enums\Core\PostingEnum::Pending }}"
                    {{ request('status') == \App\Enums\Core\PostingEnum::Pending ? 'selected' : '' }}>
                    Pending
                </option>
                <option value="{{ \App\Enums\Core\PostingEnum::Completed }}"
                    {{ request('status') == \App\Enums\Core\PostingEnum::Completed ? 'selected' : '' }}>
                    Completed
                </option>
            </select>
        </div>

        {{-- Buttons --}}
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <button type="button"
                    onclick="window.location='{{ route('maintenancedashboard.index') }}'"
                    class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-clockwise me-1"></i> Reset
            </button>
        </div>
    </form>

    {{-- ================= ACTIVE FILTERS ================= --}}
    @if(request()->anyFilled(['property_id','priority','status']))
        <div class="mb-3">
            <strong>Active Filters:</strong>

            {{-- Property --}}
            @if(request('property_id'))
                @php
                    $p = $properties->firstWhere('Id', request('property_id'));
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['property_id' => null]) }}"
                   class="badge bg-primary text-decoration-none me-1">
                    Property: {{ $p->PropertyName ?? 'Unknown' }} ✕
                </a>
            @endif

            {{-- Priority --}}
            @if(request('priority'))
                <a href="{{ request()->fullUrlWithQuery(['priority' => null]) }}"
                   class="badge bg-warning text-dark text-decoration-none me-1">
                    Priority: {{ request('priority') }} ✕
                </a>
            @endif

            {{-- Status --}}
            @if(request('status'))
                <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}"
                   class="badge bg-success text-decoration-none me-1">
                    Status: {{ \App\Enums\Core\PostingEnum::from(request('status'))->Label() }} ✕
                </a>
            @endif

            {{-- Clear All --}}
            <a href="{{ route('maintenancedashboard.index') }}"
            class="badge bg-secondary text-decoration-none">
                <i class="bi bi-arrow-clockwise me-1"></i> Reset
            </a>
        </div>
    @endif

    {{-- ================= TABLE ================= --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Request</th>
                    <th>Property</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Date Reported</th>
                </tr>
            </thead>

            <tbody>
            @forelse($requests as $assign)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $assign->request->RequestNumber ?? '-' }}</td>
                    <td>{{ $assign->request->property->PropertyName ?? '-' }}</td>
                    <td>{{ $assign->request->issueType->Description ?? '-' }}</td>

                    {{-- Priority --}}
                    <td>
                        @php $priority = $assign->request->priority->Description ?? '-'; @endphp
                        <span class="badge
                            @if($priority === 'High') bg-danger
                            @elseif($priority === 'Medium') bg-warning text-dark
                            @elseif($priority === 'Low') bg-info
                            @else bg-secondary @endif">
                            {{ $priority }}
                        </span>
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="badge
                            @if($assign->Status == \App\Enums\Core\PostingEnum::Completed) bg-success
                            @elseif($assign->Status == \App\Enums\Core\PostingEnum::Pending) bg-warning text-dark
                            @else bg-secondary @endif">
                            {{ $assign->Status->Label() }}
                        </span>
                    </td>

                    {{-- Assigned To --}}
                    <td>
                        {{ $assign->internalTechnician->FullName
                            ?? $assign->prequalifiedVendor->TradingName
                            ?? '-' }}
                    </td>

                    {{-- Date --}}
                    <td>
                        {{ $assign->AssignmentDate
                            ? \Carbon\Carbon::parse($assign->AssignmentDate)->format('d M Y')
                            : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">
                        No maintenance records found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= PAGINATION ================= --}}
    <div class="mt-3">
        {{ $requests->withQueryString()->links() }}
    </div>

</div>
@endsection
