@extends('layouts.app')

@section('title', 'Working Days & Hours')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Working Days & Standard Hours</h2>
        <a class="btn btn-outline-primary" href="{{ route('hr.config.workingdays.index', ['edit' => 1]) }}">Edit</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weekdays as $idx => $dayName)
                        @php $row = $days[$idx] ?? null; @endphp
                        <tr>
                            <td>{{ $dayName }}</td>
                            <td>{{ ($row?->IsWorking) ? 'Working' : 'Off' }}</td>
                            <td>{{ $row?->StartTime ?? '-' }}</td>
                            <td>{{ $row?->EndTime ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
