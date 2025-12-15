<div>
    <!-- Be present above all else. - Naval Ravikant -->
</div>
@extends('layouts.app')

@section('title', 'Leave Calendar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Leave Calendar</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($events->count())
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Reliever</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $ev)
                                <tr>
                                    <td>{{ $ev->employee->FirstName ?? '' }} {{ $ev->employee->LastName ?? '' }}</td>
                                    <td>{{ $ev->type->Name ?? '' }}</td>
                                    <td>{{ $ev->StartDate }}</td>
                                    <td>{{ $ev->EndDate }}</td>
                                <td>{{ $ev->StartDate }}</td>
                                <td>{{ $ev->EndDate }}</td>
                                <td>{{ $ev->TotalDays }}</td>
                                <td>{{ $ev->reliever->FirstName ?? '-' }} {{ $ev->reliever->LastName ?? '' }}</td>
                                <td>{{ $ev->Status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="alert alert-light border">No leave entries yet.</div>
            @endif
        </div>
    </div>
</div>
@endsection
