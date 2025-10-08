@extends('layouts.app')
@section('title', 'Drivers')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"> Fleet Drivers List</h4>
            <a href="{{ route('fleet.drivers.create') }}" class="btn btn-primary">
                + New Driver
            </a>
        </div>

        @if(!$drivers->isEmpty())
            <div class="mb-3">
                <p class="mb-0" style="font-style: italic;">
                    <span class="me-2">💡</span>
                    To manage driver licenses and vehicle assignments, click the 'Details' button, then use the tabs to
                    add licenses or assign vehicles.<br>
                    <strong><span class="me-1">ℹ️</span>Note:</strong> Trips are automatically loaded from the trips
                    table when a driver is assigned a trip.
                </p>
            </div>
        @endif

        <div class="table-responsive">
            <table id="driversTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Staff No.</th>
                    <th>ID No.</th>
                    <th>Phone</th>
                    <th>Employment</th>
                    <th>Status</th>
                    <th>Driver Availability</th>
                    <th>Actions</th>
                    
                </tr>
                </thead>
                <tbody>
                @forelse ($drivers as $driver)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $driver->FullName }}</td>
                        <td>{{ $driver->driver->EmployeeID ?? '—' }}</td>
                        <td>{{ $driver->NationalID }}</td>
                        <td>{{ $driver->Phone }}</td>
                        <td>{{ $driver->employmentType->Description ?? '—' }}</td>
                        <td>
                            @if($driver->IsActive)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                        @if($driver->driverStatus)
                            @php
                                // Map status descriptions to badge colors
                                $statusColors = [
                                    'Available'     => 'success',
                                    'AssignedTrip'  => 'warning',
                                    'OnTrip'        => 'info',
                                ];

                                $color = $statusColors[$driver->driverStatus->Description] ?? 'light';
                            @endphp

                            <span class="badge bg-{{ $color }}">
                                {{ $driver->driverStatus->Description }}
                            </span>
                        @else
                            <span class="badge bg-light text-dark">-</span>
                        @endif
                    </td>
                        <td>
                            <a href="{{ route('fleet.drivers.show', $driver->Id) }}" class="btn btn-sm btn-info">👁️Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">No drivers registered.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            @if(!$drivers->isEmpty())
            $('#driversTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    emptyTable: ""
                }
            });
            @endif
        });
    </script>
@endsection

