@extends('layouts.app')
@section('title', 'Lease Agreements')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <a href="{{ route('addlease.create') }}" class="btn btn-primary mb-3">New Lease</a>
    <h4 class="fw-bold mb-3">Lease Agreements</h4>

    @if($newleases->count())
        <table id="leaseagreement" class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Lease Number</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Payment Frequency</th>
                    <th>Due Day</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($newleases as $newlease)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $newlease->LeaseNumber ?? '-' }}</td>
                    <td>{{ $newlease->tenant->TenantName ?? '-' }}</td>
                    <td>{{ $newlease->property->PropertyName ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($newlease->StartDate)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($newlease->EndDate)->format('d/m/Y') }}</td>
                    <td>{{ $newlease->code->Description ?? '-' }}</td>
                    <td>{{ $newlease->DueDay ?? '-' }}</td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('addlease.show', $newlease->Id) }}" class="btn btn-success btn-sm">View</a>
                        <a href="{{ route('addlease.edit', $newlease->Id) }}" class="btn btn-info btn-sm">Edit</a>
                        <form action="{{ route('addlease.destroy', $newlease->Id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lease?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>No lease agreements registered yet.</p>
    @endif
</div>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#leaseagreement').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
