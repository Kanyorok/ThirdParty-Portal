@extends('layouts.app')
@section('title', 'Insurance Records')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="card shadow p-4 rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>📋 Fleet Insurance List</h4>
            <a href="{{ route('fleet.insurance_tracker.create') }}" class="btn btn-primary">➕ Add New</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table id="insuranceTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Fleet Insurance No</th>
                    <th>Vehicle</th>
                    <th>Policy No</th>
                    <th>Provider</th>
                    <th>Premium</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $record->InsuranceNo }}</td>
                        <td>{{ $record->vehicle->RegistrationNo ?? 'N/A' }}</td>
                        <td>{{ $record->PolicyNumber }}</td>
                        <td>{{ $record->insurance->Name ?? 'N/A' }}</td>
                        <td>KES {{ number_format($record->PremiumAmount, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->CoverageStartDate)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->CoverageEndDate)->format('d/m/Y') }}</td>
                        @php
                            $statusColor = match(strtolower($record->insuranceStatus->Description ?? '')) {
                                'active' => 'success',
                                'expired' => 'danger',
                                'pending' => 'warning',
                                default => 'secondary',
                            };
                        @endphp

                        <td>
                        <span class="badge bg-{{ $statusColor }}">
                            {{ $record->insuranceStatus->Description ?? 'N/A' }}
                        </span>
                        </td>

                        <td>
                            <a href="{{ route('fleet.insurance_tracker.show', $record->Id) }}"
                               class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('fleet.insurance_tracker.edit', $record->Id) }}"
                               class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('fleet.insurance_tracker.destroy', $record->Id) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No records found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @section('scripts')
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script>
                $(document).ready(function () {
                    @if(!$records->isEmpty())
                    $('#insuranceTable').DataTable({
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
@endsection
