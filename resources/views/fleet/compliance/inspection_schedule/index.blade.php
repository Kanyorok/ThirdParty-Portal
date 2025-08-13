@extends('layouts.app')
@section('title', 'License & Inspection Schedule')

@section('content')
<div class="card p-4 shadow rounded-4">
   
    <div class="d-flex justify-content-between align-items-center mb-3">
         <h4 class="mb-4">🧾 License & Inspection Schedule</h4>
        <a href="{{ route('fleet.inspection_schedule.create') }}" class="btn btn-primary mb-3">➕ Schedule Inspection</a>

        </a>
</div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Inspection Type</th>
                    <th>Inspection Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schedules as $schedule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $schedule->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $schedule->InspectionType }}</td>
                        <td>{{ $schedule->InspectionDate }}</td>
                        <td>{{ $schedule->ExpiryDate }}</td>
                        <td>{{ $schedule->Status }}</td>
                        <td>{{ $schedule->Remarks }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No inspection records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
