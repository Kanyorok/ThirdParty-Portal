@extends('layouts.app')
@section('title', 'Contracted Driver Licenses')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">🪪 License History – {{ $driver->FullName }}</h4>

        <div class="mb-3 text-end">
            <a href="{{ route('fleet.contracted_driver_licenses.create', $driver->ID) }}" class="btn btn-primary">+ Add
                License</a>
        </div>


        <div class="table-responsive">
            <table id="licenseTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>License No.</th>
                    <th>Category</th>
                    <th>Issue Date</th>
                    <th>Expiry Date</th>
                    <th>Notes</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($licenses as $license)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $license->LicenseNumber }}</td>
                        <td>{{ $license->LicenseCategory }}</td>
                        <td>{{ $license->IssueDate }}</td>
                        <td>{{ $license->ExpiryDate }}</td>
                        <td>{{ $license->Notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No license records found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @endsection
        @section('scripts')
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script>
                $(document).ready(function () {
                    @if(!$licenses->isEmpty())
                    $('#licenseTable').DataTable({
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

