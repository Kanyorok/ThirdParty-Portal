@extends('layouts.app')
@section('title', 'Contracted Drivers Management')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between mb-3">
            <h4 class="mb-0">🚐 Contracted Drivers List</h4>
            <a href="{{ route('fleet.contracted_drivers.create') }}" class="btn btn-success">
                + Add Contracted Driver
            </a>
        </div>

        <div class="table-responsive">
            <table id="contractedDriversTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>DriverNo</th>
                    <th>Name</th>
                    <th>ID No.</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Contract Period</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($drivers as $driver)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $driver->DriverNo }}</td>
                        <td>{{ $driver->FullName }}</td>
                        <td>{{ $driver->NationalID ?? '—' }}</td>
                        <td>{{ $driver->Phone ?? '—' }}</td>
                        <td>{{ $driver->company->ThirdPartyName ?? '—' }}</td>
                        <td>
                            @if($driver->ContractStartDate && $driver->ContractEndDate)
                                {{ \Carbon\Carbon::parse($driver->ContractStartDate)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($driver->ContractEndDate)->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($driver->IsActive)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('fleet.contracted_drivers.show', $driver->Id) }}"
                               class="btn btn-sm btn-info">👁️Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No contracted drivers found.</td>
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
            $('#contractedDriversTable').DataTable({
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

