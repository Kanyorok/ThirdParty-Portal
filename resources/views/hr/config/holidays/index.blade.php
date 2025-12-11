@extends('layouts.app')

@section('title', 'Holidays')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Holiday Calendar</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.holidays.create') }}">+ New Holiday</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form class="row g-3 mb-3" method="GET">
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control" value="{{ request('year') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Any</option>
                        @foreach(['Pending','Approved'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Region</th>
                            <th>Recurring</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                            <tr>
                                <td>{{ $holiday->Name }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($holiday->HolidayDate)->format('Y-m-d') }}</td>
                                <td>{{ $holiday->Region ?? '-' }}</td>
                                <td>{{ $holiday->IsRecurring ? 'Yes' : 'No' }}</td>
                                <td>{{ $holiday->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.holidays.edit', $holiday->Id) }}">Edit</a>
                                    <form action="{{ route('hr.config.holidays.destroy', $holiday->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deactivate this holiday?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                    @if($holiday->Status !== 'Approved')
                                        <form action="{{ route('hr.config.holidays.approve', $holiday->Id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No holidays found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $holidays->links() }}
        </div>
    </div>
</div>
@endsection
