@extends('layouts.app')
@section('title', 'Repair Logs')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between mb-3">
            <h4 class="mb-0">Repairs List</h4>
            <a href="{{ route('fleet.repair_logs.create') }}" class="btn btn-primary">➕ Log Repair</a>
        </div>


        <div class="table-responsive">
            <table id="repairlogsTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Repair</th>
                    <th>Vehicle</th>
                    <th>Type</th>
                    <th>Repair Date</th>
                    <th>Vendor</th>
                    <th>Cost</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($repairs as $repair)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $repair->RepairID }}</td>
                        <td>{{ $repair->vehicle->RegistrationNo ?? '-' }}</td>
                        <td>{{ $repair->repairType->Description ?? '-' }}</td>
                        <td>{{ $repair->RepairDate }}</td>
                        <td>{{ $repair->Vendor ?? '-' }}</td>
                        <td>KES {{ number_format($repair->Cost, 2) }}</td>
                        <td>{{ $repair->Description }}</td>
                        <td>
                            <a href="{{ route('fleet.repair_logs.show', $repair->Id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('fleet.repair_logs.edit', $repair->Id) }}" class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{ route('fleet.repair_logs.destroy', $repair->Id) }}" method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this repair log?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No repair logs found.</td>
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
            @if(!$repairs->isEmpty())
            $('#repairlogsTable').DataTable({
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

